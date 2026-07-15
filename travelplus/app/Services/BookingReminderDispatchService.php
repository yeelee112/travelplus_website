<?php

namespace App\Services;

use App\Models\BookingModel;

class BookingReminderDispatchService
{
    /**
     * @param list<int> $bookingIds
     * @return array{sent:int,skipped:int,candidates:list<array<string,string>>,errors:list<string>}
     */
    public function dispatch(string $type = 'all', int $limit = 50, bool $dryRun = true, array $bookingIds = []): array
    {
        $type = in_array($type, ['all', 'payment', 'document'], true) ? $type : 'all';
        $limit = max(1, min(200, $limit));
        $bookingIds = array_values(array_unique(array_filter(array_map('intval', $bookingIds), static fn(int $id): bool => $id > 0)));
        $notifier = new BookingNotificationService();
        $result = [
            'sent' => 0,
            'skipped' => 0,
            'candidates' => [],
            'errors' => [],
        ];

        if ($bookingIds !== []) {
            $type = $type === 'all' ? 'payment' : $type;
            $limit = min($limit, count($bookingIds));
        }

        if (! $notifier->hasEmailLogTable()) {
            $result['errors'][] = 'Thiếu bảng booking_email_logs. Vui lòng chạy SQL thủ công trước khi gửi reminder.';

            return $result;
        }

        if (! $dryRun && ! $notifier->isConfigured()) {
            $result['errors'][] = 'Email chưa được cấu hình. Kiểm tra email.fromEmail và SMTP.';

            return $result;
        }

        if ($type === 'all' || $type === 'payment') {
            $this->processPaymentReminders($notifier, $limit, $dryRun, $result, $bookingIds);
        }

        if ($type === 'all' || $type === 'document') {
            $this->processDocumentReminders($notifier, $limit, $dryRun, $result, $bookingIds);
        }

        return $result;
    }

    /**
     * @param array{sent:int,skipped:int,candidates:list<array<string,string>>,errors:list<string>} $result
     * @param list<int> $bookingIds
     */
    private function processPaymentReminders(BookingNotificationService $notifier, int $limit, bool $dryRun, array &$result, array $bookingIds = []): void
    {
        $hours = max(1, (int) env('booking.paymentReminderAfterHours', 12));
        $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));
        $query = (new BookingModel())
            ->whereIn('payment_status', ['pending_payment', 'pending_transfer'])
            ->where('customer_email IS NOT NULL', null, false)
            ->where('customer_email !=', '')
            ->where('created_at <=', $cutoff);

        if ($bookingIds !== []) {
            $query->whereIn('id', $bookingIds);
        }

        $bookings = $query->orderBy('created_at', 'ASC')->findAll($limit);

        foreach ($bookings as $booking) {
            $this->handleBooking(
                $notifier,
                $booking,
                BookingNotificationService::TYPE_PAYMENT_REMINDER,
                'payment',
                'Nhắc thanh toán',
                $dryRun,
                $result
            );
        }
    }

    /**
     * @param array{sent:int,skipped:int,candidates:list<array<string,string>>,errors:list<string>} $result
     * @param list<int> $bookingIds
     */
    private function processDocumentReminders(BookingNotificationService $notifier, int $limit, bool $dryRun, array &$result, array $bookingIds = []): void
    {
        $days = max(1, (int) env('booking.documentReminderBeforeDays', 14));
        $today = new \DateTimeImmutable('today');
        $latest = $today->modify('+' . $days . ' days');
        $query = (new BookingModel())
            ->where('payment_status', 'paid')
            ->where('customer_email IS NOT NULL', null, false)
            ->where('customer_email !=', '')
            ->where('departure_label IS NOT NULL', null, false)
            ->where('departure_label !=', '');

        if ($bookingIds !== []) {
            $query->whereIn('id', $bookingIds);
        }

        $bookings = $query->orderBy('departure_label', 'ASC')->findAll($limit * 3);
        $processed = 0;

        foreach ($bookings as $booking) {
            if ($processed >= $limit) {
                break;
            }

            $departureDate = $this->parseDepartureDate((string) ($booking['departure_label'] ?? ''));

            if ($departureDate === null || $departureDate < $today || $departureDate > $latest) {
                continue;
            }

            $processed++;
            $this->handleBooking(
                $notifier,
                $booking,
                BookingNotificationService::TYPE_DOCUMENT_REMINDER,
                'document',
                'Nhắc hồ sơ',
                $dryRun,
                $result,
                $departureDate->format('Y-m-d')
            );
        }
    }

    /**
     * @param array<string,mixed> $booking
     * @param array{sent:int,skipped:int,candidates:list<array<string,string>>,errors:list<string>} $result
     */
    private function handleBooking(
        BookingNotificationService $notifier,
        array $booking,
        string $emailType,
        string $type,
        string $label,
        bool $dryRun,
        array &$result,
        string $extra = ''
    ): void {
        if ($notifier->hasSuccessfulCustomerEmail($booking, $emailType)) {
            $result['skipped']++;

            return;
        }

        $result['candidates'][] = [
            'id' => (string) ($booking['id'] ?? ''),
            'type' => $type,
            'label' => $label,
            'booking_code' => (string) ($booking['booking_code'] ?? ''),
            'customer' => (string) ($booking['customer_name'] ?? ''),
            'email' => (string) ($booking['customer_email'] ?? ''),
            'tour' => (string) ($booking['tour_title'] ?? ''),
            'status' => (string) ($booking['payment_status'] ?? ''),
            'departure' => (string) ($booking['departure_label'] ?? ''),
            'extra' => $extra,
        ];

        if ($dryRun) {
            return;
        }

        $sent = $type === 'document'
            ? $notifier->sendDocumentReminder($booking)
            : $notifier->sendPaymentReminder($booking);

        $sent ? $result['sent']++ : $result['skipped']++;
    }

    private function parseDepartureDate(string $label): ?\DateTimeImmutable
    {
        $label = trim($label);

        if ($label === '') {
            return null;
        }

        $patterns = [
            '/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/' => 'Y-m-d',
            '/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/' => 'd/m/Y',
        ];

        foreach ($patterns as $pattern => $format) {
            if (! preg_match($pattern, $label, $matches)) {
                continue;
            }

            $dateText = $format === 'Y-m-d'
                ? $matches[1] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[3], 2, '0', STR_PAD_LEFT)
                : str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '/' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '/' . $matches[3];
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $dateText);

            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        $timestamp = strtotime($label);

        return $timestamp !== false ? (new \DateTimeImmutable())->setTimestamp($timestamp)->setTime(0, 0) : null;
    }
}
