<?php

namespace App\Services;

use App\Models\PromotionCodeModel;
use App\Models\PromotionCodeTourModel;

class PromotionCodeService
{
    /**
     * @param array<string, mixed> $pendingBooking
     * @return array{ok: bool, message: string, code?: array<string, mixed>, discount_amount: float, subtotal: float, grand_total: float}
     */
    public function applyCode(string $rawCode, array $pendingBooking): array
    {
        $code = strtoupper(trim($rawCode));
        $subtotal = max(0, (float) ($pendingBooking['subtotal_vnd'] ?? $pendingBooking['grand_total'] ?? 0));

        if ($subtotal <= 0) {
            return $this->failure('invalidSubtotal', 0, 0);
        }

        if ($code === '') {
            return $this->failure('missingCode', $subtotal, $subtotal);
        }

        $model = new PromotionCodeModel();

        if (! $model->db->tableExists($model->getTable())) {
            return $this->failure('tableMissing', $subtotal, $subtotal);
        }

        $promotion = $model->where('code', $code)->first();

        if (! is_array($promotion)) {
            return $this->failure('notFound', $subtotal, $subtotal);
        }

        if ((int) ($promotion['is_active'] ?? 0) !== 1) {
            return $this->failure('inactive', $subtotal, $subtotal);
        }

        $now = date('Y-m-d H:i:s');
        $startsAt = trim((string) ($promotion['starts_at'] ?? ''));
        $endsAt = trim((string) ($promotion['ends_at'] ?? ''));

        if ($startsAt !== '' && $startsAt > $now) {
            return $this->failure('notStarted', $subtotal, $subtotal);
        }

        if ($endsAt !== '' && $endsAt < $now) {
            return $this->failure('expired', $subtotal, $subtotal);
        }

        $usageLimit = (int) ($promotion['usage_limit'] ?? 0);
        $usedCount = (int) ($promotion['used_count'] ?? 0);

        if ($usageLimit > 0 && $usedCount >= $usageLimit) {
            return $this->failure('usageExceeded', $subtotal, $subtotal);
        }

        $minimumOrder = max(0, (float) ($promotion['min_order_amount'] ?? 0));

        if ($minimumOrder > 0 && $subtotal < $minimumOrder) {
            return $this->failure('minimumOrder', $subtotal, $subtotal);
        }

        if (! $this->isApplicableToBooking($promotion, $pendingBooking)) {
            return $this->failure('tourMismatch', $subtotal, $subtotal);
        }

        $discountType = (string) ($promotion['discount_type'] ?? 'fixed');
        $discountValue = max(0, (float) ($promotion['discount_value'] ?? 0));
        $discountAmount = 0.0;

        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * ($discountValue / 100), 0);
            $maxDiscount = max(0, (float) ($promotion['max_discount_amount'] ?? 0));

            if ($maxDiscount > 0) {
                $discountAmount = min($discountAmount, $maxDiscount);
            }
        } else {
            $discountAmount = $discountValue;
        }

        $discountAmount = min($subtotal, max(0, $discountAmount));
        $grandTotal = max(0, $subtotal - $discountAmount);

        if ($discountAmount <= 0) {
            return $this->failure('invalidDiscount', $subtotal, $subtotal);
        }

        return [
            'ok' => true,
            'message' => $this->message('applied'),
            'code' => $promotion,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ];
    }

    private function failure(string $messageKey, float $subtotal, float $grandTotal): array
    {
        return [
            'ok' => false,
            'message' => $this->message($messageKey),
            'discount_amount' => 0,
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ];
    }

    private function locale(): string
    {
        $request = service('request');

        return $request && $request->getLocale() === 'en' ? 'en' : 'vi';
    }

    private function message(string $key): string
    {
        $messages = [
            'vi' => [
                'invalidSubtotal' => 'Không tìm thấy tổng tiền hợp lệ để áp dụng mã.',
                'missingCode' => 'Vui lòng nhập mã khuyến mãi.',
                'tableMissing' => 'Bảng mã khuyến mãi chưa sẵn sàng.',
                'notFound' => 'Mã khuyến mãi không tồn tại.',
                'inactive' => 'Mã khuyến mãi hiện không hoạt động.',
                'notStarted' => 'Mã khuyến mãi chưa đến thời gian sử dụng.',
                'expired' => 'Mã khuyến mãi đã hết hạn.',
                'usageExceeded' => 'Mã khuyến mãi đã hết lượt sử dụng.',
                'minimumOrder' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp mã.',
                'tourMismatch' => 'Mã khuyến mãi này không áp dụng cho tour bạn đang chọn.',
                'invalidDiscount' => 'Mã khuyến mãi không tạo ra mức giảm hợp lệ.',
                'applied' => 'Áp dụng mã khuyến mãi thành công.',
            ],
            'en' => [
                'invalidSubtotal' => 'A valid booking subtotal is required before applying a coupon.',
                'missingCode' => 'Please enter a coupon code.',
                'tableMissing' => 'The promotion code table is not ready yet.',
                'notFound' => 'This coupon code does not exist.',
                'inactive' => 'This coupon code is not active right now.',
                'notStarted' => 'This coupon code cannot be used yet.',
                'expired' => 'This coupon code has expired.',
                'usageExceeded' => 'This coupon code has reached its usage limit.',
                'minimumOrder' => 'This booking does not meet the minimum order amount for the coupon.',
                'tourMismatch' => 'This coupon code does not apply to the selected tour.',
                'invalidDiscount' => 'This coupon code does not produce a valid discount.',
                'applied' => 'Coupon code applied successfully.',
            ],
        ];

        $locale = $this->locale();

        return $messages[$locale][$key] ?? $messages['vi'][$key] ?? '';
    }

    /**
     * @param array<string, mixed> $promotion
     * @param array<string, mixed> $pendingBooking
     */
    private function isApplicableToBooking(array $promotion, array $pendingBooking): bool
    {
        $promotionId = (int) ($promotion['id'] ?? 0);
        $tourId = (int) ($pendingBooking['tour_id'] ?? 0);

        if ($promotionId <= 0 || $tourId <= 0) {
            return true;
        }

        $mappingModel = new PromotionCodeTourModel();

        if (! $mappingModel->db->tableExists($mappingModel->getTable())) {
            return true;
        }

        $assignedTourIds = array_map(
            static fn(array $row): int => (int) ($row['tour_id'] ?? 0),
            $mappingModel->select('tour_id')->where('promotion_code_id', $promotionId)->findAll()
        );

        $assignedTourIds = array_values(array_filter($assignedTourIds, static fn(int $value): bool => $value > 0));

        if ($assignedTourIds === []) {
            return true;
        }

        return in_array($tourId, $assignedTourIds, true);
    }
}
