<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Owns the lifecycle of a coupon after a booking has been created.
 *
 * Passport vouchers are reserved before the customer leaves for a payment
 * gateway, consumed only after a successful payment, and returned when the
 * booking fails or is cancelled. Generic promotion codes keep their existing
 * one-time usage counter behaviour.
 */
final class BookingDiscountSettlementService
{
    private const VOUCHER_TABLE = 'loyalty_reward_vouchers';
    private const PROMOTION_TABLE = 'promotion_codes';
    private const RESERVATION_MINUTES = 120;

    private ?BaseConnection $database = null;

    /** @return array{ok: bool, message: string} */
    public function reserveForBooking(array $booking): array
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);
        $bookingId = (int) ($booking['id'] ?? 0);
        $userId = (int) ($booking['user_id'] ?? 0);

        if ($bookingId <= 0 || ! $this->isAvailable()) {
            return ['ok' => true, 'message' => ''];
        }

        $db = $this->database();

        try {
            $db->transBegin();

            if ($couponId <= 0) {
                $this->releaseOtherReservations($db, $bookingId, 0);
                $db->transCommit();

                return ['ok' => true, 'message' => ''];
            }

            $voucher = $db->query(
                'SELECT * FROM ' . self::VOUCHER_TABLE . ' WHERE promotion_code_id = ? FOR UPDATE',
                [$couponId]
            )->getRowArray();

            // This is a regular promotion code, so there is no Passport row to reserve.
            if (! is_array($voucher)) {
                $this->releaseOtherReservations($db, $bookingId, $couponId);
                $db->transCommit();

                return ['ok' => true, 'message' => ''];
            }

            if ($userId <= 0 || (int) ($voucher['user_id'] ?? 0) !== $userId) {
                $db->transRollback();

                return ['ok' => false, 'message' => 'Voucher Passport không thuộc tài khoản đang đặt tour.'];
            }

            $status = strtolower((string) ($voucher['status'] ?? 'issued'));
            $reservedBookingId = (int) ($voucher['booking_id'] ?? 0);

            if ($status === 'used') {
                $db->transRollback();

                return ['ok' => false, 'message' => 'Voucher Passport này đã được sử dụng.'];
            }

            if ($status === 'reserved' && $reservedBookingId > 0 && $reservedBookingId !== $bookingId) {
                $db->transRollback();

                return ['ok' => false, 'message' => 'Voucher đang được giữ cho một booking khác. Vui lòng hoàn tất hoặc hủy booking đó trước.'];
            }

            $now = date('Y-m-d H:i:s');
            $db->table(self::VOUCHER_TABLE)
                ->where('id', (int) $voucher['id'])
                ->update([
                    'status' => 'reserved',
                    'booking_id' => $bookingId,
                    'reserved_at' => $now,
                    'reservation_expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::RESERVATION_MINUTES . ' minutes')),
                    'released_at' => null,
                    'updated_at' => $now,
                ]);

            $this->releaseOtherReservations($db, $bookingId, $couponId);

            $db->transCommit();

            return ['ok' => true, 'message' => ''];
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Unable to reserve booking coupon: {message}', ['message' => $exception->getMessage()]);

            return ['ok' => false, 'message' => 'Chưa thể giữ voucher lúc này. Vui lòng thử lại.'];
        }
    }

    public function syncTransition(array $before, array $after): void
    {
        $action = self::transitionAction(
            (string) ($before['payment_status'] ?? ''),
            (string) ($after['payment_status'] ?? '')
        );

        if ($action === 'consume') {
            $this->consumeCoupon($after);
        } elseif ($action === 'restore') {
            $this->restoreCoupon($after);
        } elseif ($action === 'release') {
            $this->releaseReservation($after);
        }
    }

    public static function transitionAction(string $beforeStatus, string $afterStatus): string
    {
        $beforeStatus = strtolower(trim($beforeStatus));
        $afterStatus = strtolower(trim($afterStatus));

        if ($beforeStatus !== 'paid' && $afterStatus === 'paid') {
            return 'consume';
        }
        if ($beforeStatus === 'paid' && $afterStatus !== 'paid') {
            return 'restore';
        }
        if ($beforeStatus !== 'paid' && in_array($afterStatus, ['failed', 'cancelled'], true)) {
            return 'release';
        }

        return 'none';
    }

    public function releaseExpiredReservations(): void
    {
        if (! $this->isAvailable()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = $this->database()->table(self::VOUCHER_TABLE)
            ->select('id, booking_id')
            ->where('status', 'reserved')
            ->where('reservation_expires_at <', $now)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $this->releaseExpiredReservation(
                (int) $row['id'],
                (int) ($row['booking_id'] ?? 0),
                $now
            );
        }
    }

    private function releaseExpiredReservation(int $voucherId, int $bookingId, string $now): void
    {
        $db = $this->database();
        $booking = null;

        try {
            $db->transBegin();

            // Keep the lock order identical to coupon settlement: booking first,
            // voucher second. This prevents a late gateway callback from racing
            // an expiry cleanup and returning a voucher that was just consumed.
            if ($bookingId > 0 && $db->tableExists('bookings')) {
                $booking = $db->query(
                    'SELECT * FROM bookings WHERE id = ? FOR UPDATE',
                    [$bookingId]
                )->getRowArray();
            }

            $voucher = $db->query(
                'SELECT * FROM ' . self::VOUCHER_TABLE . ' WHERE id = ? FOR UPDATE',
                [$voucherId]
            )->getRowArray();

            if (! is_array($voucher)
                || strtolower((string) ($voucher['status'] ?? '')) !== 'reserved'
                || (int) ($voucher['booking_id'] ?? 0) !== $bookingId
                || trim((string) ($voucher['reservation_expires_at'] ?? '')) === ''
                || (string) $voucher['reservation_expires_at'] >= $now) {
                $db->transCommit();
                return;
            }

            if (is_array($booking) && strtolower((string) ($booking['payment_status'] ?? '')) === 'paid') {
                $db->transCommit();
                $this->consumeCoupon($booking);
                return;
            }

            $this->markVoucherIssued($voucherId);
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Unable to release expired Passport voucher {voucherId}: {message}', [
                'voucherId' => $voucherId,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function consumeCoupon(array $booking): void
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);
        if ($couponId <= 0 || ! $this->promotionAvailable()) {
            return;
        }

        $db = $this->database();
        try {
            $db->transBegin();
            $lockedBooking = $db->query(
                'SELECT id, coupon_settled_at FROM bookings WHERE id = ? FOR UPDATE',
                [(int) ($booking['id'] ?? 0)]
            )->getRowArray();
            if (is_array($lockedBooking) && trim((string) ($lockedBooking['coupon_settled_at'] ?? '')) !== '') {
                $db->transCommit();
                return;
            }

            $promotion = $db->query(
                'SELECT id, used_count FROM ' . self::PROMOTION_TABLE . ' WHERE id = ? FOR UPDATE',
                [$couponId]
            )->getRowArray();

            if (! is_array($promotion)) {
                $db->transRollback();
                return;
            }

            $voucher = $this->lockedVoucher($couponId);
            if (is_array($voucher) && strtolower((string) ($voucher['status'] ?? '')) === 'used') {
                $db->transCommit();
                return;
            }

            $db->table(self::PROMOTION_TABLE)
                ->where('id', $couponId)
                ->update(['used_count' => max(0, (int) ($promotion['used_count'] ?? 0)) + 1]);

            if (is_array($voucher)) {
                $now = date('Y-m-d H:i:s');
                $db->table(self::VOUCHER_TABLE)
                    ->where('id', (int) $voucher['id'])
                    ->update([
                        'status' => 'used',
                        'booking_id' => (int) ($booking['id'] ?? 0) ?: null,
                        'reservation_expires_at' => null,
                        'used_at' => $now,
                        'released_at' => null,
                        'updated_at' => $now,
                    ]);
            }

            if (is_array($lockedBooking)) {
                $db->table('bookings')
                    ->where('id', (int) $lockedBooking['id'])
                    ->update(['coupon_settled_at' => date('Y-m-d H:i:s')]);
            }

            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('critical', 'Unable to consume booking coupon for booking {bookingId}: {message}', [
                'bookingId' => (int) ($booking['id'] ?? 0),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function restoreCoupon(array $booking): void
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);
        if ($couponId <= 0 || ! $this->promotionAvailable()) {
            return;
        }

        $db = $this->database();
        try {
            $db->transBegin();
            $lockedBooking = $db->query(
                'SELECT id, coupon_settled_at FROM bookings WHERE id = ? FOR UPDATE',
                [(int) ($booking['id'] ?? 0)]
            )->getRowArray();
            if (! is_array($lockedBooking) || trim((string) ($lockedBooking['coupon_settled_at'] ?? '')) === '') {
                $db->transCommit();
                return;
            }

            $promotion = $db->query(
                'SELECT id, used_count FROM ' . self::PROMOTION_TABLE . ' WHERE id = ? FOR UPDATE',
                [$couponId]
            )->getRowArray();

            if (is_array($promotion)) {
                $db->table(self::PROMOTION_TABLE)
                    ->where('id', $couponId)
                    ->update(['used_count' => max(0, (int) ($promotion['used_count'] ?? 0) - 1)]);
            }

            $voucher = $this->lockedVoucher($couponId);
            if (is_array($voucher) && (int) ($voucher['booking_id'] ?? 0) === (int) ($booking['id'] ?? 0)) {
                $this->markVoucherIssued((int) $voucher['id']);
            }

            $db->table('bookings')
                ->where('id', (int) $lockedBooking['id'])
                ->update(['coupon_settled_at' => null]);

            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Unable to restore booking coupon: {message}', ['message' => $exception->getMessage()]);
        }
    }

    private function releaseReservation(array $booking): void
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);
        if ($couponId <= 0 || ! $this->isAvailable()) {
            return;
        }

        $voucher = $this->database()->table(self::VOUCHER_TABLE)
            ->where('promotion_code_id', $couponId)
            ->where('booking_id', (int) ($booking['id'] ?? 0))
            ->where('status', 'reserved')
            ->get()
            ->getRowArray();

        if (is_array($voucher)) {
            $this->markVoucherIssued((int) $voucher['id']);
        }
    }

    private function markVoucherIssued(int $voucherId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->database()->table(self::VOUCHER_TABLE)
            ->where('id', $voucherId)
            ->update([
                'status' => 'issued',
                'booking_id' => null,
                'reserved_at' => null,
                'reservation_expires_at' => null,
                'used_at' => null,
                'released_at' => $now,
                'updated_at' => $now,
            ]);
    }

    private function releaseOtherReservations(BaseConnection $db, int $bookingId, int $exceptPromotionId): void
    {
        $builder = $db->table(self::VOUCHER_TABLE)
            ->where('booking_id', $bookingId)
            ->where('status', 'reserved');

        if ($exceptPromotionId > 0) {
            $builder->where('promotion_code_id !=', $exceptPromotionId);
        }

        $now = date('Y-m-d H:i:s');
        $builder->update([
            'status' => 'issued',
            'booking_id' => null,
            'reserved_at' => null,
            'reservation_expires_at' => null,
            'used_at' => null,
            'released_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function lockedVoucher(int $promotionId): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $row = $this->database()->query(
            'SELECT * FROM ' . self::VOUCHER_TABLE . ' WHERE promotion_code_id = ? FOR UPDATE',
            [$promotionId]
        )->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function promotionAvailable(): bool
    {
        return $this->database()->tableExists(self::PROMOTION_TABLE);
    }

    private function isAvailable(): bool
    {
        if (! $this->database()->tableExists(self::VOUCHER_TABLE)) {
            return false;
        }

        $fields = $this->database()->getFieldNames(self::VOUCHER_TABLE);

        return in_array('booking_id', $fields, true)
            && in_array('reservation_expires_at', $fields, true)
            && in_array('used_at', $fields, true);
    }

    private function database(): BaseConnection
    {
        return $this->database ??= db_connect();
    }
}
