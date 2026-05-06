<?php

namespace App\Services;

use Config\Services;

class BookingNotificationService
{
    /**
     * @param array<string, mixed> $booking
     */
    public function sendBookingEmails(array $booking): void
    {
        if (! $this->isConfigured()) {
            log_message('info', 'Booking email skipped because email config is incomplete.');
            return;
        }

        $customerEmail = trim((string) ($booking['customer_email'] ?? ''));
        $adminEmail = trim((string) env('booking.notifyEmail', env('email.recipients', '')));

        if ($customerEmail !== '') {
            $this->sendCustomerEmail($customerEmail, $booking);
        }

        if ($adminEmail !== '') {
            $this->sendAdminEmail($adminEmail, $booking);
        }
    }

    public function isConfigured(): bool
    {
        $fromEmail = trim((string) env('email.fromEmail', ''));
        $protocol = trim((string) env('email.protocol', 'mail'));

        if ($fromEmail === '') {
            return false;
        }

        if ($protocol !== 'smtp') {
            return true;
        }

        return trim((string) env('email.SMTPHost', '')) !== ''
            && trim((string) env('email.SMTPUser', '')) !== ''
            && trim((string) env('email.SMTPPass', '')) !== '';
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendCustomerEmail(string $email, array $booking): void
    {
        $subject = $this->buildCustomerSubject($booking);
        $message = $this->buildCustomerMessage($booking);
        $this->deliver($email, $subject, $message);
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendAdminEmail(string $email, array $booking): void
    {
        $subject = 'Booking moi: ' . (string) ($booking['booking_code'] ?? '');
        $message = $this->buildAdminMessage($booking);
        $this->deliver($email, $subject, $message);
    }

    private function deliver(string $to, string $subject, string $message): void
    {
        $emailService = Services::email();
        $fromEmail = (string) env('email.fromEmail', '');
        $fromName = (string) env('email.fromName', 'TravelPlus');

        $emailService->clear(true);
        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($to);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        if (! $emailService->send()) {
            log_message('error', 'Booking email send failed: {error}', [
                'error' => print_r($emailService->printDebugger(['headers', 'subject']), true),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildCustomerSubject(array $booking): string
    {
        $status = strtolower((string) ($booking['payment_status'] ?? ''));
        $code = (string) ($booking['booking_code'] ?? '');

        if ($status === 'paid') {
            return 'Xac nhan thanh toan booking ' . $code;
        }

        return 'Xac nhan da nhan booking ' . $code;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildCustomerMessage(array $booking): string
    {
        $name = $this->e((string) ($booking['customer_name'] ?? 'Quy khach'));
        $code = $this->e((string) ($booking['booking_code'] ?? ''));
        $tourTitle = $this->e((string) ($booking['tour_title'] ?? ''));
        $departure = $this->e((string) ($booking['departure_label'] ?? '-'));
        $paymentMethod = strtoupper($this->e((string) ($booking['payment_method'] ?? '-')));
        $status = strtolower((string) ($booking['payment_status'] ?? ''));
        $amount = number_format((float) ($status === 'paid' ? ($booking['amount_paid_vnd'] ?? 0) : ($booking['amount_due_vnd'] ?? 0)), 0, ',', '.') . ' VND';
        $statusText = $status === 'paid'
            ? 'Booking cua ban da thanh toan thanh cong.'
            : 'Booking cua ban da duoc ghi nhan va dang cho doi soat chuyen khoan.';

        return <<<HTML
<h2>Thong tin booking</h2>
<p>Xin chao {$name},</p>
<p>{$statusText}</p>
<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d8d8d8;">
    <tr><td><strong>Ma booking</strong></td><td>{$code}</td></tr>
    <tr><td><strong>Tour</strong></td><td>{$tourTitle}</td></tr>
    <tr><td><strong>Ngay khoi hanh</strong></td><td>{$departure}</td></tr>
    <tr><td><strong>Phuong thuc</strong></td><td>{$paymentMethod}</td></tr>
    <tr><td><strong>So tien</strong></td><td>{$amount}</td></tr>
</table>
<p>Cam on ban da dat tour tai TravelPlus.</p>
HTML;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildAdminMessage(array $booking): string
    {
        $status = $this->e((string) ($booking['payment_status'] ?? '-'));
        $paymentMethod = strtoupper($this->e((string) ($booking['payment_method'] ?? '-')));
        $amountDue = number_format((float) ($booking['amount_due_vnd'] ?? 0), 0, ',', '.') . ' VND';
        $amountPaid = number_format((float) ($booking['amount_paid_vnd'] ?? 0), 0, ',', '.') . ' VND';

        return <<<HTML
<h2>Co booking moi</h2>
<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d8d8d8;">
    <tr><td><strong>Ma booking</strong></td><td>{$this->e((string) ($booking['booking_code'] ?? ''))}</td></tr>
    <tr><td><strong>Khach hang</strong></td><td>{$this->e((string) ($booking['customer_name'] ?? ''))}</td></tr>
    <tr><td><strong>Email</strong></td><td>{$this->e((string) ($booking['customer_email'] ?? ''))}</td></tr>
    <tr><td><strong>So dien thoai</strong></td><td>{$this->e((string) ($booking['customer_phone'] ?? ''))}</td></tr>
    <tr><td><strong>Tour</strong></td><td>{$this->e((string) ($booking['tour_title'] ?? ''))}</td></tr>
    <tr><td><strong>Khoi hanh</strong></td><td>{$this->e((string) ($booking['departure_label'] ?? '-'))}</td></tr>
    <tr><td><strong>Phuong thuc</strong></td><td>{$paymentMethod}</td></tr>
    <tr><td><strong>Trang thai</strong></td><td>{$status}</td></tr>
    <tr><td><strong>Can thu</strong></td><td>{$amountDue}</td></tr>
    <tr><td><strong>Da thu</strong></td><td>{$amountPaid}</td></tr>
</table>
HTML;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
