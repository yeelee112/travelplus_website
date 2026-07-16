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
