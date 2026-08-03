<?php

use App\Services\ZaloOaTokenService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Zalo;

/**
 * @internal
 */
final class ZaloOaTokenServiceTest extends CIUnitTestCase
{
    public function testBuildsOaPermissionUrlWithExactCallbackAndState(): void
    {
        $config = new Zalo();
        $config->appId = '861108087263637056';
        $config->callbackUrl = 'https://travelplusvn.com/zalo/oauth/callback';
        $config->permissionEndpoint = 'https://oauth.zaloapp.com/v4/oa/permission';
        $db = $this->createMock(BaseConnection::class);
        $service = new ZaloOaTokenService($config, $db);

        $url = $service->authorizationUrl('state with special/+', 'pkce-challenge-value');
        $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);

        $this->assertSame('https', $parts['scheme'] ?? '');
        $this->assertSame('oauth.zaloapp.com', $parts['host'] ?? '');
        $this->assertSame('/v4/oa/permission', $parts['path'] ?? '');
        $this->assertSame('861108087263637056', $query['app_id'] ?? '');
        $this->assertSame('https://travelplusvn.com/zalo/oauth/callback', $query['redirect_uri'] ?? '');
        $this->assertSame('state with special/+', $query['state'] ?? '');
        $this->assertSame('pkce-challenge-value', $query['code_challenge'] ?? '');
    }

    public function testRequiresAppSecretForOauthConnection(): void
    {
        $config = new Zalo();
        $config->appId = '861108087263637056';
        $config->appSecret = '';
        $config->callbackUrl = 'https://travelplusvn.com/zalo/oauth/callback';
        $service = new ZaloOaTokenService($config, $this->createMock(BaseConnection::class));

        $this->assertFalse($service->isOauthConfigured());

        $config->appSecret = 'configured-outside-source';
        $this->assertTrue($service->isOauthConfigured());
    }
}
