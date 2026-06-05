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
        $subject = 'Booking mới: ' . (string) ($booking['booking_code'] ?? '');
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
            return 'Xác nhận thanh toán booking ' . $code;
        }

        return 'Xác nhận đã nhận booking ' . $code;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildCustomerMessage(array $booking): string
    {
        $name = (string) ($booking['customer_name'] ?? 'Quý khách');
        $code = (string) ($booking['booking_code'] ?? '');
        $tourTitle = (string) ($booking['tour_title'] ?? '');
        $departure = (string) ($booking['departure_label'] ?? '-');
        $paymentMethod = strtoupper((string) ($booking['payment_method'] ?? '-'));
        $status = strtolower((string) ($booking['payment_status'] ?? ''));
        $amount = number_format((float) ($status === 'paid' ? ($booking['amount_paid_vnd'] ?? 0) : ($booking['amount_due_vnd'] ?? 0)), 0, ',', '.') . ' VND';
        $isPaid = $status === 'paid';
        $template = new EmailTemplateService();

        return $template->render(
            $isPaid ? 'Thanh toán thành công' : 'Đã ghi nhận booking',
            $isPaid ? 'Booking của bạn đã thanh toán thành công' : 'Travel Plus đã ghi nhận booking của bạn',
            'Xin chào ' . $name . ', cảm ơn bạn đã đặt dịch vụ tại Travel Plus. Thông tin booking được tóm tắt bên dưới.',
            [
                'Mã booking' => $code,
                'Số tiền' => $amount,
            ],
            [
                ['label' => 'Tour', 'value' => $tourTitle],
                ['label' => 'Ngày khởi hành', 'value' => $departure],
                ['label' => 'Phương thức', 'value' => $paymentMethod],
                ['label' => 'Trạng thái', 'value' => $isPaid ? 'Đã thanh toán' : 'Chờ đối soát'],
            ],
            '',
            'Xem booking',
            $this->bookingUrl($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildAdminMessage(array $booking): string
    {
        $status = (string) ($booking['payment_status'] ?? '-');
        $paymentMethod = strtoupper((string) ($booking['payment_method'] ?? '-'));
        $amountDue = number_format((float) ($booking['amount_due_vnd'] ?? 0), 0, ',', '.') . ' VND';
        $amountPaid = number_format((float) ($booking['amount_paid_vnd'] ?? 0), 0, ',', '.') . ' VND';
        $template = new EmailTemplateService();

        return $template->render(
            'Booking mới',
            'Có booking mới từ website',
            'Khách vừa hoàn tất bước đặt tour/thanh toán trên website Travel Plus. Vui lòng kiểm tra và xử lý trong hệ thống.',
            [
                'Mã booking' => (string) ($booking['booking_code'] ?? ''),
                'Khách hàng' => (string) ($booking['customer_name'] ?? ''),
            ],
            [
                ['label' => 'Email', 'value' => (string) ($booking['customer_email'] ?? '')],
                ['label' => 'Số điện thoại', 'value' => (string) ($booking['customer_phone'] ?? '')],
                ['label' => 'Tour', 'value' => (string) ($booking['tour_title'] ?? '')],
                ['label' => 'Khởi hành', 'value' => (string) ($booking['departure_label'] ?? '-')],
                ['label' => 'Phương thức', 'value' => $paymentMethod],
                ['label' => 'Trạng thái', 'value' => $status],
                ['label' => 'Cần thu', 'value' => $amountDue],
                ['label' => 'Đã thu', 'value' => $amountPaid],
            ],
            '',
            'Mở booking',
            $this->bookingUrl($booking)
        );
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function bookingUrl(array $booking): string
    {
        $code = trim((string) ($booking['booking_code'] ?? ''));

        return $code !== '' ? site_url('booking/success/' . rawurlencode($code)) : site_url('/');
    }
}
