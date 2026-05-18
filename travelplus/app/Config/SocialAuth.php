<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SocialAuth extends BaseConfig
{
    public bool $googleEnabled = false;
    public string $googleClientId = '';
    public string $googleClientSecret = '';

    public function __construct()
    {
        parent::__construct();

        $enabled = env('socialauth.googleEnabled');
        if ($enabled !== null) {
            $this->googleEnabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        $clientId = env('socialauth.googleClientId');
        if (is_string($clientId) && $clientId !== '') {
            $this->googleClientId = trim($clientId, " \t\n\r\0\x0B\"'");
        }

        $clientSecret = env('socialauth.googleClientSecret');
        if (is_string($clientSecret) && $clientSecret !== '') {
            $this->googleClientSecret = trim($clientSecret, " \t\n\r\0\x0B\"'");
        }
    }
}
