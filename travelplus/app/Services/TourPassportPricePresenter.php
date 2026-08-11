<?php

namespace App\Services;

final class TourPassportPricePresenter
{
    /**
     * @param array<string, mixed>|null $authUser
     * @param array<string, mixed>|null $headerMembership
     * @return array<string, mixed>|null
     */
    public static function build(
        float $priceAmount,
        ?array $authUser,
        ?array $headerMembership,
        string $locale = 'vi'
    ): ?array {
        $priceAmount = max(0, $priceAmount);
        $locale = $locale === 'en' ? 'en' : 'vi';

        if ($priceAmount <= 0) {
            return null;
        }

        $isLoggedIn = is_array($authUser) && (int) ($authUser['id'] ?? 0) > 0;
        if (! $isLoggedIn) {
            return [
                'state' => 'guest',
                'eyebrow' => '',
                'label' => $locale === 'en'
                    ? 'Member benefits'
                    : 'Ưu đãi thành viên',
                'tooltip' => $locale === 'en'
                    ? 'Sign in to see the member benefit available for your tier.'
                    : 'Đăng nhập để xem quyền lợi phù hợp với hạng thành viên của bạn.',
            ];
        }

        $points = max(0, (int) ($headerMembership['points'] ?? 0));
        $qualifyingPoints = max(0, (int) ($headerMembership['qualifying_points'] ?? 0));
        $snapshot = (new LoyaltyMembershipService())->buildSnapshotFromCounts(
            0,
            0,
            0,
            $points,
            $qualifyingPoints
        );
        $tier = is_array($snapshot['current_tier'] ?? null) ? $snapshot['current_tier'] : [];
        $discountRate = max(0, (float) ($tier['discount_rate'] ?? 0));
        $discountCap = max(0, (float) ($tier['discount_cap_vnd'] ?? 0));

        if ($discountRate <= 0) {
            $remaining = max(0, (int) ($snapshot['remaining_points'] ?? 0));

            return [
                'state' => 'locked',
                'eyebrow' => $locale === 'en' ? 'Member benefits' : 'Ưu đãi thành viên',
                'label' => $locale === 'en'
                    ? self::formatNumber($remaining, $locale) . ' Miles to unlock Silver pricing'
                    : 'Còn ' . self::formatNumber($remaining, $locale) . ' Dặm để mở giá Bạc',
                'tooltip' => $locale === 'en'
                    ? 'Tier pricing starts from Silver membership.'
                    : 'Ưu đãi giá bắt đầu từ hạng Bạc.',
            ];
        }

        $discountAmount = LoyaltyMembershipService::calculateTierDiscount(
            $priceAmount,
            $discountRate,
            $discountCap
        );
        $tierNames = $locale === 'en'
            ? ['silver' => 'Silver', 'gold' => 'Gold', 'diamond' => 'Diamond', 'signature' => 'Signature']
            : ['silver' => 'Bạc', 'gold' => 'Vàng', 'diamond' => 'Kim Cương', 'signature' => 'Signature'];
        $tierKey = strtolower((string) ($tier['key'] ?? 'member'));
        $tierName = $tierNames[$tierKey] ?? ucfirst($tierKey);

        return [
            'state' => 'active',
            'eyebrow' => $locale === 'en' ? 'Member price' : 'Giá thành viên',
            'label' => $tierName,
            'tier_key' => $tierKey,
            'price' => self::formatMoney(max(0, $priceAmount - $discountAmount), $locale),
            'saving' => ($locale === 'en' ? 'Save ' : 'Tiết kiệm ') . self::formatMoney($discountAmount, $locale),
            'tooltip' => $locale === 'en'
                ? self::formatRate($discountRate) . '% tier saving, capped at ' . self::formatMoney($discountCap, $locale) . ' per booking.'
                : 'Giảm ' . self::formatRate($discountRate) . '% theo hạng, tối đa ' . self::formatMoney($discountCap, $locale) . ' mỗi booking.',
            'discount_amount' => $discountAmount,
        ];
    }

    private static function formatMoney(float $amount, string $locale): string
    {
        $number = number_format($amount, 0, $locale === 'en' ? '.' : ',', $locale === 'en' ? ',' : '.');

        return $locale === 'en' ? $number . ' VND' : $number . 'đ';
    }

    private static function formatNumber(int $number, string $locale): string
    {
        return number_format($number, 0, $locale === 'en' ? '.' : ',', $locale === 'en' ? ',' : '.');
    }

    private static function formatRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2, ',', ''), '0'), ',');
    }
}
