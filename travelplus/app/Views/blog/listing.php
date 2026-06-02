<?php
$blogs = $blogs ?? [];
$featuredBlog = $featuredBlog ?? ($blogs[0] ?? null);
$recentBlogs = $recentBlogs ?? array_slice($blogs, 0, 4);
$fallbackImage = base_url('assets/images/home/banner02.jpg');
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key) => lang('Frontend.' . $key, [], $locale);
$listUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$pageTitle = trim((string) $t('blog.listTitle'));
$pageDesc = trim((string) ($meta_desc ?? $t('blog.metaDesc')));
$labels = $locale === 'en'
    ? [
        'eyebrow' => '',
        'latest' => 'Latest articles',
        'featured' => 'Featured story',
        'readMore' => 'Read article',
        'topics' => 'Topics',
        'statsArticles' => 'Published articles',
        'statsTopics' => 'Content topics',
        'recent' => 'Recently published',
        'ctaTitle' => 'Need a real itinerary, not only ideas?',
        'ctaDesc' => 'Travel Plus can turn destination ideas into a practical tour, visa plan or MICE itinerary.',
        'ctaPrimary' => 'Contact Travel Plus',
        'ctaSecondary' => 'Find tours',
        'emptyTitle' => 'No articles yet',
        'seoTitle' => 'Travel ideas, destination guides and trip planning tips',
        'seoDesc' => 'Travel Plus shares practical travel inspiration for travelers and companies: destination ideas, itinerary suggestions, visa notes, MICE travel insights and curated experiences in Vietnam and abroad.',
    ]
    : [
        'eyebrow' => '',
        'latest' => 'Bài viết mới nhất',
        'featured' => 'Bài viết nổi bật',
        'readMore' => 'Đọc bài viết',
        'topics' => 'Chủ đề',
        'statsArticles' => 'Bài viết đã đăng',
        'statsTopics' => 'Nhóm nội dung',
        'recent' => 'Mới đăng gần đây',
        'ctaTitle' => 'Cần một lịch trình thực tế, không chỉ là ý tưởng?',
        'ctaDesc' => 'Travel Plus có thể biến cảm hứng điểm đến thành tour, kế hoạch visa hoặc chương trình MICE phù hợp.',
        'ctaPrimary' => 'Liên hệ tư vấn',
        'ctaSecondary' => 'Tìm tour',
        'emptyTitle' => 'Chưa có bài viết',
        'seoTitle' => 'Ý tưởng điểm đến, kinh nghiệm du lịch và gợi ý lịch trình',
        'seoDesc' => 'Travel Plus chia sẻ cảm hứng du lịch thực tế cho khách cá nhân và doanh nghiệp: điểm đến nổi bật, kinh nghiệm lên lịch trình, lưu ý visa, góc nhìn MICE và những trải nghiệm đáng chọn tại Việt Nam lẫn quốc tế.',
    ];
