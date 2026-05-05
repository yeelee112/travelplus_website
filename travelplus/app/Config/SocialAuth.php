<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SocialAuth extends BaseConfig
{
    public bool $googleEnabled = false;
    public string $googleClientId = '';
    public string $googleClientSecret = '';
}
