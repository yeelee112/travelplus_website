<?php

namespace App\Services;

use Config\App;
use Config\Cookie;
use Config\Email;
use CodeIgniter\Database\BaseConnection;

class SystemHealthService
{
    public const STATUS_OK = 'ok';
    public const STATUS_WARNING = 'warning';
    public const STATUS_ERROR = 'error';

    /**
     * @var array<string, list<string>>
     */
    private const PERFORMANCE_INDEXES = [
        'bookings' => [
            'idx_bookings_customer_email_created',
            'idx_bookings_customer_phone_created',
            'idx_bookings_status_method_created',
        ],
        'crm_leads' => [
            'idx_crm_leads_stage_source_updated',
            'idx_crm_leads_source_updated',
        ],
        'tour_departures' => ['idx_tour_departures_lookup'],
        'tour_media' => ['idx_tour_media_lookup'],
        'tour_reviews' => ['idx_tour_reviews_public'],
        'tour_translations' => ['idx_tour_translations_locale_slug'],
        'location_translations' => ['idx_location_translations_locale_slug'],
        'tours' => ['idx_tours_catalog'],
        'booking_email_logs' => ['idx_booking_email_logs_dedupe'],
        'booking_status_logs' => ['idx_booking_status_logs_timeline'],
        'analytics_page_views' => ['idx_analytics_page_views_journey'],
        'analytics_search_queries' => ['idx_analytics_search_queries_journey'],
    ];

    /**
     * @return array{
     *     generated_at: string,
     *     summary: array{ok: int, warning: int, error: int, total: int, status: string},
     *     groups: list<array{key: string, title: string, description: string, checks: list<array<string, string>>}>
     * }
     */
    public function inspect(): array
    {
        $groups = [
            $this->inspectRuntime(),
            $this->inspectDatabase(),
            $this->inspectEmail(),
            $this->inspectSecurity(),
            $this->inspectStorage(),
        ];

        $checks = [];
        foreach ($groups as $group) {
            array_push($checks, ...$group['checks']);
        }

        return [
            'generated_at' => date('d/m/Y H:i:s'),
            'summary' => self::summarize($checks),
            'groups' => $groups,
        ];
    }