$imageUrl = static function (string $path) use ($fallbackImage): string {
    $path = trim($path);
    if ($path === '') {
        return $fallbackImage;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return base_url($path);
};
$categories = array_values(array_unique(array_filter(array_map(
    static fn(array $item): string => trim((string) ($item['category'] ?? '')),
    $blogs
))));
$articleCards = $featuredBlog !== null
    ? array_values(array_filter($blogs, static fn(array $blog): bool => (int) ($blog['id'] ?? 0) !== (int) ($featuredBlog['id'] ?? 0)))
    : $blogs;
?>

<section class="travelplus-blog-list-hero" aria-labelledby="blog-list-title">
    <div class="container">
        <div class="travelplus-blog-list-hero-inner">
            <div class="travelplus-blog-list-hero-copy">
                <?php if ($labels['eyebrow'] !== ''): ?>
                    <span><?= esc($labels['eyebrow']) ?></span>
                <?php endif; ?>
                <h1 id="blog-list-title"><?= esc($pageTitle) ?></h1>
                <p><?= esc($pageDesc) ?></p>
            </div>
            <div class="travelplus-blog-list-stats" aria-label="Blog summary">
                <div>
                    <strong><?= esc((string) count($blogs)) ?></strong>
                    <span><?= esc($labels['statsArticles']) ?></span>
                </div>
                <div>
                    <strong><?= esc((string) count($categories)) ?></strong>
                    <span><?= esc($labels['statsTopics']) ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="travelplus-blog-list-wrap">
    <div class="container">
        <?php if ($blogs === []): ?>
            <div class="travelplus-blog-empty">
                <h2><?= esc($labels['emptyTitle']) ?></h2>
                <p><?= esc($t('blog.empty')) ?></p>
                <a href="<?= esc($contactUrl, 'attr') ?>"><?= esc($labels['ctaPrimary']) ?></a>
            </div>
        <?php else: ?>
            <?php if ($featuredBlog !== null): ?>
                <article class="travelplus-blog-featured">
                    <a class="travelplus-blog-featured-image" href="<?= esc((string) $featuredBlog['link'], 'attr') ?>" aria-label="<?= esc((string) $featuredBlog['title'], 'attr') ?>">
                        <img
                            src="<?= esc($imageUrl((string) ($featuredBlog['image'] ?? '')), 'attr') ?>"
                            alt="<?= esc((string) $featuredBlog['title'], 'attr') ?>"
                            width="760"
                            height="500"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async">
                    </a>
                    <div class="travelplus-blog-featured-copy">
                        <span><?= esc($labels['featured']) ?></span>
                        <h2><a href="<?= esc((string) $featuredBlog['link'], 'attr') ?>"><?= esc((string) $featuredBlog['title']) ?></a></h2>
                        <?php if (! empty($featuredBlog['excerpt'])): ?>
                            <p><?= esc((string) $featuredBlog['excerpt']) ?></p>
                        <?php endif; ?>
                        <ul>
                            <?php if (! empty($featuredBlog['category'])): ?>
                                <li><i class="bi bi-folder2-open"></i><?= esc((string) $featuredBlog['category']) ?></li>
                            <?php endif; ?>
                            <?php if (! empty($featuredBlog['published_label'])): ?>
                                <li><i class="bi bi-calendar3"></i><?= esc((string) $featuredBlog['published_label']) ?></li>
                            <?php endif; ?>
                            <?php if (! empty($featuredBlog['author'])): ?>
                                <li><i class="bi bi-person-circle"></i><?= esc((string) $featuredBlog['author']) ?></li>
                            <?php endif; ?>
                        </ul>
                        <a class="travelplus-blog-readmore" href="<?= esc((string) $featuredBlog['link'], 'attr') ?>">
                            <?= esc($labels['readMore']) ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </article>
            <?php endif; ?>

            <div class="travelplus-blog-seo-intro">
                <h2><?= esc($labels['seoTitle']) ?></h2>
                <p><?= esc($labels['seoDesc']) ?></p>
                <div>
                    <a href="<?= esc($searchUrl, 'attr') ?>"><?= esc($labels['ctaSecondary']) ?></a>
                    <a href="<?= esc($contactUrl, 'attr') ?>"><?= esc($labels['ctaPrimary']) ?></a>
                </div>
            </div>

            <div class="travelplus-blog-list-layout">
                <main class="travelplus-blog-list-main" aria-labelledby="blog-latest-title">
                    <div class="travelplus-blog-section-head">
                        <span><?= esc($labels['eyebrow']) ?></span>
                        <h2 id="blog-latest-title"><?= esc($labels['latest']) ?></h2>
                    </div>

                    <div class="travelplus-blog-card-grid">
                        <?php foreach ($articleCards as $blog): ?>
                            <article class="travelplus-blog-list-card">
                                <a class="travelplus-blog-list-card-image" href="<?= esc((string) $blog['link'], 'attr') ?>">
                                    <img
                                        src="<?= esc($imageUrl((string) ($blog['image'] ?? '')), 'attr') ?>"
                                        alt="<?= esc((string) $blog['title'], 'attr') ?>"
                                        width="420"
                                        height="280"
                                        loading="lazy"
                                        decoding="async">
                                    <span class="travelplus-blog-card-badges">
                                        <?php if (! empty($blog['category'])): ?>
                                            <span><i class="bi bi-folder2-open"></i><?= esc((string) $blog['category']) ?></span>
                                        <?php endif; ?>
                                        <?php if (! empty($blog['published_label'])): ?>
                                            <time><i class="bi bi-calendar3"></i><?= esc((string) $blog['published_label']) ?></time>
                                        <?php endif; ?>
                                    </span>
                                </a>
                                <div class="travelplus-blog-list-card-body">
                                    <h3><a href="<?= esc((string) $blog['link'], 'attr') ?>"><?= esc((string) $blog['title']) ?></a></h3>
                                    <?php if (! empty($blog['excerpt'])): ?>
                                        <p><?= esc((string) $blog['excerpt']) ?></p>
                                    <?php endif; ?>
                                    <a class="travelplus-blog-card-link" href="<?= esc((string) $blog['link'], 'attr') ?>">
                                        <?= esc($labels['readMore']) ?>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </main>

                <aside class="travelplus-blog-list-aside" aria-label="Blog listing sidebar">
                    <?php if ($categories !== []): ?>
                        <div class="travelplus-blog-list-panel">
                            <h2><?= esc($labels['topics']) ?></h2>
                            <div class="travelplus-blog-topic-list">
                                <?php foreach ($categories as $category): ?>
                                    <a href="<?= esc($listUrl, 'attr') ?>"><?= esc($category) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="travelplus-blog-list-panel travelplus-blog-list-cta">
                        <span>Travel Plus</span>
                        <h2><?= esc($labels['ctaTitle']) ?></h2>
                        <p><?= esc($labels['ctaDesc']) ?></p>
                        <div>
                            <a href="<?= esc($contactUrl, 'attr') ?>"><?= esc($labels['ctaPrimary']) ?></a>
                            <a href="<?= esc($searchUrl, 'attr') ?>"><?= esc($labels['ctaSecondary']) ?></a>
                        </div>
                    </div>

                    <?php if ($recentBlogs !== []): ?>
                        <div class="travelplus-blog-list-panel">
                            <h2><?= esc($labels['recent']) ?></h2>
                            <div class="travelplus-blog-list-recent">
                                <?php foreach ($recentBlogs as $recentBlog): ?>
                                    <a href="<?= esc((string) $recentBlog['link'], 'attr') ?>">
                                        <img
                                            src="<?= esc($imageUrl((string) ($recentBlog['image'] ?? '')), 'attr') ?>"
                                            alt="<?= esc((string) $recentBlog['title'], 'attr') ?>"
                                            width="92"
                                            height="72"
                                            loading="lazy"
                                            decoding="async">
                                        <span>
                                            <small><?= esc((string) ($recentBlog['category'] ?? '')) ?></small>
                                            <strong><?= esc((string) $recentBlog['title']) ?></strong>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
