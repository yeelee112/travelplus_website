<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Builds a single Passport breakdown for booking confirmation and detail views.
 */
final class BookingPassportSummaryService
{
    private const POINT_TABLE = 'loyalty_point_transactions';
    private const VOUCHER_TABLE = 'loyalty_reward_vouchers';

    private ?BaseConnection $database = null;

    /**
     * @param array<string, mixed> $booking
     * @return array<string, mixed>
     */
    public function summarize(array $booking): array
    {
        $bookingId = (int) ($booking['id'] ?? 0);
        $userId = (int) ($booking['user_id'] ?? 0);
        $paymentStatus = strtolower(trim((string) ($booking['payment_status'] ?? '')));
        $amountPaid = max(0, (float) ($booking['amount_paid_vnd'] ?? 0));
        $amountDue = max(0, (float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0));
        $membershipDiscount = max(0, (float) ($booking['membership_discount_amount_vnd'] ?? 0));
        $couponDiscount = max(0, (float) ($booking['discount_amount_vnd'] ?? 0));

        $summary = [
            'visible' => $userId > 0,
            'state' => match (true) {
                $paymentStatus === 'paid' => 'earned',
                in_array($paymentStatus, ['cancelled', 'failed', 'rejected'], true) => 'reversed',
                default => 'pending',
            },
            'earned_points' => 0,
            'reversed_points' => 0,
            'net_points' => 0,
            'preview_points' => LoyaltyPointService::previewPoints($paymentStatus === 'paid' ? $amountPaid : $amountDue),
            'available_points' => 0,
            'qualifying_points' => 0,
            'current_tier' => ['key' => 'member', 'minimum_points' => 0],
            'next_tier' => null,
            'progress' => 0,
            'remaining_points' => 0,
            'tier_up' => false,
            'previous_tier_key' => 'member',
            'booking_tier_after_key' => 'member',
            'membership_tier_key' => trim((string) ($booking['membership_tier_key'] ?? 'member')) ?: 'member',
            'membership_discount_rate' => min(3.0, max(0, (float) ($booking['membership_discount_rate'] ?? 0))),
            'membership_discount_amount_vnd' => $membershipDiscount,
            'coupon_discount_amount_vnd' => $couponDiscount,
            'total_benefit_vnd' => $membershipDiscount + $couponDiscount,
            'voucher' => null,
        ];

        if ($userId <= 0 || $bookingId <= 0) {
            return $summary;
        }

        try {
            $db = $this->database();
            $pointRows = [];
            if ($db->tableExists(self::POINT_TABLE)) {
                $pointRows = $db->table(self::POINT_TABLE)
                    ->select('id, type, points, amount_vnd, created_at')
                    ->where('booking_id', $bookingId)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
            }

            $earnedPoints = 0;
            $reversedPoints = 0;
            $netPoints = 0;
            $lastTransactionId = 0;
            foreach ($pointRows as $pointRow) {
                $points = (int) ($pointRow['points'] ?? 0);
                $netPoints += $points;
                $lastTransactionId = max($lastTransactionId, (int) ($pointRow['id'] ?? 0));
                if ($points > 0 && (string) ($pointRow['type'] ?? '') === 'booking_earned') {
                    $earnedPoints += $points;
                } elseif ($points < 0) {
                    $reversedPoints += abs($points);
                }
            }

            $loyaltyPoints = new LoyaltyPointService();
            $availablePoints = (int) ($loyaltyPoints->balanceForUser($userId) ?? 0);
            $qualifyingPoints = (int) ($loyaltyPoints->qualifyingPointsForUser($userId) ?? 0);
            $membership = (new LoyaltyMembershipService())->buildSnapshotFromCounts(
                0,
                0,
                0,
                $availablePoints,
                $qualifyingPoints
            );

            $qualifyingAtBooking = $qualifyingPoints;
            if ($lastTransactionId > 0 && $db->tableExists(self::POINT_TABLE)) {
                $qualifyingAtBookingRow = $db->table(self::POINT_TABLE)
                    ->select('COALESCE(SUM(points), 0) AS points', false)
                    ->where('user_id', $userId)
                    ->whereIn('type', ['booking_earned', 'booking_reversed'])
                    ->where('id <=', $lastTransactionId)
                    ->get()
                    ->getRowArray();
                $qualifyingAtBooking = max(0, (int) ($qualifyingAtBookingRow['points'] ?? 0));
            }

            $beforeBookingQualifying = max(0, $qualifyingAtBooking - max(0, $netPoints));
            $beforeMembership = (new LoyaltyMembershipService())->buildSnapshotFromCounts(
                0,
                0,
                0,
                0,
                $beforeBookingQualifying
            );
            $afterBookingMembership = (new LoyaltyMembershipService())->buildSnapshotFromCounts(
                0,
                0,
                0,
                0,
                $qualifyingAtBooking
            );

            $currentTierKey = (string) ($afterBookingMembership['current_tier']['key'] ?? 'member');
            $previousTierKey = (string) ($beforeMembership['current_tier']['key'] ?? 'member');
            $summary['earned_points'] = $earnedPoints;
            $summary['reversed_points'] = $reversedPoints;
            $summary['net_points'] = $netPoints;
            $summary['preview_points'] = $earnedPoints > 0 ? $earnedPoints : $summary['preview_points'];
            $summary['available_points'] = $availablePoints;
            $summary['qualifying_points'] = $qualifyingPoints;
            $summary['current_tier'] = $membership['current_tier'] ?? $summary['current_tier'];
            $summary['next_tier'] = $membership['next_tier'] ?? null;
            $summary['progress'] = (int) ($membership['progress'] ?? 0);
            $summary['remaining_points'] = (int) ($membership['remaining_points'] ?? 0);
            $summary['previous_tier_key'] = $previousTierKey;
            $summary['booking_tier_after_key'] = $currentTierKey;
            $summary['tier_up'] = $paymentStatus === 'paid'
                && $netPoints > 0
                && $currentTierKey !== $previousTierKey;

            if ($paymentStatus === 'paid' && $earnedPoints === 0 && $amountPaid > 0) {
                $summary['earned_points'] = LoyaltyPointService::previewPoints($amountPaid);
            }
            if ($paymentStatus === 'paid' && $reversedPoints > 0 && $netPoints <= 0) {
                $summary['state'] = 'reversed';
            }

            $summary['voucher'] = $this->passportVoucherForBooking($booking);

            return $summary;
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to build Passport summary for booking {bookingId}: {message}', [
                'bookingId' => $bookingId,
                'message' => $exception->getMessage(),
            ]);

