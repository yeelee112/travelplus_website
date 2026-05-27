<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class BlogService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function hasTables(): bool
    {
        return $this->db->tableExists('blogs') && $this->db->tableExists('blog_translations');
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
    public function getPublishedBlogs(string $locale, int $limit = 12): array
    {
        if (! $this->hasTables()) {
            return [];
        }

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
            ->limit($limit)
            ->get()
            ->getResultArray();

        return array_map(fn (array $row): array => $this->mapBlogRow($row, $locale), $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBlogBySlug(string $locale, string $slug): ?array
    {
        if (! $this->hasTables()) {
            return null;
        }

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

        $rows = $builder
            ->orderBy('b.published_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

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
        $rawCategory = trim((string) ($row['category'] ?? ''));

        return [
            'id' => (int) $row['id'],
            'category_raw' => $rawCategory,
            'category' => $this->translateCategory($rawCategory, $locale),
            'author' => (string) ($row['author_name'] ?? 'Travel Plus'),
            'thumbnail' => (string) ($row['thumbnail'] ?? ''),
            'cover_image' => (string) ($row['cover_image'] ?? ''),
            'featured_image' => (string) ($row['featured_image'] ?? ''),
            'image' => $image,
            'published_at' => (string) ($row['published_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'published_label' => $this->formatPublishedAt((string) ($row['published_at'] ?? '')),
            'title' => (string) ($row['title'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'excerpt' => (string) ($row['excerpt'] ?? ''),
            'content' => (string) ($row['content'] ?? ''),
            'meta_title' => (string) ($row['meta_title'] ?? ''),
            'meta_description' => (string) ($row['meta_description'] ?? ''),
            'link' => localized_url($basePath . '/' . (string) ($row['slug'] ?? '')),
        ];
    }

    private function translateCategory(string $category, string $locale): string
    {
        if ($category === '') {
            return '';
        }

        $normalized = mb_strtolower(trim($category));
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
