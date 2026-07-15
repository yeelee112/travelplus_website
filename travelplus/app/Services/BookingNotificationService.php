<?php

namespace App\Services;

use Config\Services;

class BookingNotificationService
{
    public const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';
    public const TYPE_PAYMENT_CONFIRMATION = 'payment_confirmation';
    public const TYPE_PAYMENT_REMINDER = 'payment_reminder';
    public const TYPE_DOCUMENT_REMINDER = 'document_reminder';
    public const TYPE_STATUS_UPDATE = 'status_update';
    public const TYPE_ADMIN_ALERT = 'admin_booking_alert';

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
            $type = strtolower((string) ($booking['payment_status'] ?? '')) === 'paid'
                ? self::TYPE_PAYMENT_CONFIRMATION
                : self::TYPE_BOOKING_CONFIRMATION;

            $this->sendCustomerEmail($customerEmail, $booking, $type);
        }

        if ($adminEmail !== '') {
            $this->sendAdminEmail($adminEmail, $booking);
        }
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function sendPaymentReminder(array $booking): bool
    {
        return $this->sendCustomerTransactionalEmail(
            $booking,
            self::TYPE_PAYMENT_REMINDER,
            'Nhắc thanh toán booking ' . (string) ($booking['booking_code'] ?? ''),
            $this->buildPaymentReminderMessage($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function sendDocumentReminder(array $booking): bool
    {
        return $this->sendCustomerTransactionalEmail(
            $booking,
            self::TYPE_DOCUMENT_REMINDER,
            'Nhắc chuẩn bị hồ sơ cho booking ' . (string) ($booking['booking_code'] ?? ''),
            $this->buildDocumentReminderMessage($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function sendStatusUpdateEmail(array $booking, string $status, string $note = ''): bool
    {
        $type = self::TYPE_STATUS_UPDATE . ':' . $status;

        return $this->sendCustomerTransactionalEmail(
            $booking,
            $type,
            'Cập nhật trạng thái booking ' . (string) ($booking['booking_code'] ?? ''),
            $this->buildStatusUpdateMessage($booking, $status, $note)
        );
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
    public function hasSuccessfulCustomerEmail(array $booking, string $type): bool
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $recipient = trim((string) ($booking['customer_email'] ?? ''));

        if ($bookingId <= 0 || $recipient === '' || ! $this->hasEmailLogTable()) {
            return false;
        }

        return db_connect()
            ->table('booking_email_logs')
            ->where('booking_id', $bookingId)
            ->where('email_type', $type)
            ->where('recipient_email', $recipient)
            ->where('status', 'sent')
            ->countAllResults() > 0;
    }

    public function hasEmailLogTable(): bool
    {
        try {
            return db_connect()->tableExists('booking_email_logs');
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to check booking_email_logs table: {error}', ['error' => $exception->getMessage()]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendCustomerEmail(string $email, array $booking, string $type): bool
    {
        return $this->sendAndLog(
            $booking,
            $type,
            $email,
            $this->buildCustomerSubject($booking),
            $this->buildCustomerMessage($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendAdminEmail(string $email, array $booking): bool
    {
        return $this->sendAndLog(
            $booking,
            self::TYPE_ADMIN_ALERT,
            $email,
            'Booking mới: ' . (string) ($booking['booking_code'] ?? ''),
            $this->buildAdminMessage($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendCustomerTransactionalEmail(array $booking, string $type, string $subject, string $message): bool
    {
        if (! $this->isConfigured()) {
            log_message('info', 'Booking email skipped because email config is incomplete.');

            return false;
        }

        $customerEmail = trim((string) ($booking['customer_email'] ?? ''));

        if ($customerEmail === '') {
            return false;
        }

        return $this->sendAndLog($booking, $type, $customerEmail, $subject, $message);
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function sendAndLog(array $booking, string $type, string $to, string $subject, string $message): bool
    {
        $result = $this->deliver($to, $subject, $message);

        $this->logEmail($booking, $type, $to, $subject, $result['ok'] ? 'sent' : 'failed', $result['error']);

        return $result['ok'];
    }

    /**
     * @return array{ok:bool,error:string}
     */
    private function deliver(string $to, string $subject, string $message): array
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
            $error = print_r($emailService->printDebugger(['headers', 'subject']), true);
            log_message('error', 'Booking email send failed: {error}', ['error' => $error]);

            return ['ok' => false, 'error' => $error];
        }

        return ['ok' => true, 'error' => ''];
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function logEmail(array $booking, string $type, string $recipient, string $subject, string $status, string $error = ''): void
    {
        if (! $this->hasEmailLogTable()) {
            return;
        }

        try {
            db_connect()->table('booking_email_logs')->insert([
                'booking_id' => (int) ($booking['id'] ?? 0) ?: null,
                'booking_code' => (string) ($booking['booking_code'] ?? ''),
                'email_type' => $type,
                'recipient_email' => $recipient,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $error !== '' ? substr($error, 0, 1000) : null,
                'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to write booking email log: {error}', ['error' => $exception->getMessage()]);
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
    private function buildPaymentReminderMessage(array $booking): string
    {
        $name = (string) ($booking['customer_name'] ?? 'Quý khách');
        $amount = number_format((float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0), 0, ',', '.') . ' VND';
        $method = strtoupper((string) ($booking['payment_method'] ?? '-'));
        $template = new EmailTemplateService();

        return $template->render(
            'Nhắc thanh toán',
            'Booking của bạn đang chờ thanh toán',
            'Xin chào ' . $name . ', Travel Plus đang giữ thông tin booking của bạn. Vui lòng hoàn tất thanh toán để đội ngũ tư vấn tiếp tục xử lý dịch vụ.',
            [
                'Mã booking' => (string) ($booking['booking_code'] ?? ''),
                'Cần thanh toán' => $amount,
            ],
            [
                ['label' => 'Tour', 'value' => (string) ($booking['tour_title'] ?? '')],
                ['label' => 'Ngày khởi hành', 'value' => (string) ($booking['departure_label'] ?? '-')],
                ['label' => 'Phương thức', 'value' => $method],
                ['label' => 'Trạng thái', 'value' => $this->statusLabel((string) ($booking['payment_status'] ?? ''))],
            ],
            'Nếu bạn đã chuyển khoản, vui lòng giữ lại chứng từ hoặc liên hệ Travel Plus để được đối soát nhanh hơn.',
            'Xem booking',
            $this->bookingUrl($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildDocumentReminderMessage(array $booking): string
    {
        $name = (string) ($booking['customer_name'] ?? 'Quý khách');
        $template = new EmailTemplateService();

        return $template->render(
            'Nhắc chuẩn bị hồ sơ',
            'Chuẩn bị thông tin cho chuyến đi sắp tới',
            'Xin chào ' . $name . ', booking của bạn đã được ghi nhận thanh toán. Travel Plus gửi email này để nhắc bạn chuẩn bị các thông tin cần thiết trước ngày khởi hành.',
            [
                'Mã booking' => (string) ($booking['booking_code'] ?? ''),
                'Ngày khởi hành' => (string) ($booking['departure_label'] ?? '-'),
            ],
            [
                ['label' => 'Tour', 'value' => (string) ($booking['tour_title'] ?? '')],
                ['label' => 'Số khách', 'value' => $this->travelerSummary($booking)],
                ['label' => 'Email liên hệ', 'value' => (string) ($booking['customer_email'] ?? '')],
                ['label' => 'Số điện thoại', 'value' => (string) ($booking['customer_phone'] ?? '')],
            ],
            "Bạn nên chuẩn bị hộ chiếu/giấy tờ tùy thân còn hạn, thông tin xuất hóa đơn nếu cần, yêu cầu ăn uống đặc biệt và các giấy tờ visa hoặc bảo hiểm theo tư vấn viên Travel Plus.",
            'Xem booking',
            $this->bookingUrl($booking)
        );
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function buildStatusUpdateMessage(array $booking, string $status, string $note): string
    {
        $name = (string) ($booking['customer_name'] ?? 'Quý khách');
        $template = new EmailTemplateService();

        return $template->render(
            'Cập nhật booking',
            'Trạng thái booking của bạn vừa được cập nhật',
            'Xin chào ' . $name . ', Travel Plus vừa cập nhật trạng thái booking của bạn. Thông tin mới nhất nằm bên dưới.',
            [
                'Mã booking' => (string) ($booking['booking_code'] ?? ''),
                'Trạng thái' => $this->statusLabel($status),
            ],
            [
                ['label' => 'Tour', 'value' => (string) ($booking['tour_title'] ?? '')],
                ['label' => 'Ngày khởi hành', 'value' => (string) ($booking['departure_label'] ?? '-')],
                ['label' => 'Phương thức', 'value' => strtoupper((string) ($booking['payment_method'] ?? '-'))],
                ['label' => 'Đã thanh toán', 'value' => number_format((float) ($booking['amount_paid_vnd'] ?? 0), 0, ',', '.') . ' VND'],
            ],
            $note,
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

    private function statusLabel(string $status): string
    {
        return [
            'pending_payment' => 'Chờ thanh toán',
            'pending_transfer' => 'Chờ đối soát chuyển khoản',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
        ][$status] ?? ($status !== '' ? $status : '-');
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function travelerSummary(array $booking): string
    {
        $adult = (int) ($booking['adult_quantity'] ?? 0);
        $child = (int) ($booking['child_quantity'] ?? 0);
        $infant = (int) ($booking['infant_quantity'] ?? 0);
        $parts = [];

        if ($adult > 0) {
            $parts[] = $adult . ' người lớn';
        }

        if ($child > 0) {
            $parts[] = $child . ' trẻ em';
        }

        if ($infant > 0) {
            $parts[] = $infant . ' em bé';
        }

        return $parts !== [] ? implode(', ', $parts) : '-';
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