    /**
     * @param list<array<string, string>> $checks
     * @return array{ok: int, warning: int, error: int, total: int, status: string}
     */
    public static function summarize(array $checks): array
    {
        $summary = [self::STATUS_OK => 0, self::STATUS_WARNING => 0, self::STATUS_ERROR => 0];

        foreach ($checks as $check) {
            $status = (string) ($check['status'] ?? '');
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        $overallStatus = self::STATUS_OK;
        if ($summary[self::STATUS_ERROR] > 0) {
            $overallStatus = self::STATUS_ERROR;
        } elseif ($summary[self::STATUS_WARNING] > 0) {
            $overallStatus = self::STATUS_WARNING;
        }

        return [
            'ok' => $summary[self::STATUS_OK],
            'warning' => $summary[self::STATUS_WARNING],
            'error' => $summary[self::STATUS_ERROR],
            'total' => array_sum($summary),
            'status' => $overallStatus,
        ];
    }

    /**
     * @param list<string> $installedIndexes
     * @return array{status: string, installed: int, expected: int, missing: list<string>}
     */
    public static function evaluateIndexCoverage(array $installedIndexes): array
    {
        $expected = self::expectedIndexNames();
        $installed = array_values(array_unique(array_intersect($expected, $installedIndexes)));
        $missing = array_values(array_diff($expected, $installed));

        $status = self::STATUS_OK;
        if ($installed === []) {
            $status = self::STATUS_ERROR;
        } elseif ($missing !== []) {
            $status = self::STATUS_WARNING;
        }

        return [
            'status' => $status,
            'installed' => count($installed),
            'expected' => count($expected),
            'missing' => $missing,
        ];
    }

    /**
     * @return list<string>
     */
    public static function expectedIndexNames(): array
    {
        $indexes = [];
        foreach (self::PERFORMANCE_INDEXES as $tableIndexes) {
            array_push($indexes, ...$tableIndexes);
        }

        return $indexes;
    }

    /**
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function inspectRuntime(): array
    {
        $environment = defined('ENVIRONMENT') ? (string) ENVIRONMENT : 'unknown';
        $requiredExtensions = ['curl', 'fileinfo', 'gd', 'intl', 'json', 'mbstring', 'mysqli', 'openssl'];
        $missingExtensions = array_values(array_filter(
            $requiredExtensions,
            static fn (string $extension): bool => ! extension_loaded($extension)
        ));

        return $this->group(
            'runtime',
            'PHP và môi trường chạy',
            'Phiên bản nền tảng và các extension website cần để xử lý nội dung, ảnh, email và thanh toán.',
            [
                $this->check(
                    'php_version',
                    'Phiên bản PHP',
                    version_compare(PHP_VERSION, '8.1.0', '>=') ? self::STATUS_OK : self::STATUS_ERROR,
                    PHP_VERSION,
                    version_compare(PHP_VERSION, '8.1.0', '>=')
                        ? 'Đáp ứng yêu cầu PHP 8.1 trở lên của dự án.'
                        : 'Phiên bản PHP quá cũ so với yêu cầu của dự án.',
                    'Chọn PHP 8.1 trở lên trong phần cấu hình PHP của hosting.'
                ),
                $this->check(
                    'environment',
                    'Chế độ môi trường',
                    $environment === 'production' ? self::STATUS_OK : self::STATUS_WARNING,
                    $environment,
                    $environment === 'production'
                        ? 'Website đang dùng cấu hình production.'
                        : 'Ở máy local có thể bỏ qua cảnh báo này. Trên hosting nên đặt production.',
                    'Đặt CI_ENVIRONMENT = production trong file .env trên hosting.'
                ),
                $this->check(
                    'php_extensions',
                    'PHP extensions',
                    $missingExtensions === [] ? self::STATUS_OK : self::STATUS_ERROR,
                    $missingExtensions === []
                        ? count($requiredExtensions) . '/' . count($requiredExtensions)
                        : (count($requiredExtensions) - count($missingExtensions)) . '/' . count($requiredExtensions),
                    $missingExtensions === []
                        ? 'Đã bật đầy đủ extension cần thiết.'
                        : 'Đang thiếu: ' . implode(', ', $missingExtensions) . '.',
                    'Bật các extension còn thiếu trong PHP Selector của hosting.'
                ),
                $this->inspectUploadLimit(),
            ]
        );
    }

    /**
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function inspectDatabase(): array
    {
        $checks = [];

        try {
            $db = db_connect();
            $db->initialize();
            $db->query('SELECT 1');
            $databaseName = $this->databaseName($db);

            $checks[] = $this->check(
                'database_connection',
                'Kết nối database',
                self::STATUS_OK,
                'Đã kết nối',
                $databaseName !== '' ? 'Database hiện tại: ' . $databaseName . '.' : 'Kết nối MySQL đang hoạt động.',
                ''
            );

            $coreTables = ['users', 'tours', 'tour_translations', 'bookings', 'crm_leads', 'booking_email_logs'];
            $missingTables = array_values(array_filter(
                $coreTables,
                static fn (string $table): bool => ! $db->tableExists($table)
            ));
            $checks[] = $this->check(
                'database_tables',
                'Bảng dữ liệu chính',
                $missingTables === [] ? self::STATUS_OK : self::STATUS_ERROR,
                (count($coreTables) - count($missingTables)) . '/' . count($coreTables),
                $missingTables === [] ? 'Các bảng vận hành chính đều tồn tại.' : 'Đang thiếu: ' . implode(', ', $missingTables) . '.',
                'Import migration/SQL còn thiếu vào đúng database trong phpMyAdmin.'
            );

            $coverage = self::evaluateIndexCoverage($this->loadInstalledIndexes($db, $databaseName));
            $checks[] = $this->check(
                'performance_indexes',
                'Index tối ưu truy vấn',
                $coverage['status'],
                $coverage['installed'] . '/' . $coverage['expected'],
                $coverage['missing'] === []
                    ? 'Đã cài đủ index tối ưu hiệu năng.'
                    : 'Còn thiếu ' . count($coverage['missing']) . ' index.',
                'Import database/sql/2026-07-16_add_query_performance_indexes.sql bằng phpMyAdmin.'
            );
        } catch (\Throwable $exception) {
            log_message('error', 'System health database check failed: {message}', ['message' => $exception->getMessage()]);
            $checks[] = $this->check(
                'database_connection',
                'Kết nối database',
                self::STATUS_ERROR,
                'Không kết nối',
                'Không thể thực hiện truy vấn kiểm tra.',
                'Kiểm tra database.default trong .env và quyền của database user.'
            );
        }

        return $this->group(
            'database',
            'Database',
            'Kết nối, các bảng cốt lõi và bộ index đã tối ưu cho booking, CRM, catalog và analytics.',
            $checks
        );
    }

    /**
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function inspectEmail(): array
    {
        $email = config(Email::class);
        $protocol = strtolower(trim((string) $email->protocol));
        $fromEmail = trim((string) $email->fromEmail);
        $missing = [];

        if ($fromEmail === '' || filter_var($fromEmail, FILTER_VALIDATE_EMAIL) === false) {
            $missing[] = 'email.fromEmail';
        }
        if ($protocol === 'smtp') {
            foreach (['SMTPHost', 'SMTPUser', 'SMTPPass'] as $property) {
                if (trim((string) $email->{$property}) === '') {
                    $missing[] = 'email.' . $property;
                }
            }
        }
        if (! in_array($protocol, ['mail', 'sendmail', 'smtp'], true)) {
            $missing[] = 'email.protocol';
        }

        return $this->group(
            'email',
            'Email giao dịch',
            'Kiểm tra cấu hình gửi booking và thông báo. Trang này không gửi email thử.',
            [
                $this->check(
                    'email_configuration',
                    'Cấu hình gửi email',
                    $missing === [] ? self::STATUS_OK : self::STATUS_ERROR,
                    $protocol !== '' ? strtoupper($protocol) : 'Chưa đặt',
                    $missing === []
                        ? 'Người gửi: ' . $fromEmail . '. Thông tin xác thực đã được ẩn.'
                        : 'Thiếu hoặc không hợp lệ: ' . implode(', ', $missing) . '.',
                    'Bổ sung các biến email.* còn thiếu trong file .env trên hosting.'
                ),
            ]
        );
    }

    /**
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function inspectSecurity(): array
    {
        $app = config(App::class);
        $cookie = config(Cookie::class);
        $baseUrl = trim((string) $app->baseURL);
        $isHttps = str_starts_with(strtolower($baseUrl), 'https://');
        $secureCookie = (bool) $cookie->secure;
        $httpOnlyCookie = (bool) $cookie->httponly;

        return $this->group(
            'security',
            'URL và bảo mật phiên',
            'Các thiết lập nền tảng giúp tránh URL sai, cookie phiên lộ qua kết nối không bảo mật và lỗi chuyển hướng.',
            [
                $this->check(
                    'base_url',
                    'Địa chỉ website',
                    filter_var($baseUrl, FILTER_VALIDATE_URL) !== false && $isHttps ? self::STATUS_OK : self::STATUS_ERROR,
                    $baseUrl !== '' ? $baseUrl : 'Chưa đặt',
                    $isHttps ? 'Base URL hợp lệ và dùng HTTPS.' : 'Base URL phải là địa chỉ HTTPS đầy đủ của website.',
                    'Đặt app.baseURL = https://travelplusvn.com/ trong .env.'
                ),
                $this->check(
                    'force_https',
                    'Bắt buộc HTTPS',
                    (bool) $app->forceGlobalSecureRequests ? self::STATUS_OK : self::STATUS_ERROR,
                    (bool) $app->forceGlobalSecureRequests ? 'Đang bật' : 'Đang tắt',
                    (bool) $app->forceGlobalSecureRequests ? 'CodeIgniter luôn tạo và chuyển hướng sang URL bảo mật.' : 'Request có thể tiếp tục qua HTTP.',
                    'Đặt app.forceGlobalSecureRequests = true trong .env.'
                ),
                $this->check(
                    'secure_cookie',
                    'Cookie đăng nhập',
                    $secureCookie && $httpOnlyCookie ? self::STATUS_OK : self::STATUS_ERROR,
                    $secureCookie && $httpOnlyCookie ? 'Secure + HttpOnly' : 'Chưa an toàn',
                    $secureCookie && $httpOnlyCookie ? 'Cookie phiên chỉ đi qua HTTPS và không cho JavaScript đọc.' : 'Cookie secure hoặc httponly đang bị tắt.',
                    'Đặt cookie.secure = true và cookie.httponly = true trong .env.'
                ),
            ]
        );
    }

    /**
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function inspectStorage(): array
    {
        $paths = [
            'writable/cache' => WRITEPATH . 'cache',
            'writable/logs' => WRITEPATH . 'logs',
            'writable/session' => WRITEPATH . 'session',
            'writable/stats' => WRITEPATH . 'stats',
            'public/uploads' => FCPATH . 'uploads',
        ];
        $unwritable = [];
        foreach ($paths as $label => $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $unwritable[] = $label;
            }
        }

        $freeBytes = @disk_free_space(WRITEPATH);
        $totalBytes = @disk_total_space(WRITEPATH);
        $diskStatus = self::STATUS_OK;
        $diskDetail = 'Hosting không cung cấp thông tin dung lượng đĩa.';
        $diskValue = 'Không xác định';
        if (is_float($freeBytes) && $freeBytes >= 0) {
            $diskValue = $this->formatBytes((int) $freeBytes) . ' trống';
            $freeRatio = is_float($totalBytes) && $totalBytes > 0 ? $freeBytes / $totalBytes : 1.0;
            $diskStatus = $freeBytes < 512 * 1024 * 1024
                ? self::STATUS_ERROR
                : ($freeBytes < 1024 * 1024 * 1024 || ($freeBytes < 5 * 1024 * 1024 * 1024 && $freeRatio < 0.1)
                    ? self::STATUS_WARNING
                    : self::STATUS_OK);
            $diskDetail = $diskStatus === self::STATUS_OK
                ? 'Còn đủ dung lượng cho log, cache và ảnh tải lên.'
                : 'Dung lượng trống thấp, có thể làm lỗi upload, log hoặc cache.';
        }

        return $this->group(
            'storage',
            'Thư mục và dung lượng',
            'Quyền ghi cần thiết cho session, log, cache, bộ đếm và hình ảnh tải lên.',
            [
                $this->check(
                    'writable_directories',
                    'Quyền ghi thư mục',
                    $unwritable === [] ? self::STATUS_OK : self::STATUS_ERROR,
                    (count($paths) - count($unwritable)) . '/' . count($paths),
                    $unwritable === [] ? 'Tất cả thư mục cần thiết đều ghi được.' : 'Không ghi được: ' . implode(', ', $unwritable) . '.',
                    'Cấp quyền ghi cho các thư mục được liệt kê trong File Manager của hosting.'
                ),
                $this->check(
                    'disk_space',
                    'Dung lượng lưu trữ',
                    $diskStatus,
                    $diskValue,
                    $diskDetail,
                    'Xóa log/cache cũ hoặc tăng dung lượng hosting nếu mức trống quá thấp.'
                ),
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    private function inspectUploadLimit(): array
    {
        $uploadBytes = $this->iniBytes((string) ini_get('upload_max_filesize'));
        $postBytes = $this->iniBytes((string) ini_get('post_max_size'));
        $effectiveBytes = min($uploadBytes, $postBytes);
        $status = $effectiveBytes >= 8 * 1024 * 1024 ? self::STATUS_OK : self::STATUS_WARNING;

        return $this->check(
            'upload_limit',
            'Giới hạn tải file',
            $status,
            $this->formatBytes($effectiveBytes),
            $status === self::STATUS_OK ? 'Đủ cho phần lớn ảnh tour và bài viết.' : 'Giới hạn thấp có thể làm upload ảnh dung lượng lớn thất bại.',
            'Tăng upload_max_filesize và post_max_size lên tối thiểu 8M trong PHP Options.'
        );
    }

    /**
     * @return list<string>
     */
    private function loadInstalledIndexes(BaseConnection $db, string $databaseName): array
    {
        if ($databaseName === '') {
            return [];
        }

        $expected = self::expectedIndexNames();
        $placeholders = implode(',', array_fill(0, count($expected), '?'));
        $sql = 'SELECT DISTINCT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS'
            . ' WHERE TABLE_SCHEMA = ? AND INDEX_NAME IN (' . $placeholders . ')';
        $rows = $db->query($sql, array_merge([$databaseName], $expected))->getResultArray();

        return array_values(array_filter(array_map(
            static fn (array $row): string => (string) ($row['INDEX_NAME'] ?? ''),
            $rows
        )));
    }

    private function databaseName(BaseConnection $db): string
    {
        $result = $db->query('SELECT DATABASE() AS database_name')->getRowArray();

        return trim((string) ($result['database_name'] ?? ''));
    }

    /**
     * @param list<array<string, string>> $checks
     * @return array{key: string, title: string, description: string, checks: list<array<string, string>>}
     */
    private function group(string $key, string $title, string $description, array $checks): array
    {
        return compact('key', 'title', 'description', 'checks');
    }

    /**
     * @return array<string, string>
     */
    private function check(string $key, string $label, string $status, string $value, string $detail, string $action): array
    {
        return compact('key', 'label', 'status', 'value', 'detail', 'action');
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $bytes = (int) $value;
        $unit = strtolower(substr($value, -1));
        if ($unit === 'g') {
            return $bytes * 1024 * 1024 * 1024;
        }
        if ($unit === 'm') {
            return $bytes * 1024 * 1024;
        }
        if ($unit === 'k') {
            return $bytes * 1024;
        }

        return $bytes;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024 / 1024, 1, ',', '.') . ' GB';
        }
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
        }

        return number_format(max(0, $bytes) / 1024, 0, ',', '.') . ' KB';
    }
}
