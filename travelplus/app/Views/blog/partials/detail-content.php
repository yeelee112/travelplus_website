<?php
$blog = $blog ?? [];
$relatedBlogs = $relatedBlogs ?? [];
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key) => lang('Frontend.' . $key, [], $locale);
$listUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
$blogImage = ! empty($blog['image']) ? base_url((string) $blog['image']) : base_url('assets/images/home/banner02.webp');
$title = trim((string) ($blog['title'] ?? ''));
$excerpt = trim((string) ($blog['excerpt'] ?? ''));
$content = trim((string) ($blog['content'] ?? ''));
$author = trim((string) ($blog['author'] ?? $t('blog.authorFallback'))) ?: 'Travel Plus';
$category = trim((string) ($blog['category'] ?? ''));
$publishedLabel = trim((string) ($blog['published_label'] ?? ''));
$publishedAt = trim((string) ($blog['published_at'] ?? ''));
$publishedIso = '';
$publishedTimestamp = $publishedAt !== '' ? strtotime($publishedAt) : false;
if ($publishedTimestamp !== false) {
    $publishedIso = date(DATE_ATOM, $publishedTimestamp);
}
$plainContent = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';
$wordCount = count(preg_split('/\s+/u', trim($plainContent), -1, PREG_SPLIT_NO_EMPTY) ?: []);
$readMinutes = max(1, (int) ceil($wordCount / 180));
$labels = $locale === 'en'
    ? [
        'published' => 'Published',
        'by' => 'By',
        'read' => 'min read',
        'toc' => 'In this article',
        'ctaTitle' => 'Plan your next trip with Travel Plus',
        'ctaDesc' => 'Talk to our team for tours, visa support, MICE programs or a tailor-made itinerary.',
        'ctaPrimary' => 'Contact Travel Plus',
        'ctaSecondary' => 'Find tours',
    ]
    : [
        'published' => 'Ngày đăng',
        'by' => 'Tác giả',
        'read' => 'phút đọc',
        'toc' => 'Trong bài viết này',
        'ctaTitle' => 'Lên kế hoạch chuyến đi cùng Travel Plus',
        'ctaDesc' => 'Gửi nhu cầu để đội ngũ Travel Plus tư vấn tour, visa, MICE hoặc lịch trình thiết kế riêng.',
        'ctaPrimary' => 'Liên hệ tư vấn',
        'ctaSecondary' => 'Tìm tour',
    ];
?>

<section class="travelplus-blog-hero" aria-labelledby="blog-title">
    <div class="container">
        <div class="travelplus-blog-hero-grid">
            <div class="travelplus-blog-hero-copy">
                <?php if ($category !== ''): ?>
                    <a class="travelplus-blog-category" href="<?= esc($listUrl, 'attr') ?>"><?= esc($category) ?></a>
                <?php endif; ?>
                <h1 id="blog-title"><?= esc($title) ?></h1>
                <?php if ($excerpt !== ''): ?>
                    <p class="travelplus-blog-excerpt"><?= esc($excerpt) ?></p>
                <?php endif; ?>
                <ul class="travelplus-blog-meta" aria-label="Article information">
                    <?php if ($publishedLabel !== ''): ?>
                        <li>
                            <i class="bi bi-calendar3"></i>
                            <span><?= esc($labels['published']) ?>: <time datetime="<?= esc($publishedIso, 'attr') ?>"><?= esc($publishedLabel) ?></time></span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <i class="bi bi-person-circle"></i>
                        <span><?= esc($labels['by']) ?> <?= esc($author) ?></span>
                    </li>
                    <li>
                        <i class="bi bi-clock"></i>
                        <span><?= esc((string) $readMinutes) ?> <?= esc($labels['read']) ?></span>
                    </li>
                </ul>
            </div>
            <figure class="travelplus-blog-cover">
                <img src="<?= esc($blogImage, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" width="760" height="500" loading="eager" decoding="async" fetchpriority="high">
            </figure>
        </div>
    </div>
</section>

<section class="travelplus-blog-detail-wrap">
    <div class="container">
        <div class="travelplus-blog-layout">
            <article class="travelplus-blog-article" itemscope itemtype="https://schema.org/BlogPosting">
                <meta itemprop="headline" content="<?= esc($title, 'attr') ?>">
                <meta itemprop="author" content="<?= esc($author, 'attr') ?>">
                <?php if ($publishedIso !== ''): ?>
                    <meta itemprop="datePublished" content="<?= esc($publishedIso, 'attr') ?>">
                <?php endif; ?>
                <div class="travelplus-blog-content" itemprop="articleBody">
                    <?php if ($content !== ''): ?>
                        <?= $content ?>
                    <?php elseif ($excerpt !== ''): ?>
                        <p><?= esc($excerpt) ?></p>
                    <?php endif; ?>
                </div>
            </article>

            <aside class="travelplus-blog-aside" aria-label="Blog sidebar">
                <div class="travelplus-blog-side-card travelplus-blog-cta-card">
                    <span>Travel Plus</span>
                    <h2><?= esc($labels['ctaTitle']) ?></h2>
                    <p><?= esc($labels['ctaDesc']) ?></p>
                    <div class="travelplus-blog-cta-actions">
                        <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('contact', $locale), 'attr') ?>"><?= esc($labels['ctaPrimary']) ?></a>
                        <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('search', $locale), 'attr') ?>"><?= esc($labels['ctaSecondary']) ?></a>
                    </div>
                </div>

                <?php if ($relatedBlogs !== []): ?>
                    <div class="travelplus-blog-side-card">
                        <h2><?= esc($t('blog.relatedPosts')) ?></h2>
                        <div class="travelplus-blog-related-list">
                            <?php foreach ($relatedBlogs as $relatedBlog): ?>
                                <a class="travelplus-blog-related-item" href="<?= esc((string) $relatedBlog['link'], 'attr') ?>">
                                    <img src="<?= esc(base_url((string) ($relatedBlog['image'] ?? 'assets/images/home/banner02.webp')), 'attr') ?>" alt="<?= esc((string) $relatedBlog['title'], 'attr') ?>" width="92" height="72" loading="lazy" decoding="async">
                                    <span>
                                        <small><?= esc((string) $relatedBlog['published_label']) ?></small>
                                        <strong><?= esc((string) $relatedBlog['title']) ?></strong>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>

        <?php if ($relatedBlogs !== []): ?>
            <div class="travelplus-blog-more">
                <div class="travelplus-blog-more-head">
                    <span><?= esc($t('blog.listTitle')) ?></span>
                    <h2><?= esc($t('blog.moreArticles')) ?></h2>
                </div>
                <div class="travelplus-blog-more-grid">
                    <?php foreach (array_slice($relatedBlogs, 0, 3) as $relatedBlog): ?>
                        <a class="travelplus-blog-more-card" href="<?= esc((string) $relatedBlog['link'], 'attr') ?>">
                            <img src="<?= esc(base_url((string) ($relatedBlog['image'] ?? 'assets/images/home/banner02.webp')), 'attr') ?>" alt="<?= esc((string) $relatedBlog['title'], 'attr') ?>" width="420" height="280" loading="lazy" decoding="async">
                            <span><?= esc((string) $relatedBlog['published_label']) ?></span>
                            <h3><?= esc((string) $relatedBlog['title']) ?></h3>
                            <?php if (! empty($relatedBlog['excerpt'])): ?>
                                <p><?= esc((string) $relatedBlog['excerpt']) ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
