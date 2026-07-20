<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

final class LoyaltyPointService
{
    public const VND_PER_POINT = 10000;

    private const TABLE = 'loyalty_point_transactions';

    private ?BaseConnection $database = null;
    private ?bool $available = null;

    public function isAvailable(): bool
    {
        if ($this->available !== null) {
            return $this->available;
        }

        try {
            return $this->available = $this->database()->tableExists(self::TABLE);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to inspect loyalty point storage: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->available = false;
        }
    }

    public function calculatePoints(float $amountPaidVnd): int
    {
        return (int) floor(max(0, $amountPaidVnd) / self::VND_PER_POINT);
    }

    /**
     * @param list<array<string, mixed>> $bookings
     */
    public function syncBookings(array $bookings): void
    {
        if ($bookings === [] || ! $this->isAvailable()) {
            return;
        }

        $eligibleBookings = [];

        foreach ($bookings as $booking) {
            $bookingId = (int) ($booking['id'] ?? 0);
            $userId = (int) ($booking['user_id'] ?? 0);

            if ($bookingId > 0 && $userId > 0) {
                $eligibleBookings[$bookingId] = $booking;
            }
        }

        if ($eligibleBookings === []) {
            return;
        }

        $currentRows = $this->database()
            ->table(self::TABLE)
            ->select('booking_id, COALESCE(SUM(points), 0) AS current_points, COUNT(*) AS transaction_count', false)
            ->whereIn('booking_id', array_keys($eligibleBookings))
            ->groupBy('booking_id')
            ->get()
            ->getResultArray();

        $currentByBooking = [];

        foreach ($currentRows as $row) {
            $currentByBooking[(int) ($row['booking_id'] ?? 0)] = [
                'points' => (int) ($row['current_points'] ?? 0),
                'count' => (int) ($row['transaction_count'] ?? 0),
            ];
        }

        foreach ($eligibleBookings as $bookingId => $booking) {
            $current = $currentByBooking[$bookingId] ?? ['points' => 0, 'count' => 0];
            $status = strtolower(trim((string) ($booking['payment_status'] ?? '')));
            $amountPaid = max(0, (float) ($booking['amount_paid_vnd'] ?? 0));
            $targetPoints = $status === 'paid' ? $this->calculatePoints($amountPaid) : 0;
            $pointDelta = $targetPoints - $current['points'];

            if ($pointDelta === 0) {
                continue;
            }

            $eventSeed = implode('|', [
                'booking',
                $bookingId,
                $current['count'],
                $current['points'],
                $targetPoints,
                (string) ($booking['updated_at'] ?? ''),
            ]);

            $payload = [
                'user_id' => (int) $booking['user_id'],
                'booking_id' => $bookingId,
                'event_key' => hash('sha256', $eventSeed),
                'type' => $pointDelta > 0 ? 'booking_earned' : 'booking_reversed',
                'points' => $pointDelta,
                'amount_vnd' => $amountPaid,
                'description' => trim((string) ($booking['booking_code'] ?? '')) ?: 'Booking #' . $bookingId,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            try {
                $this->database()->table(self::TABLE)->insert($payload);
            } catch (\Throwable $exception) {
                if (! $this->eventExists($payload['event_key'])) {
                    log_message('error', 'Unable to synchronize loyalty points for booking {bookingId}: {message}', [
                        'bookingId' => $bookingId,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function syncBooking(array $booking): void
    {
        $this->syncBookings([$booking]);
    }

    public function balanceForUser(int $userId): ?int
    {
        if ($userId <= 0 || ! $this->isAvailable()) {
            return null;
        }

        $row = $this->database()
            ->table(self::TABLE)
            ->select('COALESCE(SUM(points), 0) AS balance', false)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return max(0, (int) ($row['balance'] ?? 0));
    }

    private function eventExists(string $eventKey): bool
    {
        return $this->database()
            ->table(self::TABLE)
            ->where('event_key', $eventKey)
            ->countAllResults() > 0;
    }

    private function database(): BaseConnection
    {
        return $this->database ??= db_connect();
    }
}
