<?php

namespace App\Services;

final class LoyaltyMembershipService
{
    private const TIERS = [
        ['key' => 'member', 'minimum_points' => 0, 'discount_rate' => 0.0, 'discount_cap_vnd' => 0],
        ['key' => 'silver', 'minimum_points' => 5000, 'discount_rate' => 1.0, 'discount_cap_vnd' => 200000],
        ['key' => 'gold', 'minimum_points' => 20000, 'discount_rate' => 1.5, 'discount_cap_vnd' => 400000],
        ['key' => 'diamond', 'minimum_points' => 60000, 'discount_rate' => 2.0, 'discount_cap_vnd' => 600000],
        ['key' => 'signature', 'minimum_points' => 150000, 'discount_rate' => 3.0, 'discount_cap_vnd' => 1000000],
    ];

    /**
     * @param list<array<string, mixed>> $bookings
     * @return array<string, mixed>
     */
    public function buildSnapshot(array $bookings, ?int $points = null): array
    {
        $paidBookings = 0;
        $pendingBookings = 0;

        foreach ($bookings as $booking) {
            $status = strtolower(trim((string) ($booking['payment_status'] ?? '')));

            if ($status === 'paid') {
                $paidBookings++;
            } elseif (in_array($status, ['draft', 'pending_payment', 'pending_transfer'], true)) {
                $pendingBookings++;
            }
        }

        return $this->buildSnapshotFromCounts(count($bookings), $paidBookings, $pendingBookings, $points);
    }

    /**
     * Builds the membership snapshot from aggregate booking counts so account
     * pages do not need to load every historical booking into PHP.
     *
     * @return array<string, mixed>
     */
    public function buildSnapshotFromCounts(
        int $bookingCount,
        int $paidBookingCount,
        int $pendingBookingCount,
        ?int $points = null,
        ?int $qualifyingPoints = null
    ): array {
        $programActive = $points !== null;
        $points = max(0, $points ?? 0);
        $qualifyingPoints = max(0, $qualifyingPoints ?? $points);
        $currentTierIndex = 0;

        foreach (self::TIERS as $index => $tier) {
            if ($qualifyingPoints < $tier['minimum_points']) {
                break;
            }

            $currentTierIndex = $index;
        }

        $currentTier = self::TIERS[$currentTierIndex];
        $nextTier = self::TIERS[$currentTierIndex + 1] ?? null;
        $progress = 100;
        $remainingPoints = 0;

        if ($nextTier !== null) {
            $range = max(1, $nextTier['minimum_points'] - $currentTier['minimum_points']);
            $earnedInTier = max(0, $qualifyingPoints - $currentTier['minimum_points']);
            $progress = (int) min(100, floor(($earnedInTier / $range) * 100));
            $remainingPoints = max(0, $nextTier['minimum_points'] - $qualifyingPoints);
        }

        return [
            'program_active' => $programActive,
            'points' => $points,
            'qualifying_points' => $qualifyingPoints,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress' => $programActive ? $progress : 0,
            'remaining_points' => $remainingPoints,
            'tiers' => self::TIERS,
            'booking_count' => max(0, $bookingCount),
            'paid_booking_count' => max(0, $paidBookingCount),
            'pending_booking_count' => max(0, $pendingBookingCount),
        ];
    }

    public static function calculateTierDiscount(float $eligibleSubtotal, float $discountRate, float $discountCap = 0): float
    {
        $eligibleSubtotal = max(0, $eligibleSubtotal);
        $discountRate = min(3.0, max(0, $discountRate));
        $discountAmount = round($eligibleSubtotal * ($discountRate / 100), 0);

        return $discountCap > 0 ? min($discountAmount, $discountCap) : $discountAmount;
    }
}
