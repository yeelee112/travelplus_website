<?php

namespace App\Services;

use Config\Zalo;

class ZaloOtpService
{
    private Zalo $config;
    private ZaloOaTokenService $tokens;

    public function __construct(?Zalo $config = null, ?ZaloOaTokenService $tokens = null)
    {
        $this->config = $config ?? config(Zalo::class);
        $this->tokens = $tokens ?? new ZaloOaTokenService($this->config);
    }

    public function isConfigured(): bool
    {
        return $this->readiness()['ready'];
    }

    /**
     * @return array{ready:bool,reason:string}
     */
    public function readiness(): array
    {
        if (! $this->config->otpEnabled) {
            return ['ready' => false, 'reason' => 'otp_disabled'];
        }
        if ($this->config->otpTemplateId === '') {
            return ['ready' => false, 'reason' => 'template_missing'];
        }
        if ($this->config->otpField === '') {
            return ['ready' => false, 'reason' => 'otp_field_missing'];
        }
        if (! $this->tokens->canProvideAccessToken()) {
            return ['ready' => false, 'reason' => 'oa_token_unavailable'];
        }

        return ['ready' => true, 'reason' => 'ready'];
    }

    /**
     * @return array{ok:bool,error_code:string,message_id:?string,reason:string,provider_message:string}
     */
    public function send(string $phone, string $otp, string $trackingId): array
    {
        if (! $this->isConfigured()) {
            return $this->failure('unconfigured');
        }

        $accessToken = $this->tokens->getAccessToken();
        if ($accessToken === '') {
            return $this->failure('token_unavailable');
        }

        $internationalPhone = self::toInternationalPhone($phone);
        if ($internationalPhone === '') {
            return $this->failure('invalid_phone');
        }

        $templateData = [$this->config->otpField => $otp];
        if ($this->config->otpExpiryField !== '') {
            $templateData[$this->config->otpExpiryField] = '5';
        }

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => $this->config->verifySsl,
                'http_errors' => false,
            ]);
            $response = $client->post($this->config->endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'access_token' => $accessToken,
                ],
                'json' => [
                    'phone' => $internationalPhone,
                    'template_id' => $this->config->otpTemplateId,
                    'template_data' => $templateData,
                    'tracking_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', $trackingId),
                ],
            ]);
            $payload = json_decode((string) $response->getBody(), true);
        } catch (\Throwable $exception) {
            log_message('warning', 'Zalo OTP transport failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->failure('transport_error');
        }

        if (! is_array($payload)) {
            return $this->failure('invalid_response');
        }

        $errorCode = (string) ($payload['error'] ?? 'unknown');
        $providerMessage = trim((string) ($payload['message'] ?? ''));
        if ($errorCode !== '0') {
            log_message('warning', 'Zalo OTP rejected with provider code {code}.', [
                'code' => $errorCode,
            ]);

            return [
                'ok' => false,
                'error_code' => $errorCode,
                'message_id' => null,
                'reason' => 'provider_rejected',
                'provider_message' => $providerMessage,
            ];
        }

        return [
            'ok' => true,
            'error_code' => '0',
            'message_id' => isset($payload['data']['msg_id']) ? (string) $payload['data']['msg_id'] : null,
            'reason' => 'sent',
            'provider_message' => $providerMessage,
        ];
    }

    public static function toInternationalPhone(string $phone): string
    {
        $normalized = VietnamPhoneService::normalize($phone);

        if (! VietnamPhoneService::isValid($normalized)) {
            return '';
        }

        return '84' . substr($normalized, 1);
    }

    /**
     * @return array{ok:bool,error_code:string,message_id:?string,reason:string,provider_message:string}
     */
    private function failure(string $reason): array
    {
        return [
            'ok' => false,
            'error_code' => '',
            'message_id' => null,
            'reason' => $reason,
            'provider_message' => '',
        ];
    }
}
