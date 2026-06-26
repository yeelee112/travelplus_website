<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Throwable;

class BlogService
{
    private const SCHEMA_CACHE_KEY = 'db_blog_tables_ready';
    private const SCHEMA_CACHE_TTL = 3600;

    private BaseConnection $db;
    private static ?bool $tablesReady = null;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function hasTables(): bool
    {
        if (self::$tablesReady !== null) {
            return self::$tablesReady;
        }

        try {
            $cached = cache()->get(self::SCHEMA_CACHE_KEY);
            if ($cached !== null) {
                self::$tablesReady = (bool) $cached;

                return self::$tablesReady;
            }
        } catch (Throwable) {
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$tablesReady = false;

            return false;
        }

        $checked = false;
        try {
            self::$tablesReady = $this->db->tableExists('blogs') && $this->db->tableExists('blog_translations');
            $checked = true;
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Blog table check failed');
            self::$tablesReady = false;
        }

        if ($checked) {
            try {
                cache()->save(self::SCHEMA_CACHE_KEY, self::$tablesReady ? 1 : 0, self::SCHEMA_CACHE_TTL);
            } catch (Throwable) {
            }
        }

        return self::$tablesReady;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getHomeBlogs(string $locale, int $limit = 3): array
    {
        return array_slice($this->getPublishedBlogs($locale, 50), 0, $limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPublishedBlogs(string $locale, int $limit = 12, int $offset = 0): array
    {
        if (! $this->hasTables()) {
            return [];
        }

        try {
            $rows = $this->db->table('blogs b')
                ->select('
                    b.id,
                    b.category,
                    b.author_name,
                    b.thumbnail,
                    b.cover_image,
                    b.featured_image,
                    b.published_at,
                    b.updated_at,
                    b.is_featured,
                    bt.locale,
                    bt.title,
                    bt.slug,
                    bt.excerpt,
                    bt.meta_title,
                    bt.meta_description
                ')
                ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
                ->where('b.status', 'published')
                ->where('bt.locale', $locale)
                ->orderBy('b.is_featured', 'DESC')
                ->orderBy('b.published_at', 'DESC')
                ->limit($limit, max(0, $offset))
                ->get()
                ->getResultArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Blog list load failed');

            return [];
        }

        return array_map(fn (array $row): array => $this->mapBlogRow($row, $locale), $rows);
    }

    public function countPublishedBlogs(string $locale): int
    {
        if (! $this->hasTables()) {
            return 0;
        }

        try {
            return (int) $this->db->table('blogs b')
                ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
                ->where('b.status', 'published')
                ->where('bt.locale', $locale)
                ->countAllResults();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Blog count failed');

            return 0;
        }
    }

    /**
     * @return list<string>
     */
    public function getPublishedCategories(string $locale): array
    {
        if (! $this->hasTables()) {
            return [];
        }

        try {
            $rows = $this->db->table('blogs b')
                ->select('b.category')
                ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
                ->where('b.status', 'published')
                ->where('bt.locale', $locale)
                ->where('b.category !=', '')
                ->groupBy('b.category')
                ->orderBy('b.category', 'ASC')
                ->get()
                ->getResultArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Blog category load failed');

            return [];
        }

        $categories = [];
        foreach ($rows as $row) {
            $category = TextEncodingService::repair(trim((string) ($row['category'] ?? '')));
            if ($category !== '') {
                $categories[] = $this->translateCategory($category, $locale);
            }
        }

        return array_values(array_unique($categories));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBlogBySlug(string $locale, string $slug): ?array
    {
        if (! $this->hasTables()) {
            return null;
        }

        try {
            $row = $this->db->table('blogs b')
                ->select('
                    b.id,
                    b.category,
                    b.author_name,
                    b.thumbnail,
                    b.cover_image,
                    b.featured_image,
                    b.published_at,
                    b.updated_at,
                    b.is_featured,
                    bt.locale,
                    bt.title,
                    bt.slug,
                    bt.excerpt,
                    bt.content,
                    bt.meta_title,
                    bt.meta_description
                ')
                ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
                ->where('b.status', 'published')
                ->where('bt.locale', $locale)
                ->where('bt.slug', $slug)
                ->get()
                ->getRowArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Blog detail load failed');

            return null;
        }

        if ($row === null) {
            return null;
        }

        return $this->mapBlogRow($row, $locale);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRelatedBlogs(string $locale, int $excludeId, string $category = '', int $limit = 3): array
    {
        if (! $this->hasTables()) {
            return [];
        }

        $builder = $this->db->table('blogs b')
            ->select('
                b.id,
                b.category,
                b.author_name,
                b.thumbnail,
                b.cover_image,
                b.featured_image,
                b.published_at,
                b.updated_at,
                bt.locale,
                bt.title,
                bt.slug,
                bt.excerpt,
                bt.meta_title,
                bt.meta_description
            ')
            ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
            ->where('b.status', 'published')
            ->where('bt.locale', $locale)
            ->where('b.id !=', $excludeId);

        if ($category !== '') {
            $builder->where('b.category', $category);
        }

        try {
            $rows = $builder
                ->orderBy('b.published_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Related blogs load failed');

            return [];
        }

        return array_map(fn (array $row): array => $this->mapBlogRow($row, $locale), $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapBlogRow(array $row, string $locale): array
    {
        $basePath = $locale === 'en' ? 'travel-inspiration' : 'cam-hung-du-lich';
        $image = (string) ($row['featured_image'] ?: $row['cover_image'] ?: $row['thumbnail'] ?: 'assets/images/home/banner02.jpg');
        $rawCategory = TextEncodingService::repair(trim((string) ($row['category'] ?? '')));

        return [
            'id' => (int) $row['id'],
            'category_raw' => $rawCategory,
            'category' => $this->translateCategory($rawCategory, $locale),
            'author' => TextEncodingService::repairNullable($row['author_name'] ?? 'Travel Plus'),
            'thumbnail' => (string) ($row['thumbnail'] ?? ''),
            'cover_image' => (string) ($row['cover_image'] ?? ''),
            'featured_image' => (string) ($row['featured_image'] ?? ''),
            'image' => $image,
            'published_at' => (string) ($row['published_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'published_label' => $this->formatPublishedAt((string) ($row['published_at'] ?? '')),
            'title' => TextEncodingService::repairNullable($row['title'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'excerpt' => TextEncodingService::repairNullable($row['excerpt'] ?? ''),
            'content' => TextEncodingService::repairNullableHtml($row['content'] ?? ''),
            'meta_title' => TextEncodingService::repairNullable($row['meta_title'] ?? ''),
            'meta_description' => TextEncodingService::repairNullable($row['meta_description'] ?? ''),
            'link' => localized_url($basePath . '/' . (string) ($row['slug'] ?? '')),
        ];
    }

    private function translateCategory(string $category, string $locale): string
    {
        if ($category === '') {
            return '';
        }

        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower(trim($category))
            : strtolower(trim($category));
        $categoryMap = [
            'cam hung du lich' => ['vi' => 'Cảm hứng du lịch', 'en' => 'Travel Inspiration'],
            'cảm hứng du lịch' => ['vi' => 'Cảm hứng du lịch', 'en' => 'Travel Inspiration'],
            'travel inspiration' => ['vi' => 'Cảm hứng du lịch', 'en' => 'Travel Inspiration'],
            'kinh nghiem du lich' => ['vi' => 'Kinh nghiệm du lịch', 'en' => 'Travel Tips'],
            'kinh nghiệm du lịch' => ['vi' => 'Kinh nghiệm du lịch', 'en' => 'Travel Tips'],
            'travel tips' => ['vi' => 'Kinh nghiệm du lịch', 'en' => 'Travel Tips'],
            'diem den noi bat' => ['vi' => 'Điểm đến nổi bật', 'en' => 'Featured Destinations'],
            'điểm đến nổi bật' => ['vi' => 'Điểm đến nổi bật', 'en' => 'Featured Destinations'],
            'featured destinations' => ['vi' => 'Điểm đến nổi bật', 'en' => 'Featured Destinations'],
            'am thuc va van hoa' => ['vi' => 'Ẩm thực và văn hóa', 'en' => 'Food & Culture'],
            'ẩm thực và văn hóa' => ['vi' => 'Ẩm thực và văn hóa', 'en' => 'Food & Culture'],
            'food & culture' => ['vi' => 'Ẩm thực và văn hóa', 'en' => 'Food & Culture'],
        ];

        if (isset($categoryMap[$normalized])) {
            return $categoryMap[$normalized][$locale] ?? $categoryMap[$normalized]['vi'];
        }

        return $category;
    }

    private function formatPublishedAt(string $publishedAt): string
    {
        if ($publishedAt === '') {
            return '';
        }

        $timestamp = strtotime($publishedAt);

        return $timestamp ? date('d/m/Y', $timestamp) : $publishedAt;
    }
}
