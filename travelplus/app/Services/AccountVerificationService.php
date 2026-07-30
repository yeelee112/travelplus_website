<?php

namespace App\Services;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;

class AccountVerificationService
{
    public const STATUS_PENDING = 'pending_verification';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_ZALO = 'zalo';

    private const OTP_TTL_SECONDS = 300;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const DAILY_SEND_LIMIT = 5;
    private const MAX_OTP_ATTEMPTS = 5;

    private BaseConnection $db;
    private ZaloOtpService $zalo;

    public function __construct(?BaseConnection $db = null, ?ZaloOtpService $zalo = null)
    {
        $this->db = $db ?? db_connect();
        $this->zalo = $zalo ?? new ZaloOtpService();
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('account_verification_requests')
            && $this->db->fieldExists('email_verified_at', 'users')
            && $this->db->fieldExists('phone_verified_at', 'users')
            && $this->db->fieldExists('verification_channel', 'users');
    }

    /**
     * @param array<string, mixed> $user
     * @return array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int}
     */
    public function start(array $user, string $locale, bool $preferZalo = true, bool $force = false): array
    {
        if (! $this->schemaReady()) {
            return $this->result(false, '', '', 'schema_missing');
        }

        $phone = VietnamPhoneService::normalize((string) ($user['phone'] ?? ''));
        if ($preferZalo && VietnamPhoneService::isValid($phone) && $this->zalo->isConfigured()) {
            $zaloResult = $this->sendZaloOtp($user, $phone, $force);
            if ($zaloResult['sent'] || $zaloResult['reason'] === 'cooldown') {
                return $zaloResult;
            }
        }

        return $this->sendEmailOtp($user, $locale, $force);
    }

    /**
     * @param array<string, mixed> $user
     * @return array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int}
     */
    public function sendEmailFallback(array $user, string $locale, bool $force = false): array
    {
        return $this->sendEmailOtp($user, $locale, $force);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestRequestForUser(int $userId): ?array
    {
        return $this->db->table('account_verification_requests')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    /**
     * @return array{ok:bool,reason:string,user:?array}
     */
    public function verifyOtp(int $userId, string $otp): array
    {
        $otp = preg_replace('/\D/', '', $otp) ?? '';
        $request = $this->db->table('account_verification_requests')
            ->where('user_id', $userId)
            ->where('verified_at', null)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (! is_array($request) || ($request['delivery_status'] ?? '') !== 'sent') {
            return $this->verificationResult(false, 'not_found');
        }
        if (strtotime((string) $request['expires_at']) < time()) {
            return $this->verificationResult(false, 'expired');
        }
        if ((int) $request['attempts'] >= (int) $request['max_attempts']) {
            return $this->verificationResult(false, 'attempts_exceeded');
        }

        $attempts = (int) $request['attempts'] + 1;
        $this->db->table('account_verification_requests')
            ->where('id', (int) $request['id'])
            ->update(['attempts' => $attempts, 'updated_at' => date('Y-m-d H:i:s')]);

        if (strlen($otp) !== 6 || ! hash_equals((string) $request['token_hash'], self::hashToken($otp))) {
            return $this->verificationResult(false, $attempts >= (int) $request['max_attempts'] ? 'attempts_exceeded' : 'invalid');
        }

        return $this->activateUser(
            (int) $request['user_id'],
            (string) $request['channel'],
            (int) $request['id']
        );
    }

    /**
     * @return array{ok:bool,reason:string,user:?array}
     */
    public function verifyEmailToken(string $plainToken): array
    {
        if ($plainToken === '') {
            return $this->verificationResult(false, 'invalid');
        }

        $request = $this->db->table('account_verification_requests')
            ->where('channel', self::CHANNEL_EMAIL)
            ->where('token_hash', self::hashToken($plainToken))
            ->where('delivery_status', 'sent')
            ->where('verified_at', null)
            ->get()
            ->getRowArray();

        if (! is_array($request)) {
            return $this->verificationResult(false, 'invalid');
        }
        if (strtotime((string) $request['expires_at']) < time()) {
            return $this->verificationResult(false, 'expired');
        }

        $latest = $this->latestRequestForUser((int) $request['user_id']);
        if (! is_array($latest) || (int) $latest['id'] !== (int) $request['id']) {
            return $this->verificationResult(false, 'invalid');
        }

        return $this->activateUser((int) $request['user_id'], self::CHANNEL_EMAIL, (int) $request['id']);
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        if ($domain === '') {
            return $email;
        }

        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));
        return $visible . str_repeat('*', max(3, mb_strlen($local) - mb_strlen($visible))) . '@' . $domain;
    }

