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
        $tourTitle = (string) ($enquiry['tour_title'] ?? '');
        $template = new EmailTemplateService();
        $message = $template->render(
            'Xác nhận yêu cầu',
            'Travel Plus đã nhận yêu cầu tư vấn',
            'Xin chào ' . ((string) ($enquiry['full_name'] ?? '') ?: 'Quý khách') . ', đội ngũ Travel Plus sẽ kiểm tra thông tin và liên hệ lại sớm nhất.',
            [
                'Tour quan tâm' => $tourTitle,
                'Ngày dự kiến đi' => (string) ($enquiry['travel_date'] ?? '-'),
            ],
            [
                ['label' => 'Số lượng khách', 'value' => (string) ($enquiry['travelers'] ?? '-')],
                ['label' => 'Số điện thoại', 'value' => (string) ($enquiry['phone'] ?? '-')],
                ['label' => 'Email', 'value' => (string) ($enquiry['email'] ?? '-')],
            ],
            (string) ($enquiry['message'] ?? ''),
            'Xem lại tour',
            (string) ($enquiry['tour_link'] ?? '')
        );

        return $this->deliver($to, 'Xác nhận yêu cầu tư vấn tour - ' . $tourTitle, $message);
    }

    /**
     * @param array<string, mixed> $enquiry
     */
    private function sendAdminEmail(string $to, array $enquiry): bool
    {
        $tourTitle = (string) ($enquiry['tour_title'] ?? '');
        $template = new EmailTemplateService();
        $message = $template->render(
            'Lead tư vấn tour',
            'Có yêu cầu tư vấn tour mới',
            'Khách vừa gửi form tư vấn trên website Travel Plus. Vui lòng kiểm tra và phản hồi sớm.',
            [
                'Khách hàng' => (string) ($enquiry['full_name'] ?? ''),
                'Tour quan tâm' => $tourTitle,
            ],
            [
                ['label' => 'Email', 'value' => (string) ($enquiry['email'] ?? '')],
                ['label' => 'Số điện thoại', 'value' => (string) ($enquiry['phone'] ?? '')],
                ['label' => 'Ngày dự kiến đi', 'value' => (string) ($enquiry['travel_date'] ?? '-')],
                ['label' => 'Số lượng khách', 'value' => (string) ($enquiry['travelers'] ?? '-')],
                ['label' => 'Link tour', 'value' => (string) ($enquiry['tour_link'] ?? '-')],
            ],
            (string) ($enquiry['message'] ?? ''),
            'Mở tour',
            (string) ($enquiry['tour_link'] ?? '')
        );

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
}
