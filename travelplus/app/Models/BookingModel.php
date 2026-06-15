<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    protected $allowedFields = [
        'booking_code',
        'user_id',
        'tour_id',
        'tour_title',
        'tour_link',
        'tour_image',
        'departure_label',
        'duration_label',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_note',
        'adult_quantity',
        'child_quantity',
        'infant_quantity',
        'adult_price',
        'child_price',
        'infant_price',
        'subtotal_vnd',
        'discount_amount_vnd',
        'coupon_id',
        'coupon_code',
        'coupon_snapshot',
        'grand_total',
        'currency',
        'payment_method',
        'payment_plan',
        'payment_status',
        'amount_due_vnd',
        'amount_paid_vnd',
        'paypal_order_id',
        'paypal_capture_id',
        'paypal_status',
        'provider_reference',
        'provider_payload',
        'paid_at',
        'cancelled_at',
        'created_at',
        'updated_at',
    ];
}
