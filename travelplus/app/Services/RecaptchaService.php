<?php

namespace App\Services;

class RecaptchaService
{
    public function siteKey(): string
    {
        return $this->envValue('recaptcha.siteKey');
    }

    public function verify(string $token, string $expectedAction, string $remoteIp = ''): bool
    {
        $token = trim($token);
        $secretKey = $this->envValue('recaptcha.secretKey');
        if ($token === '' || $secretKey === '') {
            log_message('warning', 'reCAPTCHA verification skipped because token or secret key is missing.');
            return false;
        }

        try {
            $options = ['timeout' => 10];
            $caBundle = $this->envValue('recaptcha.caBundle');
            if ($caBundle !== '' && is_file($caBundle)) {
                $options['verify'] = $caBundle;
            }

            $formParams = [
                'secret' => $secretKey,
                'response' => $token,
            ];
            if (filter_var($remoteIp, FILTER_VALIDATE_IP)) {
                $formParams['remoteip'] = $remoteIp;
            }

            $response = \Config\Services::curlrequest($options)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                ['form_params' => $formParams]
            );
            $result = json_decode((string) $response->getBody(), true);
            if (! is_array($result) || empty($result['success'])) {
                return false;
            }

            $action = trim((string) ($result['action'] ?? ''));
            if ($expectedAction !== '' && $action !== $expectedAction) {
                log_message('warning', 'reCAPTCHA action mismatch. Expected {expected}, received {actual}.', [
                    'expected' => $expectedAction,
                    'actual' => $action,
                ]);
                return false;
            }

            return (float) ($result['score'] ?? 0) >= (float) env('recaptcha.minimumScore', 0.5);
        } catch (\Throwable $exception) {
            log_message('error', 'reCAPTCHA verification failed: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function envValue(string $key): string
    {
        return trim((string) env($key, ''), " \t\n\r\0\x0B\"'");
    }
}