            return $summary;
        }
    }

    /**
     * @param array<string, mixed> $booking
     * @return array<string, mixed>|null
     */
    private function passportVoucherForBooking(array $booking): ?array
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);
        if ($couponId <= 0 || ! $this->database()->tableExists(self::VOUCHER_TABLE)) {
            return null;
        }

        $voucher = $this->database()->table(self::VOUCHER_TABLE)
            ->select('code, reward_key, voucher_amount_vnd, status, expires_at')
            ->where('promotion_code_id', $couponId)
            ->get(1)
            ->getRowArray();

        if (! is_array($voucher)) {
            return null;
        }

        $snapshot = json_decode((string) ($booking['coupon_snapshot'] ?? ''), true);
        $rewardKey = (string) ($voucher['reward_key'] ?? '');

        return [
            'code' => trim((string) ($booking['coupon_code'] ?? $voucher['code'] ?? '')),
            'name' => is_array($snapshot) ? trim((string) ($snapshot['name'] ?? '')) : '',
            'amount_vnd' => max(0, (float) ($booking['discount_amount_vnd'] ?? $voucher['voucher_amount_vnd'] ?? 0)),
            'benefit_type' => str_starts_with($rewardKey, 'tier_welcome_') ? 'tier' : 'miles',
            'status' => strtolower((string) ($voucher['status'] ?? 'issued')),
        ];
    }

    private function database(): BaseConnection
    {
        return $this->database ??= db_connect();
    }
}
