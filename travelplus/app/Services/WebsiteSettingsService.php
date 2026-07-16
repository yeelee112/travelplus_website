<?php

namespace App\Services;

class WebsiteSettingsService
{
    /**
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'hotline_e164' => '+84795681568',
        'hotline_vi' => '079 568 1 568',
        'hotline_en' => '(+84) 79 568 1 568',
        'email' => 'info@travelplusvn.com',
        'facebook_url' => 'https://www.facebook.com/uuthedulich.vietnam',
        'messenger_url' => 'https://m.me/uuthedulich.vietnam',
        'youtube_url' => 'https://www.youtube.com/@TravelPlus2023',
        'zalo_url' => 'https://zalo.me/84795681568',
        'company_tax_id' => '0305475784',
        'travel_license' => '79-114/2014/TCDL-GP LHQT',
        'office_hcm_address_vi' => '3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP.HCM',
        'office_hcm_address_en' => '3/30A Thich Quang Duc Street, Duc Nhuan Ward, Ho Chi Minh City',
        'office_hcm_map_url' => 'https://maps.app.goo.gl/PkqKgEp4rthxbNUn9',
        'office_hanoi_address_vi' => '47 đường Lê Văn Hưu, Phường Hai Bà Trưng, Hà Nội',
        'office_hanoi_address_en' => '47 Le Van Huu Street, Hai Ba Trung Ward, Hanoi',
        'office_hanoi_map_url' => 'https://maps.app.goo.gl/9Q5he5PYRqdr1bdEA',
        'office_danang_address_vi' => 'Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, Đà Nẵng',
        'office_danang_address_en' => '4th Floor, Mien Trung Helicopter Building, Nguyen Van Linh Street, Hoa Cuong Ward, Da Nang',
        'office_danang_map_url' => 'https://maps.app.goo.gl/FFjiLtqRNWxAjvASA',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private static array $memoryCache = [];

    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? WRITEPATH . 'stats' . DIRECTORY_SEPARATOR . 'website-settings.json';
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        if (isset(self::$memoryCache[$this->path])) {
            return self::$memoryCache[$this->path];
        }

        $settings = self::DEFAULTS;
        if (is_file($this->path) && (int) @filesize($this->path) <= 64 * 1024) {
            $decoded = json_decode((string) @file_get_contents($this->path), true);
            if (is_array($decoded)) {
                foreach (array_keys(self::DEFAULTS) as $key) {
                    if (isset($decoded[$key]) && is_string($decoded[$key]) && trim($decoded[$key]) !== '') {
                        $settings[$key] = trim($decoded[$key]);
                    }
                }
            }
        }

        self::$memoryCache[$this->path] = $settings;

        return $settings;
    }

    public function get(string $key): string
    {
        $settings = $this->all();

        return (string) ($settings[$key] ?? '');
    }

    public function phoneDisplay(string $locale): string
    {
        return $this->get($locale === 'en' ? 'hotline_en' : 'hotline_vi');
    }

    /**
     * @param array<string, mixed> $values
     */
    public function save(array $values): bool
    {
        $settings = $this->all();
        foreach (array_keys(self::DEFAULTS) as $key) {
            if (isset($values[$key]) && is_scalar($values[$key])) {
                $value = mb_substr(trim((string) $values[$key]), 0, 500);
                if ($value !== '') {
                    $settings[$key] = $value;
                }
            }
        }

        $directory = dirname($this->path);
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return false;
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            return false;
        }

        $written = @file_put_contents($this->path, $json . PHP_EOL, LOCK_EX);
        if ($written === false) {
            return false;
        }

        self::$memoryCache[$this->path] = $settings;

        return true;
    }

    public static function resetMemoryCache(): void
    {
        self::$memoryCache = [];
    }
}
