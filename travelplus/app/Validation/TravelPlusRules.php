<?php

namespace App\Validation;

use App\Services\VietnamPhoneService;

class TravelPlusRules
{
    public function validVietnamPhone(?string $value, ?string $fields = null, array $data = [], ?string &$error = null): bool
    {
        $valid = VietnamPhoneService::isValid($value);

        if (! $valid) {
            $error = 'Invalid Vietnamese phone number.';
        }

        return $valid;
    }
}