    public static function maskPhone(string $phone): string
    {
        $normalized = VietnamPhoneService::normalize($phone);
        if (strlen($normalized) < 7) {
            return $normalized;
        }

        return substr($normalized, 0, 3) . ' *** ' . substr($normalized, -3);
    }

    /**
     * @param array<string, mixed> $user
     * @return array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int}
     */
    private function sendZaloOtp(array $user, string $phone, bool $force): array
    {
        $rateLimit = $this->rateLimit((int) $user['id'], self::CHANNEL_ZALO, $force);
        if ($rateLimit !== null) {
            return $this->result(false, self::CHANNEL_ZALO, self::maskPhone($phone), $rateLimit['reason'], $rateLimit['cooldown']);
        }

        $otp = $this->generateUniqueOtp();
        if ($otp === null) {
            return $this->result(false, self::CHANNEL_ZALO, self::maskPhone($phone), 'delivery_failed');
        }
        $requestId = $this->insertRequest(
            (int) $user['id'],
            self::CHANNEL_ZALO,
            $phone,
            self::hashToken($otp),
            self::OTP_TTL_SECONDS
        );
        $provider = $this->zalo->send($phone, $otp, 'tvpverify' . $requestId . date('YmdHis'));

        $this->db->table('account_verification_requests')
            ->where('id', $requestId)
            ->update([
                'delivery_status' => $provider['ok'] ? 'sent' : 'failed',
                'provider_message_id' => $provider['message_id'],
                'provider_error_code' => $provider['error_code'] !== '' ? $provider['error_code'] : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->result(
            $provider['ok'],
            self::CHANNEL_ZALO,
            self::maskPhone($phone),
            $provider['reason']
        );
    }

    /**
     * @param array<string, mixed> $user
     * @return array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int}
     */
    private function sendEmailOtp(array $user, string $locale, bool $force): array
    {
        $email = strtolower(trim((string) ($user['email'] ?? '')));
        $maskedEmail = self::maskEmail($email);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->result(false, self::CHANNEL_EMAIL, $maskedEmail, 'invalid_email');
        }

        $rateLimit = $this->rateLimit((int) $user['id'], self::CHANNEL_EMAIL, $force);
        if ($rateLimit !== null) {
            return $this->result(false, self::CHANNEL_EMAIL, $maskedEmail, $rateLimit['reason'], $rateLimit['cooldown']);
        }

        $otp = $this->generateUniqueOtp();
        if ($otp === null) {
            return $this->result(false, self::CHANNEL_EMAIL, $maskedEmail, 'delivery_failed');
        }
        $requestId = $this->insertRequest(
            (int) $user['id'],
            self::CHANNEL_EMAIL,
            $email,
            self::hashToken($otp),
            self::OTP_TTL_SECONDS
        );
        $sent = $this->sendVerificationEmail($user, $locale, $otp);

        $this->db->table('account_verification_requests')
            ->where('id', $requestId)
            ->update([
                'delivery_status' => $sent ? 'sent' : 'failed',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->result($sent, self::CHANNEL_EMAIL, $maskedEmail, $sent ? 'sent' : 'delivery_failed');
    }

    private function insertRequest(int $userId, string $channel, string $recipient, string $tokenHash, int $ttl): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('account_verification_requests')->insert([
            'user_id' => $userId,
            'channel' => $channel,
            'recipient' => $recipient,
            'token_hash' => $tokenHash,
            'attempts' => 0,
            'max_attempts' => self::MAX_OTP_ATTEMPTS,
            'delivery_status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl),
            'last_sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function generateUniqueOtp(): ?string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $otp = (string) random_int(100000, 999999);
            $exists = $this->db->table('account_verification_requests')
                ->where('token_hash', self::hashToken($otp))
                ->countAllResults() > 0;

            if (! $exists) {
                return $otp;
            }
        }

        log_message('error', 'Unable to generate a unique account verification OTP after repeated attempts.');
        return null;
    }

    /**
     * @return array{reason:string,cooldown:int}|null
     */
    private function rateLimit(int $userId, string $channel, bool $force): ?array
    {
        $latest = $this->db->table('account_verification_requests')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (! $force && is_array($latest)) {
            $elapsed = time() - strtotime((string) $latest['last_sent_at']);
            if ($elapsed < self::RESEND_COOLDOWN_SECONDS) {
                return [
                    'reason' => 'cooldown',
                    'cooldown' => self::RESEND_COOLDOWN_SECONDS - max(0, $elapsed),
                ];
            }
        }

        $sentToday = $this->db->table('account_verification_requests')
            ->where('user_id', $userId)
            ->where('channel', $channel)
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->countAllResults();

        return $sentToday >= self::DAILY_SEND_LIMIT
            ? ['reason' => 'daily_limit', 'cooldown' => 0]
            : null;
    }

    /**
     * @return array{ok:bool,reason:string,user:?array}
     */
    private function activateUser(int $userId, string $channel, int $requestId): array
    {
        $now = date('Y-m-d H:i:s');
        $verifiedField = $channel === self::CHANNEL_ZALO ? 'phone_verified_at' : 'email_verified_at';

        $this->db->transStart();
        $this->db->table('users')
            ->where('id', $userId)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status' => 'active',
                $verifiedField => $now,
                'verification_channel' => $channel,
                'last_login_at' => $now,
                'updated_at' => $now,
            ]);
        $this->db->table('account_verification_requests')
            ->where('id', $requestId)
            ->where('verified_at', null)
            ->update(['verified_at' => $now, 'updated_at' => $now]);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return $this->verificationResult(false, 'database_error');
        }

        $user = (new UserModel())->find($userId);
        return is_array($user) && ($user['status'] ?? '') === 'active'
            ? ['ok' => true, 'reason' => 'verified', 'user' => $user]
            : $this->verificationResult(false, 'not_pending');
    }

