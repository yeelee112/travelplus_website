<?php

namespace App\Services;

use Config\Zalo;

class ZaloOtpService
{
    private Zalo $config;

    public function __construct(?Zalo $config = null)
    {
        $this->config = $config ?? config(Zalo::class);
    }

    public function isConfigured(): bool
    {
        return $this->config->otpEnabled
            && $this->config->accessToken !== ''
            && $this->config->otpTemplateId !== ''
            && $this->config->otpField !== '';
    }

    /**
     * @return array{ok:bool,error_code:string,message_id:?string,reason:string}
     */
    public function send(string $phone, string $otp, string $trackingId): array
    {
        if (! $this->isConfigured()) {
            return $this->failure('unconfigured');
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
                    'access_token' => $this->config->accessToken,
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
        if ($errorCode !== '0') {
            log_message('warning', 'Zalo OTP rejected with provider code {code}.', [
                'code' => $errorCode,
            ]);

            return [
                'ok' => false,
                'error_code' => $errorCode,
                'message_id' => null,
                'reason' => 'provider_rejected',
            ];
        }

        return [
            'ok' => true,
            'error_code' => '0',
            'message_id' => isset($payload['data']['msg_id']) ? (string) $payload['data']['msg_id'] : null,
            'reason' => 'sent',
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
     * @return array{ok:bool,error_code:string,message_id:?string,reason:string}
     */
    private function failure(string $reason): array
    {
        return [
            'ok' => false,
            'error_code' => '',
            'message_id' => null,
            'reason' => $reason,
        ];
    }
}
