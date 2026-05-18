<?php

namespace App\Services;

use Config\Services;

class TourEnquiryNotificationService
{
    /**
     * @param array<string, mixed> $enquiry
     */
    public function sendEnquiryEmails(array $enquiry): bool
    {
        if (! $this->isConfigured()) {
            log_message('info', 'Tour enquiry email skipped because email config is incomplete.');
            return false;
        }

        $customerEmail = trim((string) ($enquiry['email'] ?? ''));
        $adminEmail = trim((string) env('booking.notifyEmail', env('email.recipients', '')));
        $sent = false;

        if ($customerEmail !== '') {
            $sent = $this->sendCustomerEmail($customerEmail, $enquiry) || $sent;
        }

        if ($adminEmail !== '') {
            $sent = $this->sendAdminEmail($adminEmail, $enquiry) || $sent;
        }

        return $sent;
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
     * @param array<string, mixed> $enquiry
     */
    private function sendCustomerEmail(string $to, array $enquiry): bool
    {
        $tourTitle = $this->e((string) ($enquiry['tour_title'] ?? ''));
        $message = <<<HTML
<h2>Xác nhận yêu cầu tư vấn</h2>
<p>Xin chào {$this->e((string) ($enquiry['full_name'] ?? 'Quý khách'))},</p>
<p>Travel Plus đã nhận được yêu cầu tư vấn tour của bạn. Bộ phận tư vấn sẽ liên hệ sớm nhất có thể.</p>
<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d8d8d8;">
    <tr><td><strong>Tour quan tâm</strong></td><td>{$tourTitle}</td></tr>
    <tr><td><strong>Ngày dự kiến đi</strong></td><td>{$this->e((string) ($enquiry['travel_date'] ?? '-'))}</td></tr>
    <tr><td><strong>Số lượng khách</strong></td><td>{$this->e((string) ($enquiry['travelers'] ?? '-'))}</td></tr>
    <tr><td><strong>Số điện thoại</strong></td><td>{$this->e((string) ($enquiry['phone'] ?? '-'))}</td></tr>
    <tr><td><strong>Nội dung</strong></td><td>{$this->e((string) ($enquiry['message'] ?? ''))}</td></tr>
</table>
<p>Nếu cần bổ sung thông tin, bạn chỉ cần phản hồi lại email này.</p>
HTML;

        return $this->deliver($to, 'Xác nhận yêu cầu tư vấn tour - ' . $tourTitle, $message);
    }

    /**
     * @param array<string, mixed> $enquiry
     */
    private function sendAdminEmail(string $to, array $enquiry): bool
    {
        $tourTitle = $this->e((string) ($enquiry['tour_title'] ?? ''));
        $message = <<<HTML
<h2>Có yêu cầu tư vấn tour mới</h2>
<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#d8d8d8;">
    <tr><td><strong>Họ và tên</strong></td><td>{$this->e((string) ($enquiry['full_name'] ?? ''))}</td></tr>
    <tr><td><strong>Email</strong></td><td>{$this->e((string) ($enquiry['email'] ?? ''))}</td></tr>
    <tr><td><strong>Số điện thoại</strong></td><td>{$this->e((string) ($enquiry['phone'] ?? ''))}</td></tr>
    <tr><td><strong>Tour quan tâm</strong></td><td>{$tourTitle}</td></tr>
    <tr><td><strong>Ngày dự kiến đi</strong></td><td>{$this->e((string) ($enquiry['travel_date'] ?? '-'))}</td></tr>
    <tr><td><strong>Số lượng khách</strong></td><td>{$this->e((string) ($enquiry['travelers'] ?? '-'))}</td></tr>
    <tr><td><strong>Link tour</strong></td><td>{$this->e((string) ($enquiry['tour_link'] ?? '-'))}</td></tr>
    <tr><td><strong>Nội dung</strong></td><td>{$this->e((string) ($enquiry['message'] ?? ''))}</td></tr>
</table>
HTML;

        return $this->deliver($to, 'Yêu cầu tư vấn tour mới - ' . $tourTitle, $message, (string) ($enquiry['email'] ?? ''), (string) ($enquiry['full_name'] ?? ''));
    }

    private function deliver(string $to, string $subject, string $message, string $replyToEmail = '', string $replyToName = ''): bool
    {
        $emailService = Services::email();
        $fromEmail = (string) env('email.fromEmail', '');
        $fromName = (string) env('email.fromName', 'TravelPlus');

        $emailService->clear(true);
        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($to);
        $emailService->setSubject($subject);
        $emailService->setMessage($message);

        if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $emailService->setReplyTo($replyToEmail, $replyToName !== '' ? $replyToName : $replyToEmail);
        }

        if (! $emailService->send()) {
            log_message('error', 'Tour enquiry email send failed: {error}', [
                'error' => print_r($emailService->printDebugger(['headers', 'subject']), true),
            ]);
            return false;
        }

        return true;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
