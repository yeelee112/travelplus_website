<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$homeBlogs = array_values($homeBlogs ?? []);
$blogListUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
$featuredBlog = $homeBlogs[0] ?? null;
$sideBlogs = array_slice($homeBlogs, 1, 2);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel inspiration',
        'title' => 'Ideas, destinations and planning tips before you book',
        'desc' => 'Read practical travel articles from Travel Plus to compare destinations, prepare documents and plan a smoother itinerary.',
        'cta' => 'Read all articles',
        'empty' => 'No blog posts are available right now.',
        'read' => 'Read article',
    ]
    : [
        'eyebrow' => 'Cảm hứng du lịch',
        'title' => 'Ý tưởng điểm đến và kinh nghiệm trước khi đặt tour',
        'desc' => 'Đọc các bài viết từ Travel Plus để so sánh điểm đến, chuẩn bị hồ sơ và lên lịch trình phù hợp hơn.',
        'cta' => 'Xem tất cả bài viết',
        'empty' => 'Chưa có bài viết blog để hiển thị.',
        'read' => 'Đọc bài viết',
    ];
?>

<section class="home-page__blog home-section home-section--white" aria-labelledby="home-blog-title">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-blog-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
            <a class="home-section-link" href="<?= esc($blogListUrl, 'attr') ?>">
                <?= esc($copy['cta']) ?>
                <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>

        <?php if ($homeBlogs === []): ?>
            <div class="home-empty-state"><?= esc($copy['empty']) ?></div>
        <?php else: ?>
            <div class="home-blog-layout">
                <?php if ($featuredBlog !== null): ?>
                    <article class="home-blog-feature">
                        <a class="home-blog-feature__media" href="<?= esc((string) $featuredBlog['link'], 'attr') ?>">
                            <img
                                src="<?= esc(base_url((string) ($featuredBlog['image'] ?? 'assets/images/home/banner02.webp')), 'attr') ?>"
                                alt="<?= esc((string) $featuredBlog['title'], 'attr') ?>"
                                width="760"
                                height="460"
                                loading="lazy"
                                decoding="async">
                        </a>
                        <div class="home-blog-feature__body">
                            <div class="home-blog-meta">
                                <?php if (! empty($featuredBlog['category'])): ?>
                                    <span><?= esc((string) $featuredBlog['category']) ?></span>
                                <?php endif; ?>
                                <?php if (! empty($featuredBlog['published_label'])): ?>
                                    <time datetime="<?= esc((string) ($featuredBlog['published_at'] ?? ''), 'attr') ?>"><?= esc((string) $featuredBlog['published_label']) ?></time>
                                <?php endif; ?>
                            </div>
                            <h3><a href="<?= esc((string) $featuredBlog['link'], 'attr') ?>"><?= esc((string) $featuredBlog['title']) ?></a></h3>
                            <?php if (! empty($featuredBlog['excerpt'])): ?>
                                <p><?= esc((string) $featuredBlog['excerpt']) ?></p>
                            <?php endif; ?>
                            <a class="home-inline-link" href="<?= esc((string) $featuredBlog['link'], 'attr') ?>">
                                <?= esc($copy['read']) ?>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endif; ?>

                <div class="home-blog-list">
                    <?php foreach ($sideBlogs as $blog): ?>
                        <article class="home-blog-card">
                            <a class="home-blog-card__media" href="<?= esc((string) $blog['link'], 'attr') ?>">
                                <img
                                    src="<?= esc(base_url((string) ($blog['image'] ?? 'assets/images/home/banner02.webp')), 'attr') ?>"
                                    alt="<?= esc((string) $blog['title'], 'attr') ?>"
                                    width="280"
                                    height="200"
                                    loading="lazy"
                                    decoding="async">
                            </a>
                            <div class="home-blog-card__body">
                                <div class="home-blog-meta">
                                    <?php if (! empty($blog['category'])): ?>
                                        <span><?= esc((string) $blog['category']) ?></span>
                                    <?php endif; ?>
                                    <?php if (! empty($blog['published_label'])): ?>
                                        <time datetime="<?= esc((string) ($blog['published_at'] ?? ''), 'attr') ?>"><?= esc((string) $blog['published_label']) ?></time>
                                    <?php endif; ?>
                                </div>
                                <h3><a href="<?= esc((string) $blog['link'], 'attr') ?>"><?= esc((string) $blog['title']) ?></a></h3>
                                <a class="home-inline-link" href="<?= esc((string) $blog['link'], 'attr') ?>">
                                    <?= esc($copy['read']) ?>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
