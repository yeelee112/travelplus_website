<?php

namespace App\Services;

use RuntimeException;

class VnpayGatewayService
{
    private string $tmnCode;
    private string $hashSecret;
    private string $paymentUrl;
    private string $version;
    private int $expireMinutes;

    public function __construct()
    {
        $this->tmnCode = $this->normalizeEnvString((string) env('vnpay.tmnCode', ''));
        $this->hashSecret = $this->normalizeEnvString((string) env('vnpay.hashSecret', ''));
        $this->paymentUrl = $this->normalizeEnvString((string) env('vnpay.paymentUrl', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'));
        $this->version = $this->normalizeEnvString((string) env('vnpay.version', '2.1.0'));
        $this->expireMinutes = max(5, (int) env('vnpay.expireMinutes', 30));
    }

    public function isConfigured(): bool
    {
        return $this->tmnCode !== '' && $this->hashSecret !== '' && $this->paymentUrl !== '';
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function createCardPaymentUrl(
        array $booking,
        int $amountVnd,
        string $returnUrl,
        string $ipAddress,
        string $locale,
        string $txnRef,
        ?string $bankCode = null
    ): string {
        if (! $this->isConfigured()) {
            throw new RuntimeException('VNPAY credentials are missing.');
        }

        $params = [
            'vnp_Version' => $this->version,
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => (string) ($amountVnd * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => $this->sanitizeOrderInfo((string) ($booking['tour_title'] ?? 'Thanh toan tour Travel Plus')),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => $locale === 'en' ? 'en' : 'vn',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => $this->normalizeIpAddress($ipAddress),
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+' . $this->expireMinutes . ' minutes')),
        ];

        $bankCode = $bankCode !== null ? trim($bankCode) : '';

        if ($bankCode !== '') {
            $params['vnp_BankCode'] = $bankCode;
        }

        $query = $this->buildQuery($params);
        $secureHash = hash_hmac('sha512', $query, $this->hashSecret);

        return $this->paymentUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    /**
     * @param array<string, string|int|float|null> $inputData
     * @return array{is_valid: bool, is_success: bool, data: array<string, string>, amount_vnd: int, txn_ref: string, transaction_no: string, response_code: string, transaction_status: string, message: string}
     */
    public function validateReturnData(array $inputData, string $locale = 'vi'): array
    {
        $vnpData = [];

        foreach ($inputData as $key => $value) {
            if (strpos((string) $key, 'vnp_') !== 0) {
                continue;
            }

            $vnpData[(string) $key] = (string) ($value ?? '');
        }

        $receivedHash = $vnpData['vnp_SecureHash'] ?? '';
        unset($vnpData['vnp_SecureHash'], $vnpData['vnp_SecureHashType']);

        ksort($vnpData);
        $query = $this->buildQuery($vnpData);
        $expectedHash = hash_hmac('sha512', $query, $this->hashSecret);

        $responseCode = $vnpData['vnp_ResponseCode'] ?? '';
        $transactionStatus = $vnpData['vnp_TransactionStatus'] ?? '';
        $isValid = $receivedHash !== '' && hash_equals($expectedHash, $receivedHash);
        $isSuccess = $isValid && $responseCode === '00' && $transactionStatus === '00';

        return [
            'is_valid' => $isValid,
            'is_success' => $isSuccess,
            'data' => $vnpData,
            'amount_vnd' => (int) round(((float) ($vnpData['vnp_Amount'] ?? 0)) / 100),
            'txn_ref' => (string) ($vnpData['vnp_TxnRef'] ?? ''),
            'transaction_no' => (string) ($vnpData['vnp_TransactionNo'] ?? ''),
            'response_code' => $responseCode,
            'transaction_status' => $transactionStatus,
            'message' => $this->mapResponseMessage($responseCode, $transactionStatus, $locale),
        ];
    }

    /**
     * @param array<string, string> $params
     */
    private function buildQuery(array $params): string
    {
        ksort($params);
        $pairs = [];

        foreach ($params as $key => $value) {
            $pairs[] = urlencode($key) . '=' . urlencode($value);
        }

        return implode('&', $pairs);
    }

    private function sanitizeOrderInfo(string $text): string
    {
        $text = trim($text);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        if (is_string($ascii) && $ascii !== '') {
            $text = $ascii;
        }

        $text = preg_replace('/[^A-Za-z0-9\s\-_]/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? trim($text);

        if ($text === '') {
            return 'Thanh toan tour Travel Plus';
        }

        return mb_substr($text, 0, 255);
    }

    private function normalizeIpAddress(string $ipAddress): string
    {
        $ipAddress = trim($ipAddress);

        if ($ipAddress === '' || $ipAddress === '::1' || $ipAddress === '127.0.0.1') {
            return '127.0.0.1';
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ipAddress;
        }

        return '127.0.0.1';
    }

    private function normalizeEnvString(string $value): string
    {
        $value = trim($value);

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        return trim($value);
    }

    private function mapResponseMessage(string $responseCode, string $transactionStatus, string $locale = 'vi'): string
    {
        $isEnglish = $locale === 'en';

        if ($responseCode === '00' && $transactionStatus === '00') {
            return $isEnglish
                ? 'VNPAY payment completed successfully.'
                : 'Thanh toán VNPAY đã hoàn tất thành công.';
        }

        return match ($responseCode) {
            '07' => $isEnglish ? 'The transaction was suspected as fraudulent.' : 'Giao dịch bị nghi ngờ là gian lận.',
            '09' => $isEnglish ? 'The card or bank account is not enabled for online payments.' : 'Thẻ hoặc tài khoản ngân hàng chưa được kích hoạt thanh toán trực tuyến.',
            '10' => $isEnglish ? 'Authentication failed too many times.' : 'Xác thực thất bại quá số lần cho phép.',
            '11' => $isEnglish ? 'Payment session expired.' : 'Phiên thanh toán đã hết hạn.',
            '12' => $isEnglish ? 'The card or bank account is locked.' : 'Thẻ hoặc tài khoản ngân hàng đang bị khóa.',
            '13' => $isEnglish ? 'Incorrect OTP was entered.' : 'Mã OTP nhập không chính xác.',
            '24' => $isEnglish ? 'The customer canceled the payment.' : 'Khách hàng đã hủy giao dịch thanh toán.',
            '51' => $isEnglish ? 'Insufficient balance.' : 'Tài khoản không đủ số dư.',
            '65' => $isEnglish ? 'The bank account exceeded the daily transaction limit.' : 'Tài khoản ngân hàng đã vượt hạn mức giao dịch trong ngày.',
            '75' => $isEnglish ? 'The issuing bank is under maintenance.' : 'Ngân hàng phát hành đang bảo trì.',
            '79' => $isEnglish ? 'Too many incorrect payment password attempts.' : 'Nhập sai mật khẩu thanh toán quá số lần cho phép.',
            '97' => $isEnglish ? 'The VNPAY signature could not be verified.' : 'Không thể xác thực chữ ký bảo mật của VNPAY.',
            '99' => $isEnglish ? 'The transaction could not be completed.' : 'Giao dịch không thể hoàn tất.',
            default => $isEnglish ? 'The transaction could not be completed via VNPAY.' : 'Giao dịch không thể hoàn tất qua VNPAY.',
        };
    }
}