    /**
     * @param array<string, mixed> $user
     */
    private function sendVerificationEmail(array $user, string $locale, string $otp): bool
    {
        $english = $locale === 'en';
        try {
            $email = service('email');
            $email->clear(true);
            $embeddedLogoUrl = '';
            $logoPath = FCPATH . 'assets/images/LOGO.png';

            if (is_file($logoPath) && $email->attach($logoPath, 'inline', 'travelplus-logo.png') !== false) {
                $logoCid = $email->setAttachmentCID($logoPath);
                if (is_string($logoCid) && $logoCid !== '') {
                    $embeddedLogoUrl = 'cid:' . $logoCid;
                }
            }

            $body = (new EmailTemplateService())->render(
                $english ? 'Account verification' : 'Xác thực tài khoản',
                $english ? 'Your Travel Plus verification code' : 'Mã xác thực Travel Plus',
                $english
                    ? 'Enter this 6-digit code on the registration screen to finish creating your account.'
                    : 'Nhập mã gồm 6 chữ số này tại màn hình đăng ký để hoàn tất tạo tài khoản.',
                [
                    $english ? 'Account' : 'Tài khoản' => (string) ($user['full_name'] ?? $user['email'] ?? ''),
                    $english ? 'Valid for' : 'Hiệu lực' => $english ? '5 minutes' : '5 phút',
                ],
                [],
                $english
                    ? 'Never share this code. If you did not create this account, you can ignore this email.'
                    : 'Không cung cấp mã này cho bất kỳ ai. Nếu bạn không thực hiện đăng ký, bạn có thể bỏ qua email.',
                '',
                '',
                true,
                $embeddedLogoUrl,
                $otp,
                $english ? 'Verification code' : 'Mã xác thực'
            );

            $email->setTo((string) $user['email']);
            $email->setSubject($english
                ? $otp . ' is your Travel Plus verification code'
                : $otp . ' là mã xác thực Travel Plus');
            $email->setMailType('html');
            $email->setMessage($body);

            return $email->send();
        } catch (\Throwable $exception) {
            log_message('warning', 'Account verification email failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int}
     */
    private function result(bool $sent, string $channel, string $recipient, string $reason, int $cooldown = 0): array
    {
        return compact('sent', 'channel', 'recipient', 'reason', 'cooldown');
    }

    /**
     * @return array{ok:bool,reason:string,user:?array}
     */
    private function verificationResult(bool $ok, string $reason): array
    {
        return ['ok' => $ok, 'reason' => $reason, 'user' => null];
    }
}
