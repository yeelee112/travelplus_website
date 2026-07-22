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
            return $this->available = (new DatabaseSchemaCacheService($this->database()))->tableExists(self::TABLE);
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to inspect loyalty point storage: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->available = false;
        }
    }

    public function calculatePoints(float $amountPaidVnd): int
    {
        return self::previewPoints($amountPaidVnd);
    }

    public static function previewPoints(float $amountVnd): int
    {
        return (int) floor(max(0, $amountVnd) / self::VND_PER_POINT);
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

            if ($targetPoints === $current['points']) {
                continue;
            }

            $this->syncEligibleBooking($booking, $targetPoints, $amountPaid);
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

    /**
     * @return list<array<string, mixed>>
     */
    public function historyForUser(int $userId, int $limit = 20): array
    {
        if ($userId <= 0 || ! $this->isAvailable()) {
            return [];
        }

        return $this->database()
            ->table(self::TABLE)
            ->select('id, booking_id, type, points, amount_vnd, description, created_at')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(max(1, min(100, $limit)))
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function syncEligibleBooking(array $booking, int $targetPoints, float $amountPaid): void
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $db = $this->database();
        $eventKey = '';

        try {
            $db->transBegin();
            $db->query('SELECT id FROM bookings WHERE id = ? FOR UPDATE', [$bookingId]);

            $currentRow = $db->table(self::TABLE)
                ->select('COALESCE(SUM(points), 0) AS current_points, COUNT(*) AS transaction_count', false)
                ->where('booking_id', $bookingId)
                ->get()
                ->getRowArray();
            $currentPoints = (int) ($currentRow['current_points'] ?? 0);
            $transactionCount = (int) ($currentRow['transaction_count'] ?? 0);
            $pointDelta = $targetPoints - $currentPoints;

            if ($pointDelta === 0) {
                $db->transCommit();

                return;
            }

            $eventKey = hash('sha256', implode('|', [
                'booking',
                $bookingId,
                $transactionCount,
                $currentPoints,
                $targetPoints,
            ]));

            $db->table(self::TABLE)->insert([
                'user_id' => (int) $booking['user_id'],
                'booking_id' => $bookingId,
                'event_key' => $eventKey,
                'type' => $pointDelta > 0 ? 'booking_earned' : 'booking_reversed',
                'points' => $pointDelta,
                'amount_vnd' => $amountPaid,
                'description' => trim((string) ($booking['booking_code'] ?? '')) ?: 'Booking #' . $bookingId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();

            if ($eventKey === '' || ! $this->eventExists($eventKey)) {
                log_message('error', 'Unable to synchronize loyalty points for booking {bookingId}: {message}', [
                    'bookingId' => $bookingId,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
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
