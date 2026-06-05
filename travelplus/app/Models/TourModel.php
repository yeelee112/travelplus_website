<?php

namespace App\Models;

use CodeIgniter\Model;

class TourModel extends Model
{
    protected $table = 'tours';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'category_id',
        'departure_location_id',
        'tour_type',
        'duration_days',
        'duration_nights',
        'thumbnail',
        'is_featured',
        'is_promotion',
        'promotion_badge',
        'promotion_ends_at',
        'promotion_sort',
        'child_price_rate',
        'infant_price_rate',
        'status'
    ];
}
