<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

final class LoyaltyRewardService
{
    private const VOUCHER_TABLE = 'loyalty_reward_vouchers';
    private const POINT_TABLE = 'loyalty_point_transactions';
    private const PROMOTION_TABLE = 'promotion_codes';
    private const VALIDITY_DAYS = 180;

    private const REWARDS = [
        ['key' => 'passport_50', 'points' => 500, 'amount_vnd' => 50000, 'min_order_vnd' => 2000000],
        ['key' => 'passport_120', 'points' => 1200, 'amount_vnd' => 120000, 'min_order_vnd' => 5000000],
        ['key' => 'passport_250', 'points' => 2500, 'amount_vnd' => 250000, 'min_order_vnd' => 10000000],
    ];

    private ?BaseConnection $database = null;

    /** @return list<array<string, int|string|bool>> */
    public function catalog(int $balance): array
    {
        return array_map(static function (array $reward) use ($balance): array {
            $reward['available'] = $balance >= $reward['points'];
            $reward['points_needed'] = max(0, $reward['points'] - $balance);

            return $reward;
        }, self::REWARDS);
    }

    /** @return array<string, int|string>|null */
    public function nextReward(int $balance): ?array
    {
        foreach (self::REWARDS as $reward) {
            if ($balance < $reward['points']) {
                return $reward;
            }
        }

        return null;
    }

    /** @return array<string, int|string>|null */
    public function bestNewlyUnlockedReward(int $balance, int $pointsToEarn): ?array
    {
        $balance = max(0, $balance);
        $projectedBalance = $balance + max(0, $pointsToEarn);
        $bestReward = null;

        foreach (self::REWARDS as $reward) {
            if ($balance < $reward['points'] && $projectedBalance >= $reward['points']) {
                $bestReward = $reward;
            }
        }

        return $bestReward;
    }

    public function isAvailable(): bool
    {
        $db = $this->database();

        return $db->tableExists(self::VOUCHER_TABLE)
            && $db->tableExists(self::POINT_TABLE)
            && $db->tableExists(self::PROMOTION_TABLE);
    }

