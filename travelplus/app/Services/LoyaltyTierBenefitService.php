<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

final class LoyaltyTierBenefitService
{
    private const VOUCHER_TABLE = 'loyalty_reward_vouchers';
    private const PROMOTION_TABLE = 'promotion_codes';
    private const VALIDITY_DAYS = 365;

    private const BENEFITS = [
        ['key' => 'silver', 'minimum_points' => 5000, 'amount_vnd' => 100000, 'min_order_vnd' => 3000000],
        ['key' => 'gold', 'minimum_points' => 20000, 'amount_vnd' => 200000, 'min_order_vnd' => 6000000],
        ['key' => 'diamond', 'minimum_points' => 60000, 'amount_vnd' => 300000, 'min_order_vnd' => 10000000],
        ['key' => 'signature', 'minimum_points' => 150000, 'amount_vnd' => 500000, 'min_order_vnd' => 15000000],
    ];

    private ?BaseConnection $database = null;

    /** @return list<array<string, int|string>> */
    public function catalog(): array
    {
        return self::BENEFITS;
    }

    /** @return list<array<string, int|string>> */
    public function eligibleBenefits(int $qualifyingPoints): array
    {
        $qualifyingPoints = max(0, $qualifyingPoints);

        return array_values(array_filter(
            self::BENEFITS,
            static fn (array $benefit): bool => $qualifyingPoints >= $benefit['minimum_points']
        ));
    }

    /**
     * Issues each earned tier welcome voucher at most once. A row lock on the
     * member serializes concurrent requests before the existing benefits are
     * checked, so page refreshes cannot create duplicate vouchers.
     *
     * @return list<string> Newly issued voucher codes.
     */
    public function syncForUser(int $userId, int $qualifyingPoints): array
    {
        $eligibleBenefits = $this->eligibleBenefits($qualifyingPoints);
        if ($userId <= 0 || $eligibleBenefits === [] || ! $this->isAvailable()) {
            return [];
        }

        $db = $this->database();

        try {
            $db->transBegin();
            $db->query('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);

            $existingRows = $db->table(self::VOUCHER_TABLE)
                ->select('reward_key')
                ->where('user_id', $userId)
                ->like('reward_key', 'tier_welcome_', 'after')
                ->get()
                ->getResultArray();
            $existingKeys = array_fill_keys(array_column($existingRows, 'reward_key'), true);
            $issuedCodes = [];

            foreach ($eligibleBenefits as $benefit) {
                $rewardKey = 'tier_welcome_' . $benefit['key'];
                if (isset($existingKeys[$rewardKey])) {
                    continue;
                }

                $code = $this->generateUniqueCode($userId, (string) $benefit['key']);
                $now = date('Y-m-d H:i:s');
                $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::VALIDITY_DAYS . ' days'));

                $db->table(self::PROMOTION_TABLE)->insert([
                    'code' => $code,
                    'name' => 'TravelPlus Passport ' . ucfirst((string) $benefit['key']) . ' welcome benefit',
                    'description' => 'One-time membership tier welcome voucher.',
                    'discount_type' => 'fixed',
                    'discount_value' => $benefit['amount_vnd'],
                    'max_discount_amount' => null,
                    'min_order_amount' => $benefit['min_order_vnd'],
                    'usage_limit' => 1,
                    'used_count' => 0,
                    'starts_at' => $now,
                    'ends_at' => $expiresAt,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $promotionId = (int) $db->insertID();

                $db->table(self::VOUCHER_TABLE)->insert([
                    'user_id' => $userId,
                    'promotion_code_id' => $promotionId,
                    'code' => $code,
                    'reward_key' => $rewardKey,
                    'points_spent' => 0,
                    'voucher_amount_vnd' => $benefit['amount_vnd'],
                    'min_order_vnd' => $benefit['min_order_vnd'],
                    'status' => 'issued',
                    'expires_at' => $expiresAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $existingKeys[$rewardKey] = true;
                $issuedCodes[] = $code;
            }

            if ($db->transStatus() === false) {
                $db->transRollback();

                return [];
            }

            $db->transCommit();

            return $issuedCodes;
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Passport tier benefit sync failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function isAvailable(): bool
    {
        $db = $this->database();

        return $db->tableExists('users')
            && $db->tableExists(self::VOUCHER_TABLE)
            && $db->tableExists(self::PROMOTION_TABLE);
    }

    private function generateUniqueCode(int $userId, string $tierKey): string
    {
        do {
            $hash = hash('sha256', $userId . '|' . $tierKey . '|' . microtime(true) . '|' . random_bytes(16));
            $code = 'TPP-' . strtoupper(substr($tierKey, 0, 2)) . '-' . strtoupper(substr($hash, 0, 8));
        } while ($this->database()->table(self::PROMOTION_TABLE)->where('code', $code)->countAllResults() > 0);

        return $code;
    }

    private function database(): BaseConnection
    {
        return $this->database ??= db_connect();
    }
}
