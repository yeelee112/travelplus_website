<?php
$blog = $blog ?? [];
$relatedBlogs = $relatedBlogs ?? [];
$blogImage = ! empty($blog['image']) ? base_url((string) $blog['image']) : base_url('assets/images/home/banner02.jpg');
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key) => lang('Frontend.' . $key, [], $locale);
$listUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
?>

<div class="breadcrumb-section" style="background-image:linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url('<?= esc($blogImage) ?>')">
    <div class="container">
        <div class="banner-content">
            <h1><?= esc((string) ($blog['title'] ?? '')) ?></h1>
            <ul class="breadcrumb-list">
                <li><a href="<?= esc(localized_url('/')) ?>"><?= esc($t('blog.home')) ?></a></li>
                <li><a href="<?= esc($listUrl) ?>"><?= esc($t('blog.listTitle')) ?></a></li>
                <li><?= esc((string) ($blog['title'] ?? '')) ?></li>
            </ul>
        </div>
    </div>
</div>

<div class="inspiration-details-page pt-100 mb-100">
    <div class="container">
        <div class="row g-lg-4 gy-5 justify-content-between">
            <div class="col-xl-8 col-lg-8">
                <div class="inspiration-details">
                    <div class="inspiration-image mb-40">
                        <img alt="<?= esc((string) ($blog['title'] ?? '')) ?>" loading="lazy" decoding="async" width="860" height="520" src="<?= esc($blogImage) ?>">
                    </div>

                    <div class="tag-and-social-area mb-30">
                        <div class="tag-area">
                            <h6><?= esc((string) ($blog['category'] ?? '')) ?></h6>
                            <ul class="tag-list">
                                <li><span><?= esc((string) ($blog['published_label'] ?? '')) ?></span></li>
                                <li><span><?= esc((string) ($blog['author'] ?? $t('blog.authorFallback'))) ?></span></li>
                            </ul>
                        </div>
                    </div>

                    <?php if (! empty($blog['excerpt'])): ?>
                        <p class="mb-30"><?= esc((string) $blog['excerpt']) ?></p>
                    <?php endif; ?>

                    <div class="blog-detail-editor">
                        <?= $blog['content'] ?? '' ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4">
                <div class="blog-sidebar-area">
                    <div class="single-widget mb-30">
                        <h5 class="widget-title"><?= esc($t('blog.relatedPosts')) ?></h5>
                        <?php foreach ($relatedBlogs as $relatedBlog): ?>
                            <div class="recent-post-widget mb-30">
                                <div class="recent-post-img">
                                    <a href="<?= esc((string) $relatedBlog['link']) ?>">
                                        <img alt="<?= esc((string) $relatedBlog['title']) ?>" loading="lazy" decoding="async" width="160" height="110" src="<?= esc(base_url((string) ($relatedBlog['image'] ?? 'assets/images/home/banner02.jpg'))) ?>">
                                    </a>
                                </div>
                                <div class="recent-post-content">
                                    <a href="<?= esc((string) $relatedBlog['link']) ?>"><?= esc((string) $relatedBlog['published_label']) ?></a>
                                    <h6><a href="<?= esc((string) $relatedBlog['link']) ?>"><?= esc((string) $relatedBlog['title']) ?></a></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($relatedBlogs !== []): ?>
            <div class="related-posts-section pt-40">
                <div class="row mb-30">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h3><?= esc($t('blog.moreArticles')) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <?php foreach (array_slice($relatedBlogs, 0, 3) as $relatedBlog): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="blog-card2 two">
                                <div class="blog-img-wrap">
                                    <a class="blog-img" href="<?= esc((string) $relatedBlog['link']) ?>">
                                        <img alt="<?= esc((string) $relatedBlog['title']) ?>" loading="lazy" decoding="async" width="420" height="280" src="<?= esc(base_url((string) ($relatedBlog['image'] ?? 'assets/images/home/banner02.jpg'))) ?>">
                                    </a>
                                    <a class="blog-category" href="<?= esc((string) $relatedBlog['link']) ?>"><?= esc((string) $relatedBlog['published_label']) ?></a>
                                </div>
                                <div class="blog-content">
                                    <h4><a href="<?= esc((string) $relatedBlog['link']) ?>"><?= esc((string) $relatedBlog['title']) ?></a></h4>
                                    <p><?= esc((string) $relatedBlog['excerpt']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
