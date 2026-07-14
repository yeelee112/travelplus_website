<?php

namespace App\Models;

use CodeIgniter\Model;

class CrmLeadModel extends Model
{
    protected $table = 'crm_leads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    protected $allowedFields = [
        'source',
        'stage',
        'priority',
        'customer_name',
        'customer_email',
        'customer_phone',
        'service_type',
        'interest_title',
        'interest_url',
        'destination',
        'travel_date',
        'travelers',
        'budget',
        'message',
        'booking_id',
        'booking_code',
        'last_contacted_at',
        'assigned_user_id',
        'internal_note',
        'metadata',
        'created_at',
        'updated_at',
    ];
}
