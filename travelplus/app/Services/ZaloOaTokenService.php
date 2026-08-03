<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Zalo;

final class ZaloOaTokenService
{
    private const TABLE = 'zalo_oa_connections';

    private Zalo $config;
    private BaseConnection $db;

    public function __construct(?Zalo $config = null, ?BaseConnection $db = null)
    {
        $this->config = $config ?? config(Zalo::class);
        $this->db = $db ?? db_connect();
    }

    public function isOauthConfigured(): bool
    {
        return $this->config->appId !== ''
            && $this->config->appSecret !== ''
            && filter_var($this->config->callbackUrl, FILTER_VALIDATE_URL) !== false;
    }

    public function hasStorage(): bool
    {
        try {
            return $this->db->tableExists(self::TABLE);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to inspect Zalo OA token storage: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function authorizationUrl(string $state, string $codeChallenge = ''): string
    {
        $parameters = [
            'app_id' => $this->config->appId,
            'redirect_uri' => $this->config->callbackUrl,
            'state' => $state,
        ];
        if ($codeChallenge !== '') {
            $parameters['code_challenge'] = $codeChallenge;
        }

        $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

        return rtrim($this->config->permissionEndpoint, '?') . '?' . $query;
    }

    /**
     * @return array{ok:bool,reason:string,oa_id:string,oa_name:string,error_code:string}
     */
    public function connect(string $authorizationCode, string $callbackOaId, int $adminUserId, string $codeVerifier = ''): array
    {
        if (! $this->isOauthConfigured()) {
            return $this->result(false, 'unconfigured');
        }

        if (! $this->hasStorage()) {
            return $this->result(false, 'storage_missing');
        }

        $tokenRequest = [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'app_id' => $this->config->appId,
        ];
        if ($codeVerifier !== '') {
            $tokenRequest['code_verifier'] = $codeVerifier;
        }
        $tokenResult = $this->requestToken($tokenRequest);

        if (! $tokenResult['ok']) {
            return $this->result(false, $tokenResult['reason'], '', '', $tokenResult['error_code']);
        }

        $profile = $this->fetchOaProfile($tokenResult['access_token']);
        $oaId = trim($callbackOaId) ?: $tokenResult['oa_id'] ?: $profile['oa_id'];
        $oaName = $profile['oa_name'];

        if ($oaId === '') {
            return $this->result(false, 'oa_identity_missing');
        }

        try {
            $encryptedAccessToken = $this->encrypt($tokenResult['access_token']);
            $encryptedRefreshToken = $this->encrypt($tokenResult['refresh_token']);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to encrypt Zalo OA credentials: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->result(false, 'encryption_error');
        }

        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + max(60, $tokenResult['expires_in']));
        $data = [
            'oa_id' => $oaId,
            'oa_name' => $oaName,
            'app_id' => $this->config->appId,
            'access_token_enc' => $encryptedAccessToken,
            'refresh_token_enc' => $encryptedRefreshToken,
            'access_token_expires_at' => $expiresAt,
            'status' => 'active',
            'last_error' => null,
            'connected_by' => $adminUserId > 0 ? $adminUserId : null,
            'updated_at' => $now,
        ];

        try {
            $builder = $this->db->table(self::TABLE);
            $existing = $builder->where('app_id', $this->config->appId)->get()->getRowArray();

            $this->db->transStart();
            $this->db->table(self::TABLE)
                ->where('app_id', $this->config->appId)
                ->update(['status' => 'inactive', 'updated_at' => $now]);

            if (is_array($existing)) {
                $this->db->table(self::TABLE)->where('id', (int) $existing['id'])->update($data);
            } else {
                $data['created_at'] = $now;
                $this->db->table(self::TABLE)->insert($data);
            }

            $this->db->transComplete();

            if (! $this->db->transStatus()) {
                return $this->result(false, 'database_error');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to save Zalo OA connection: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->result(false, 'database_error');
        }

        return $this->result(true, 'connected', $oaId, $oaName);
    }

    public function canProvideAccessToken(): bool
    {
        if ($this->config->accessToken !== '') {
            return true;
        }

        return $this->isOauthConfigured() && $this->activeConnection() !== null;
    }

    public function getAccessToken(): string
    {
        $connection = $this->activeConnection();

        if ($connection === null) {
            return $this->config->accessToken;
        }

        $accessToken = $this->decrypt((string) ($connection['access_token_enc'] ?? ''));
        $expiresAt = strtotime((string) ($connection['access_token_expires_at'] ?? '')) ?: 0;

        if ($accessToken !== '' && $expiresAt > time() + $this->config->tokenRefreshLeeway) {
            return $accessToken;
        }

        return $this->refreshConnection($connection);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $connection = $this->activeConnection();

        return [
            'app_id' => $this->config->appId,
            'callback_url' => $this->config->callbackUrl,
            'secret_configured' => $this->config->appSecret !== '',
            'storage_ready' => $this->hasStorage(),
            'connected' => $connection !== null,
            'oa_id' => (string) ($connection['oa_id'] ?? ''),
            'oa_name' => (string) ($connection['oa_name'] ?? ''),
            'expires_at' => (string) ($connection['access_token_expires_at'] ?? ''),
            'updated_at' => (string) ($connection['updated_at'] ?? ''),
            'last_error' => (string) ($connection['last_error'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function activeConnection(): ?array
    {
        if (! $this->hasStorage()) {
            return null;
        }

        try {
            $row = $this->db->table(self::TABLE)
                ->where('app_id', $this->config->appId)
                ->where('status', 'active')
                ->orderBy('id', 'DESC')
                ->get(1)
                ->getRowArray();

            return is_array($row) ? $row : null;
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to load Zalo OA connection: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $connection
     */
    private function refreshConnection(array $connection): string
    {
        $refreshToken = $this->decrypt((string) ($connection['refresh_token_enc'] ?? ''));
        if ($refreshToken === '') {
            $this->recordError((int) $connection['id'], 'refresh_token_missing');
            return '';
        }

        $tokenResult = $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'app_id' => $this->config->appId,
        ]);

        if (! $tokenResult['ok']) {
            $this->recordError((int) $connection['id'], $tokenResult['reason'] . ':' . $tokenResult['error_code']);
            return '';
        }

        $newRefreshToken = $tokenResult['refresh_token'] !== '' ? $tokenResult['refresh_token'] : $refreshToken;

        try {
            $this->db->table(self::TABLE)->where('id', (int) $connection['id'])->update([
                'access_token_enc' => $this->encrypt($tokenResult['access_token']),
                'refresh_token_enc' => $this->encrypt($newRefreshToken),
                'access_token_expires_at' => date('Y-m-d H:i:s', time() + max(60, $tokenResult['expires_in'])),
                'last_error' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to persist refreshed Zalo OA token: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return '';
        }

        return $tokenResult['access_token'];
    }

    /**
     * @param array<string, string> $form
     * @return array{ok:bool,reason:string,error_code:string,access_token:string,refresh_token:string,expires_in:int,oa_id:string}
     */
    private function requestToken(array $form): array
    {
        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 12,
                'connect_timeout' => 5,
                'verify' => $this->config->verifySsl,
                'http_errors' => false,
            ]);
            $response = $client->post($this->config->tokenEndpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'secret_key' => $this->config->appSecret,
                ],
                'form_params' => $form,
            ]);
            $payload = json_decode((string) $response->getBody(), true);
        } catch (\Throwable $exception) {
            log_message('warning', 'Zalo OA token request failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->tokenFailure('transport_error');
        }

        if (! is_array($payload)) {
            return $this->tokenFailure('invalid_response');
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $accessToken = trim((string) ($data['access_token'] ?? ''));
        $errorCode = (string) ($payload['error'] ?? $payload['error_code'] ?? '0');

        if ($accessToken === '' || ! in_array($errorCode, ['', '0'], true)) {
            log_message('warning', 'Zalo OA token request rejected with provider code {code}.', [
                'code' => $errorCode,
            ]);

            return $this->tokenFailure('provider_rejected', $errorCode);
        }

        return [
            'ok' => true,
            'reason' => 'received',
            'error_code' => '0',
            'access_token' => $accessToken,
            'refresh_token' => trim((string) ($data['refresh_token'] ?? '')),
            'expires_in' => max(60, (int) ($data['expires_in'] ?? 86400)),
            'oa_id' => trim((string) ($data['oa_id'] ?? $payload['oa_id'] ?? '')),
        ];
    }

    /**
     * @return array{oa_id:string,oa_name:string}
     */
    private function fetchOaProfile(string $accessToken): array
    {
        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => $this->config->verifySsl,
                'http_errors' => false,
            ]);
            $response = $client->get($this->config->oaInfoEndpoint, [
                'headers' => ['access_token' => $accessToken, 'Accept' => 'application/json'],
            ]);
            $payload = json_decode((string) $response->getBody(), true);
        } catch (\Throwable $exception) {
            log_message('warning', 'Unable to read connected Zalo OA profile: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return ['oa_id' => '', 'oa_name' => ''];
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        return [
            'oa_id' => trim((string) ($data['oa_id'] ?? $data['id'] ?? '')),
            'oa_name' => trim((string) ($data['name'] ?? '')),
        ];
    }

    private function encrypt(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $value,
            'aes-256-gcm',
            hash('sha256', $this->config->appSecret, true),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Unable to encrypt Zalo OA token.');
        }

        return 'v1:' . base64_encode($iv . $tag . $ciphertext);
    }

    private function decrypt(string $value): string
    {
        if (! str_starts_with($value, 'v1:')) {
            return '';
        }

        $decoded = base64_decode(substr($value, 3), true);
        if ($decoded === false || strlen($decoded) < 29) {
            return '';
        }

        $plaintext = openssl_decrypt(
            substr($decoded, 28),
            'aes-256-gcm',
            hash('sha256', $this->config->appSecret, true),
            OPENSSL_RAW_DATA,
            substr($decoded, 0, 12),
            substr($decoded, 12, 16)
        );

        return $plaintext === false ? '' : $plaintext;
    }

    private function recordError(int $connectionId, string $error): void
    {
        try {
            $this->db->table(self::TABLE)->where('id', $connectionId)->update([
                'last_error' => mb_substr($error, 0, 500),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to record Zalo OA token error: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{ok:bool,reason:string,oa_id:string,oa_name:string,error_code:string}
     */
    private function result(bool $ok, string $reason, string $oaId = '', string $oaName = '', string $errorCode = ''): array
    {
        return [
            'ok' => $ok,
            'reason' => $reason,
            'oa_id' => $oaId,
            'oa_name' => $oaName,
            'error_code' => $errorCode,
        ];
    }

    /**
     * @return array{ok:bool,reason:string,error_code:string,access_token:string,refresh_token:string,expires_in:int,oa_id:string}
     */
    private function tokenFailure(string $reason, string $errorCode = ''): array
    {
        return [
            'ok' => false,
            'reason' => $reason,
            'error_code' => $errorCode,
            'access_token' => '',
            'refresh_token' => '',
            'expires_in' => 0,
            'oa_id' => '',
        ];
    }
}
