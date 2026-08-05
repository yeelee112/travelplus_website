<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Zalo extends BaseConfig
{
    public string $appId = '861108087263637056';
    public string $appSecret = '';
    public string $callbackUrl = 'https://travelplusvn.com/zalo/oauth/callback';
    public string $permissionEndpoint = 'https://oauth.zaloapp.com/v4/oa/permission';
    public string $tokenEndpoint = 'https://oauth.zaloapp.com/v4/oa/access_token';
    public string $oaInfoEndpoint = 'https://openapi.zalo.me/v3.0/oa/getoa';
    public int $tokenRefreshLeeway = 300;
    public bool $otpEnabled = true;
    public string $accessToken = '';
    public string $otpTemplateId = '617290';
    public string $otpField = 'otp';
    public string $otpExpiryField = '';
    public string $endpoint = 'https://business.openapi.zalo.me/message/template';
    public bool $verifySsl = true;

    public function __construct()
    {
        parent::__construct();

        $this->appId = $this->envString('zalo.appId', $this->appId);
        $this->appSecret = $this->envString('zalo.appSecret');
        $this->callbackUrl = $this->envString('zalo.callbackUrl', $this->callbackUrl);
        $this->permissionEndpoint = $this->envString('zalo.permissionEndpoint', $this->permissionEndpoint);
        $this->tokenEndpoint = $this->envString('zalo.tokenEndpoint', $this->tokenEndpoint);
        $this->oaInfoEndpoint = $this->envString('zalo.oaInfoEndpoint', $this->oaInfoEndpoint);
        $this->tokenRefreshLeeway = max(60, (int) env('zalo.tokenRefreshLeeway', $this->tokenRefreshLeeway));
        $this->otpEnabled = filter_var(
            env('zalo.otpEnabled', $this->otpEnabled),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? false;
        $this->accessToken = $this->envString('zalo.accessToken');
        $this->otpTemplateId = $this->envString('zalo.otpTemplateId', $this->otpTemplateId);
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
