<?php

namespace App\Services;

use RuntimeException;

class PayPalSandboxService
{
    private string $clientId;
    private string $secret;
    private string $baseUrl;
    private string $caBundle;
    private bool $verifySsl;

    public function __construct()
    {
        $this->clientId = trim((string) env('paypal.clientId', ''), " `t`n`r`0`x0B\"'");
        $this->secret = trim((string) env('paypal.secret', ''), " `t`n`r`0`x0B\"'");
        $this->baseUrl = trim((string) env('paypal.baseUrl', 'https://api-m.sandbox.paypal.com'), " `t`n`r`0`x0B\"'");
        $this->caBundle = trim((string) env('paypal.caBundle', ''), " `t`n`r`0`x0B\"'");
        $this->verifySsl = filter_var(env('paypal.verifySsl', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->secret !== '';
    }

    /**
     * @param array<string, mixed> $booking
     * @return array<string, mixed>
     */
    public function createOrder(array $booking, string $amount, string $returnUrl, string $cancelUrl): array
    {
        $accessToken = $this->fetchAccessToken();

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'tour-booking-' . (string) ($booking['tour_id'] ?? '0'),
                'description' => (string) ($booking['tour_title'] ?? 'Travel Plus Booking'),
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $amount,
                ],
            ]],
            'application_context' => [
                'brand_name' => 'Travel Plus',
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        return $this->request('POST', '/v2/checkout/orders', $accessToken, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        $accessToken = $this->fetchAccessToken();

        return $this->request('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', $accessToken);
    }

    private function fetchAccessToken(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayPal sandbox credentials are missing.');
        }

        if (! function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is not enabled.');
        }

        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->clientId . ':' . $this->secret,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
        ]);

        $this->applySslOptions($ch);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('Could not connect to PayPal sandbox. cURL #' . $errorNo . ': ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || ! is_array($data) || empty($data['access_token'])) {
            $detail = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $response;
            throw new RuntimeException('PayPal sandbox authentication failed. HTTP ' . $httpCode . ': ' . $detail);
        }

        return (string) $data['access_token'];
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, string $accessToken, ?array $payload = null): array
    {
        if (! function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is not enabled.');
        }

        $ch = curl_init($this->baseUrl . $path);

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $this->applySslOptions($ch);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('PayPal sandbox request failed. cURL #' . $errorNo . ': ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || ! is_array($data)) {
            $detail = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $response;
            throw new RuntimeException('PayPal sandbox returned an invalid response. HTTP ' . $httpCode . ': ' . $detail);
        }

        return $data;
    }

    /**
     * @param resource $ch
     */
    private function applySslOptions($ch): void
    {
        if (! $this->verifySsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            return;
        }

        if ($this->caBundle !== '' && is_file($this->caBundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->caBundle);
        }
    }
}
