<?php
$blogs = $blogs ?? [];
$featuredBlog = $featuredBlog ?? ($blogs[0] ?? null);
$recentBlogs = $recentBlogs ?? array_slice($blogs, 0, 4);
$fallbackImage = base_url('assets/images/home/banner02.jpg');
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key) => lang('Frontend.' . $key, [], $locale);
$listUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
?>

<div class="travel-inspiration-page pt-100 mb-100">
    <div class="container">
        <?php if ($blogs === []): ?>
            <div class="alert alert-info"><?= esc($t('blog.empty')) ?></div>
        <?php else: ?>
            <div class="row gy-5 justify-content-between">
                <div class="col-xl-8 col-lg-8">
                    <?php if ($featuredBlog !== null): ?>
                        <div class="row gy-md-4 gy-3 mb-30">
                            <div class="col-lg-12">
                                <div class="blog-card2 six">
                                    <div class="blog-img-wrap">
                                        <a class="blog-img" href="<?= esc((string) $featuredBlog['link']) ?>">
                                            <img
                                                alt="<?= esc((string) $featuredBlog['title']) ?>"
                                                loading="lazy"
                                                src="<?= esc(base_url((string) ($featuredBlog['image'] ?: $fallbackImage))) ?>">
                                        </a>
                                        <a class="location" href="<?= esc($listUrl) ?>">
                                            <?= esc((string) $featuredBlog['category']) ?>
                                        </a>
                                        <a class="blog-category" href="<?= esc((string) $featuredBlog['link']) ?>">
                                            <?= esc((string) $featuredBlog['published_label']) ?>
                                        </a>
                                    </div>
                                    <div class="blog-content">
                                        <h4><a href="<?= esc((string) $featuredBlog['link']) ?>"><?= esc((string) $featuredBlog['title']) ?></a></h4>
                                        <p><?= esc((string) $featuredBlog['excerpt']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row gy-md-4 gy-3 mb-60">
                        <?php foreach (array_slice($blogs, 1) as $blog): ?>
                            <div class="col-lg-6 col-md-6 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
                                <div class="blog-card2 two">
                                    <div class="blog-img-wrap">
                                        <a class="blog-img" href="<?= esc((string) $blog['link']) ?>">
                                            <img
                                                alt="<?= esc((string) $blog['title']) ?>"
                                                loading="lazy"
                                                src="<?= esc(base_url((string) ($blog['image'] ?: $fallbackImage))) ?>">
                                        </a>
                                        <a class="location" href="<?= esc($listUrl) ?>">
                                            <?= esc((string) $blog['category']) ?>
                                        </a>
                                        <a class="blog-category" href="<?= esc((string) $blog['link']) ?>">
                                            <?= esc((string) $blog['published_label']) ?>
                                        </a>
                                    </div>
                                    <div class="blog-content">
                                        <h4><a href="<?= esc((string) $blog['link']) ?>"><?= esc((string) $blog['title']) ?></a></h4>
                                        <p><?= esc((string) $blog['excerpt']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4">
                    <div class="blog-sidebar-area">
                        <div class="single-widget mb-30">
                            <h5 class="widget-title"><?= esc($t('blog.recentPosts')) ?></h5>
                            <?php foreach ($recentBlogs as $recentBlog): ?>
                                <div class="recent-post-widget mb-30">
                                    <div class="recent-post-img">
                                        <a href="<?= esc((string) $recentBlog['link']) ?>">
                                            <img
                                                alt="<?= esc((string) $recentBlog['title']) ?>"
                                                loading="lazy"
                                                src="<?= esc(base_url((string) ($recentBlog['image'] ?: $fallbackImage))) ?>">
                                        </a>
                                    </div>
                                    <div class="recent-post-content">
                                        <a href="<?= esc((string) $recentBlog['link']) ?>"><?= esc((string) $recentBlog['published_label']) ?></a>
                                        <h6><a href="<?= esc((string) $recentBlog['link']) ?>"><?= esc((string) $recentBlog['title']) ?></a></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="single-widget">
                            <h5 class="widget-title"><?= esc($t('blog.categories')) ?></h5>
                            <ul class="tag-list">
                                <?php foreach (array_unique(array_filter(array_map(static fn ($item) => (string) ($item['category'] ?? ''), $blogs))) as $category): ?>
                                    <li><a href="<?= esc($listUrl) ?>"><?= esc($category) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
