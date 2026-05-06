<?php

namespace App\Services;

use RuntimeException;

class VietQrService
{
    private string $clientId;
    private string $apiKey;
    private string $accountNo;
    private string $accountName;
    private int $acqId;
    private string $template;
    private string $baseUrl;

    public function __construct()
    {
        $trimMask = " \t\n\r\0\x0B\"'";

        $this->clientId = trim((string) env('vietqr.clientId', ''), $trimMask);
        $this->apiKey = trim((string) env('vietqr.apiKey', ''), $trimMask);
        $this->accountNo = trim((string) env('vietqr.accountNo', ''), $trimMask);
        $this->accountName = trim((string) env('vietqr.accountName', ''), $trimMask);
        $this->acqId = (int) env('vietqr.acqId', 0);
        $this->template = trim((string) env('vietqr.template', 'compact'), $trimMask);
        $this->baseUrl = trim((string) env('vietqr.baseUrl', 'https://api.vietqr.io/v2'), $trimMask);
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== ''
            && $this->apiKey !== ''
            && $this->accountNo !== ''
            && $this->accountName !== ''
            && $this->acqId > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function generateQr(int $amount, string $addInfo): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('VietQR is not fully configured.');
        }

        if (! function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is not enabled.');
        }

        $payload = [
            'accountNo'   => $this->accountNo,
            'accountName' => $this->sanitizeText($this->accountName, 50),
            'acqId'       => $this->acqId,
            'amount'      => $amount,
            'addInfo'     => $this->sanitizeText($addInfo, 25),
            'template'    => $this->template,
        ];

        $ch = curl_init($this->baseUrl . '/generate');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'x-client-id: ' . $this->clientId,
                'x-api-key: ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('VietQR request failed. cURL #' . $errorNo . ': ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || ! is_array($data)) {
            throw new RuntimeException('VietQR returned an invalid response. HTTP ' . $httpCode . ': ' . $response);
        }

        if (($data['code'] ?? '') !== '00' || ! isset($data['data']) || ! is_array($data['data'])) {
            throw new RuntimeException((string) ($data['desc'] ?? 'VietQR request failed.'));
        }

        return $data['data'];
    }

    /**
     * @return array<string, scalar>
     */
    public function getReceiverMeta(): array
    {
        return [
            'accountNo'   => $this->accountNo,
            'accountName' => $this->accountName,
            'acqId'       => $this->acqId,
            'template'    => $this->template,
        ];
    }

    private function sanitizeText(string $value, int $maxLength): string
    {
        $value = trim($value);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($ascii) && $ascii !== '' ? $ascii : $value;
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9 ]+/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        $value = trim($value);

        return mb_substr($value, 0, $maxLength);
    }
}
