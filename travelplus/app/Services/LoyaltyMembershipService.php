<?php

namespace App\Services;

final class LoyaltyMembershipService
{
    private const TIERS = [
        ['key' => 'member', 'minimum_points' => 0],
        ['key' => 'silver', 'minimum_points' => 1000],
        ['key' => 'gold', 'minimum_points' => 5000],
        ['key' => 'diamond', 'minimum_points' => 15000],
        ['key' => 'signature', 'minimum_points' => 30000],
    ];

    /**
     * @param list<array<string, mixed>> $bookings
     * @return array<string, mixed>
     */
    public function buildSnapshot(array $bookings, ?int $points = null): array
    {
        $programActive = $points !== null;
        $points = max(0, $points ?? 0);
        $currentTierIndex = 0;

        foreach (self::TIERS as $index => $tier) {
            if ($points < $tier['minimum_points']) {
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
            $earnedInTier = max(0, $points - $currentTier['minimum_points']);
            $progress = (int) min(100, floor(($earnedInTier / $range) * 100));
            $remainingPoints = max(0, $nextTier['minimum_points'] - $points);
        }

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

        return [
            'program_active' => $programActive,
            'points' => $points,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress' => $programActive ? $progress : 0,
            'remaining_points' => $remainingPoints,
            'tiers' => self::TIERS,
            'booking_count' => count($bookings),
            'paid_booking_count' => $paidBookings,
            'pending_booking_count' => $pendingBookings,
        ];
    }
}
