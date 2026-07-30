<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Zalo extends BaseConfig
{
    public bool $otpEnabled = false;
    public string $accessToken = '';
    public string $otpTemplateId = '';
    public string $otpField = 'otp';
    public string $otpExpiryField = '';
    public string $endpoint = 'https://business.openapi.zalo.me/message/template';
    public bool $verifySsl = true;

    public function __construct()
    {
        parent::__construct();

        $this->otpEnabled = filter_var(
            env('zalo.otpEnabled', false),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? false;
        $this->accessToken = $this->envString('zalo.accessToken');
        $this->otpTemplateId = $this->envString('zalo.otpTemplateId');
        $this->otpField = $this->envString('zalo.otpField', $this->otpField);
        $this->otpExpiryField = $this->envString('zalo.otpExpiryField');
        $this->endpoint = $this->envString('zalo.endpoint', $this->endpoint);
        $this->verifySsl = filter_var(
            env('zalo.verifySsl', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? true;
    }

    private function envString(string $key, string $default = ''): string
    {
        return trim((string) env($key, $default), " \t\n\r\0\x0B\"'");
    }
}
