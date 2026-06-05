<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\BlogService;
use App\Services\EntityViewService;
use App\Services\SeoService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Blog extends BaseController
{
    private BlogService $blogService;
    public function __construct()
    {
        $this->blogService = new BlogService();
    }

    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $listPath = LocalizedPathCatalog::path('blog', $locale);
        $baseListUrl = localized_url($listPath);
        $perPage = 7;
        $totalBlogs = $this->blogService->countPublishedBlogs($locale);
        $totalPages = max(1, (int) ceil($totalBlogs / $perPage));
        $currentPage = max(1, (int) $this->request->getGet('page'));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $blogs = $this->blogService->getPublishedBlogs($locale, $perPage, $offset);
        $recentBlogs = $this->blogService->getPublishedBlogs($locale, 4);
        $categories = $this->blogService->getPublishedCategories($locale);
        $listUrl = $currentPage > 1 ? $baseListUrl . '?page=' . $currentPage : $baseListUrl;
        $pageUrl = static function (int $page) use ($baseListUrl): string {
            return $page <= 1 ? $baseListUrl : $baseListUrl . '?page=' . $page;
        };
        $hreflangBlogUrl = static function (string $targetLocale, int $page = 1): string {
            $path = LocalizedPathCatalog::path('blog', $targetLocale);
            $url = $targetLocale === 'en'
                ? base_url('en/' . ltrim($path, '/'))
                : base_url(ltrim($path, '/'));

            return $page > 1 ? $url . '?page=' . $page : $url;
        };

        $breadcrumbs = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('blog.listTitle'),
            ],
        ];

        $metaTitle = $t('blog.metaTitle');
        $metaDesc = $t('blog.metaDesc');
        if ($currentPage > 1) {
            $metaTitle .= $locale === 'en' ? ' - Page ' . $currentPage : ' - Trang ' . $currentPage;
        }
        $metaImage = ! empty($blogs[0]['image'])
            ? base_url((string) $blogs[0]['image'])
            : base_url('assets/images/TravelPlus_CompanyProfile.png');

        return view('blog/index', [
            'blogs' => $blogs,
            'featuredBlog' => $blogs[0] ?? null,
            'recentBlogs' => $recentBlogs,
            'categories' => $categories,
            'totalBlogs' => $totalBlogs,
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'total_items' => $totalBlogs,
                'prev_url' => $currentPage > 1 ? $pageUrl($currentPage - 1) : '',
                'next_url' => $currentPage < $totalPages ? $pageUrl($currentPage + 1) : '',
                'page_urls' => array_combine(
                    range(1, $totalPages),
                    array_map($pageUrl, range(1, $totalPages))
                ),
            ],
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'meta_image' => $metaImage,
            'meta_image_alt' => $metaTitle,
            'canonical_url' => $listUrl,
            'pagination_links' => [
                'prev' => $currentPage > 1 ? $pageUrl($currentPage - 1) : '',
                'next' => $currentPage < $totalPages ? $pageUrl($currentPage + 1) : '',
            ],
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => $hreflangBlogUrl('vi', $currentPage)],
                ['hreflang' => 'en', 'href' => $hreflangBlogUrl('en', $currentPage)],
                ['hreflang' => 'x-default', 'href' => $hreflangBlogUrl('vi', $currentPage)],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, (string) $listUrl),
                $seo->webpageSchema($metaTitle, $metaDesc, (string) $listUrl, 'CollectionPage'),
                $seo->itemListSchema($metaTitle, (string) $listUrl, $blogs, 'BlogPosting'),
            ],
        ]);
    }

    public function show(?string $slug = null)
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $listPath = LocalizedPathCatalog::path('blog', $locale);
        $slug = trim((string) $slug);

        if ($slug === '') {
            throw PageNotFoundException::forPageNotFound();
        }

        $blog = $this->blogService->getBlogBySlug($locale, $slug);
        if ($blog === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        (new EntityViewService())->incrementOncePerSession('blogs', (int) ($blog['id'] ?? 0), 'blog');

        $relatedBlogs = $this->blogService->getRelatedBlogs($locale, (int) $blog['id'], (string) ($blog['category_raw'] ?? ''), 4);
        if ($relatedBlogs === []) {
            $relatedBlogs = $this->blogService->getRelatedBlogs($locale, (int) $blog['id'], '', 4);
        }

        $breadcrumbs = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('blog.listTitle'),
                'url' => localized_url($listPath),
            ],
            [
                'label' => (string) $blog['title'],
            ],
        ];

        $metaTitle = (string) ($blog['meta_title'] ?: ($blog['title'] . ' | Travel Plus'));
        $metaDesc = (string) ($blog['meta_description'] ?: $blog['excerpt']);
        $publishedAt = (string) ($blog['published_at'] ?? '');
        $updatedAt = (string) ($blog['updated_at'] ?? $publishedAt);

        return view('blog/show', [
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'meta_type' => 'article',
            'meta_image' => base_url((string) $blog['image']),
            'meta_image_alt' => (string) $blog['title'],
            'meta_published_time' => $publishedAt,
            'meta_updated_time' => $updatedAt,
            'meta_author' => (string) ($blog['author'] ?? 'Travel Plus'),
            'canonical_url' => (string) $blog['link'],
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => switch_locale_url('vi')],
                ['hreflang' => 'en', 'href' => switch_locale_url('en')],
                ['hreflang' => 'x-default', 'href' => switch_locale_url('vi')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, (string) $blog['link']),
                $seo->webpageSchema($metaTitle, $metaDesc, (string) $blog['link']),
                $seo->articleSchema($blog, (string) $blog['link']),
            ],
        ]);
    }
}