    /** @return list<array<string, mixed>> */
    public function vouchersForUser(int $userId): array
    {
        if ($userId <= 0 || ! $this->isAvailable()) {
            return [];
        }

        (new BookingDiscountSettlementService())->releaseExpiredReservations();

        $voucherSelect = 'rv.id, rv.code, rv.reward_key, rv.points_spent, rv.voucher_amount_vnd, rv.min_order_vnd, rv.status, rv.expires_at, rv.created_at, pc.used_count, pc.is_active';
        $voucherFields = $this->database()->getFieldNames(self::VOUCHER_TABLE);
        if (in_array('booking_id', $voucherFields, true)) {
            $voucherSelect .= ', rv.booking_id, rv.reserved_at, rv.reservation_expires_at, rv.used_at';
        }

        return $this->database()->table(self::VOUCHER_TABLE . ' rv')
            ->select($voucherSelect)
            ->join(self::PROMOTION_TABLE . ' pc', 'pc.id = rv.promotion_code_id', 'left')
            ->where('rv.user_id', $userId)
            ->orderBy('rv.created_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function checkoutVouchersForUser(int $userId, float $eligibleSubtotal): array
    {
        if ($userId <= 0 || ! $this->isAvailable()) {
            return [];
        }

        (new BookingDiscountSettlementService())->releaseExpiredReservations();

        $now = date('Y-m-d H:i:s');
        $rows = $this->database()->table(self::VOUCHER_TABLE . ' rv')
            ->select('rv.id, rv.code, rv.reward_key, rv.points_spent, rv.voucher_amount_vnd, rv.min_order_vnd, rv.expires_at, pc.name, pc.used_count, pc.usage_limit')
            ->join(self::PROMOTION_TABLE . ' pc', 'pc.id = rv.promotion_code_id', 'inner')
            ->where('rv.user_id', $userId)
            ->where('rv.status', 'issued')
            ->where('rv.expires_at >=', $now)
            ->where('pc.is_active', 1)
            ->groupStart()
                ->where('pc.starts_at', null)
                ->orWhere('pc.starts_at <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('pc.ends_at', null)
                ->orWhere('pc.ends_at >=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('pc.usage_limit', 0)
                ->orWhere('pc.used_count < pc.usage_limit', null, false)
            ->groupEnd()
            ->orderBy('rv.voucher_amount_vnd', 'DESC')
            ->orderBy('rv.expires_at', 'ASC')
            ->get()
            ->getResultArray();

        $eligibleSubtotal = max(0, $eligibleSubtotal);

        return array_map(static function (array $voucher) use ($eligibleSubtotal): array {
            $minimumOrder = max(0, (float) ($voucher['min_order_vnd'] ?? 0));
            $rewardKey = (string) ($voucher['reward_key'] ?? '');
            $voucher['eligible'] = $eligibleSubtotal >= $minimumOrder;
            $voucher['amount_needed_vnd'] = max(0, $minimumOrder - $eligibleSubtotal);
            $voucher['benefit_type'] = str_starts_with($rewardKey, 'tier_welcome_') ? 'tier' : 'miles';

            return $voucher;
        }, $rows);
    }

    /** @return array{ok: bool, message: string, code?: string} */
    public function redeem(int $userId, string $rewardKey, string $locale = 'vi'): array
    {
        $reward = $this->findReward($rewardKey);
        if ($userId <= 0 || $reward === null) {
            return $this->failure($locale, 'invalid');
        }
        if (! $this->isAvailable()) {
            return $this->failure($locale, 'unavailable');
        }

        $db = $this->database();
        try {
            $db->transBegin();
            $db->query('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);
            $balanceRow = $db->table(self::POINT_TABLE)
                ->select('COALESCE(SUM(points), 0) AS balance', false)
                ->where('user_id', $userId)
                ->get()->getRowArray();
            $balance = max(0, (int) ($balanceRow['balance'] ?? 0));

            if ($balance < $reward['points']) {
                $db->transRollback();
                return $this->failure($locale, 'insufficient');
            }

            $code = $this->generateUniqueCode($userId);
            $now = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::VALIDITY_DAYS . ' days'));
            $db->table(self::PROMOTION_TABLE)->insert([
                'code' => $code,
                'name' => 'TravelPlus Passport ' . number_format($reward['amount_vnd'], 0, ',', '.') . ' VND',
                'description' => 'Voucher đổi từ ' . number_format($reward['points'], 0, ',', '.') . ' Dặm Hành Trình.',
                'discount_type' => 'fixed',
                'discount_value' => $reward['amount_vnd'],
                'max_discount_amount' => null,
                'min_order_amount' => $reward['min_order_vnd'],
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
                'reward_key' => $reward['key'],
                'points_spent' => $reward['points'],
                'voucher_amount_vnd' => $reward['amount_vnd'],
                'min_order_vnd' => $reward['min_order_vnd'],
                'status' => 'issued',
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $voucherId = (int) $db->insertID();
            $db->table(self::POINT_TABLE)->insert([
                'user_id' => $userId,
                'booking_id' => null,
                'event_key' => hash('sha256', 'passport-redeem|' . $voucherId),
                'type' => 'voucher_redeemed',
                'points' => -$reward['points'],
                'amount_vnd' => $reward['amount_vnd'],
                'description' => $code,
                'created_at' => $now,
            ]);
            $db->transCommit();

            return [
                'ok' => true,
                'code' => $code,
                'message' => $locale === 'en'
                    ? 'Voucher created successfully. Copy the code and use it at checkout.'
                    : 'Đổi voucher thành công. Sao chép mã để dùng khi thanh toán tour.',
            ];
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Passport reward redemption failed: {message}', ['message' => $exception->getMessage()]);

            return $this->failure($locale, 'failed');
        }
    }

    /** @return array<string, int|string>|null */
    private function findReward(string $key): ?array
    {
        foreach (self::REWARDS as $reward) {
            if ($reward['key'] === $key) {
                return $reward;
            }
        }

        return null;
    }

    private function generateUniqueCode(int $userId): string
    {
        do {
            $code = 'TPP-' . strtoupper(substr(hash('sha256', $userId . '|' . microtime(true) . '|' . random_bytes(16)), 0, 10));
        } while ($this->database()->table(self::PROMOTION_TABLE)->where('code', $code)->countAllResults() > 0);

        return $code;
    }

    /** @return array{ok: false, message: string} */
    private function failure(string $locale, string $reason): array
    {
        $english = [
            'invalid' => 'This reward is not valid.',
            'unavailable' => 'Passport rewards are being prepared. Please try again later.',
            'insufficient' => 'Your Journey Miles balance is not enough for this voucher.',
            'failed' => 'The voucher could not be created. No points were deducted.',
        ];
        $vietnamese = [
            'invalid' => 'Quà đổi không hợp lệ.',
            'unavailable' => 'Kho quà Passport đang được chuẩn bị. Vui lòng thử lại sau.',
            'insufficient' => 'Bạn chưa đủ Dặm Hành Trình để đổi voucher này.',
            'failed' => 'Chưa thể tạo voucher. Điểm của bạn không bị trừ.',
        ];

        return ['ok' => false, 'message' => ($locale === 'en' ? $english : $vietnamese)[$reason]];
    }

    private function database(): BaseConnection
    {
        return $this->database ??= db_connect();
    }
}
