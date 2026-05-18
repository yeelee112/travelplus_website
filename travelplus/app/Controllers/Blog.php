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
        $blogs = $this->blogService->getPublishedBlogs($locale, 12);

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

        return view('blog/index', [
            'blogs' => $blogs,
            'featuredBlog' => $blogs[0] ?? null,
            'recentBlogs' => array_slice($blogs, 0, 4),
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'canonical_url' => localized_url($listPath),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => LocalizedPathCatalog::url('blog', 'vi')],
                ['hreflang' => 'en', 'href' => LocalizedPathCatalog::url('blog', 'en')],
                ['hreflang' => 'x-default', 'href' => LocalizedPathCatalog::url('blog', 'vi')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, (string) localized_url($listPath)),
                $seo->webpageSchema($metaTitle, $metaDesc, (string) localized_url($listPath)),
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

        return view('blog/show', [
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'meta_image' => base_url((string) $blog['image']),
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
            ],
        ]);
    }
}
