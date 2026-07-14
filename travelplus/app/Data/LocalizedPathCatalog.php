<?php

namespace App\Data;

final class LocalizedPathCatalog
{
    /**
     * @var array<string, array<string, string>>
     */
    private const MAP = [
        'search' => [
            'vi' => 'tim-kiem-tour',
            'en' => 'tour-search',
        ],
        'blog' => [
            'vi' => 'cam-hung-du-lich',
            'en' => 'travel-inspiration',
        ],
        'about' => [
            'vi' => 've-chung-toi',
            'en' => 've-chung-toi',
        ],
        'contact' => [
            'vi' => 'contact',
            'en' => 'contact',
        ],
        'summer' => [
            'vi' => 'tour-he',
            'en' => 'summer-tours',
        ],
        'outbound' => [
            'vi' => 'tour-nuoc-ngoai',
            'en' => 'tour-nuoc-ngoai',
        ],
        'domestic' => [
            'vi' => 'tour-trong-nuoc',
            'en' => 'tour-trong-nuoc',
        ],
        'service.visa' => [
            'vi' => 'dich-vu-visa',
            'en' => 'dich-vu-visa',
        ],
        'service.mice' => [
            'vi' => 'dich-vu-mice',
            'en' => 'dich-vu-mice',
        ],
        'service.airlineTickets' => [
            'vi' => 'dich-vu-ve-may-bay',
            'en' => 'airline-ticket-service',
        ],
        'service.transport' => [
            'vi' => 'dich-vu-van-chuyen',
            'en' => 'transport-service',
        ],
        'service.translation' => [
            'vi' => 'dich-vu-dich-thuat',
            'en' => 'translation-service',
        ],
        'service.hotels' => [
            'vi' => 'dich-vu-khach-san',
            'en' => 'hotel-service',
        ],
        'legal.terms' => [
            'vi' => 'dieu-khoan-su-dung',
            'en' => 'terms-of-service',
        ],
        'legal.privacy' => [
            'vi' => 'chinh-sach-bao-mat',
            'en' => 'privacy-statement',
        ],
        'auth.register' => [
            'vi' => 'account/register',
            'en' => 'account/register',
        ],
        'auth.login' => [
            'vi' => 'account/login',
            'en' => 'account/login',
        ],
        'auth.profile' => [
            'vi' => 'account/profile',
            'en' => 'account/profile',
        ],
        'auth.forgotPassword' => [
            'vi' => 'account/forgot-password',
            'en' => 'account/forgot-password',
        ],
        'auth.logoutAll' => [
            'vi' => 'account/logout-all',
            'en' => 'account/logout-all',
        ],
        'auth.logout' => [
            'vi' => 'auth/logout',
            'en' => 'auth/logout',
        ],
        'auth.google' => [
            'vi' => 'auth/google',
            'en' => 'auth/google',
        ],
        'auth.googleCallback' => [
            'vi' => 'auth/google/callback',
            'en' => 'auth/google/callback',
        ],
        'admin.bookings' => [
            'vi' => 'admin/bookings',
            'en' => 'admin/bookings',
        ],
        'admin.dashboard' => [
            'vi' => 'admin',
            'en' => 'admin',
        ],
        'admin.tours' => [
            'vi' => 'admin/tours',
            'en' => 'admin/tours',
        ],
        'admin.tours.create' => [
            'vi' => 'admin/tours/create',
            'en' => 'admin/tours/create',
        ],
        'admin.blogs' => [
            'vi' => 'admin/blogs',
            'en' => 'admin/blogs',
        ],
        'admin.blogs.create' => [
            'vi' => 'admin/blogs/create',
            'en' => 'admin/blogs/create',
        ],
        'admin.promotionCodes' => [
            'vi' => 'admin/promotion-codes',
            'en' => 'admin/promotion-codes',
        ],
        'admin.promotionCodes.create' => [
            'vi' => 'admin/promotion-codes/create',
            'en' => 'admin/promotion-codes/create',
        ],
        'admin.reviews' => [
            'vi' => 'admin/reviews',
            'en' => 'admin/reviews',
        ],
        'admin.mediaAudit' => [
            'vi' => 'admin/media-audit',
            'en' => 'admin/media-audit',
        ],
        'admin.users' => [
            'vi' => 'admin/users',
            'en' => 'admin/users',
        ],
        'booking.checkout' => [
            'vi' => 'booking/checkout',
            'en' => 'booking/checkout',
        ],
        'booking.applyCoupon' => [
            'vi' => 'booking/apply-coupon',
            'en' => 'booking/apply-coupon',
        ],
        'booking.guest' => [
            'vi' => 'booking/guest',
            'en' => 'booking/guest',
        ],
        'booking.lookup' => [
            'vi' => 'booking/lookup',
            'en' => 'booking/lookup',
        ],
        'booking.successPrefix' => [
            'vi' => 'booking/success',
            'en' => 'booking/success',
        ],
        'booking.paypalCreateOrder' => [
            'vi' => 'booking/paypal/create-order',
            'en' => 'booking/paypal/create-order',
        ],
        'booking.vnpayCreatePayment' => [
            'vi' => 'booking/vnpay/create-payment',
            'en' => 'booking/vnpay/create-payment',
        ],
        'booking.vietqrGenerate' => [
            'vi' => 'booking/vietqr/generate',
            'en' => 'booking/vietqr/generate',
        ],
        'booking.vietqrComplete' => [
            'vi' => 'booking/vietqr/complete',
            'en' => 'booking/vietqr/complete',
        ],
        'booking.paypalReturn' => [
            'vi' => 'booking/paypal/return',
            'en' => 'booking/paypal/return',
        ],
        'booking.paypalCancel' => [
            'vi' => 'booking/paypal/cancel',
            'en' => 'booking/paypal/cancel',
        ],
        'booking.vnpayReturn' => [
            'vi' => 'booking/vnpay/return',
            'en' => 'booking/vnpay/return',
        ],
        'booking.vnpayIpn' => [
            'vi' => 'booking/vnpay/ipn',
            'en' => 'booking/vnpay/ipn',
        ],
    ];

    public static function path(string $key, string $locale = 'vi'): string
    {
        $paths = self::MAP[$key] ?? [];

        return $paths[$locale] ?? ($paths['vi'] ?? '');
    }

    public static function url(string $key, string $locale = 'vi'): string
    {
        return localized_url(self::path($key, $locale));
    }
}
