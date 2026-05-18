<?php
$homeBlogs = $homeBlogs ?? [];
$blogListUrl = \App\Data\LocalizedPathCatalog::url('blog', service('request')->getLocale() === 'en' ? 'en' : 'vi');
?>

<div class="home4-blog-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc(lang('Frontend.home.blog.title')) ?></h2>
                    <p><?= esc(lang('Frontend.home.blog.desc')) ?></p>
                </div>
            </div>
        </div>

        <?php if ($homeBlogs === []): ?>
            <div class="alert alert-info text-center"><?= esc(lang('Frontend.home.blog.empty')) ?></div>
        <?php else: ?>
            <div class="row g-4 mb-40">
                <?php foreach ($homeBlogs as $blog): ?>
                    <div class="col-lg-4 col-md-6 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
                        <div class="blog-card2 two">
                            <div class="blog-img-wrap">
                                <a class="blog-img" href="<?= esc((string) $blog['link']) ?>" aria-label="<?= esc('Xem bài viết ' . (string) $blog['title']) ?>">
                                    <img alt="<?= esc((string) $blog['title']) ?>" loading="lazy" src="<?= esc(base_url((string) ($blog['image'] ?? 'assets/images/home/banner02.jpg'))) ?>">
                                </a>
                                <a class="location" href="<?= esc($blogListUrl) ?>">
                                    <?= esc((string) $blog['category']) ?>
                                </a>
                            </div>
                            <div class="blog-content">
                                <a class="blog-date" href="<?= esc((string) $blog['link']) ?>"><?= esc((string) $blog['published_label']) ?></a>
                                <h4><a href="<?= esc((string) $blog['link']) ?>"><?= esc((string) $blog['title']) ?></a></h4>
                                <p><?= esc((string) $blog['excerpt']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                <div class="col-lg-12 d-flex justify-content-center">
                    <a class="primary-btn1 two transparent" href="<?= esc($blogListUrl) ?>">
                        <span><?= esc(lang('Frontend.home.blog.cta')) ?><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path></svg></span>
                        <span><?= esc(lang('Frontend.home.blog.cta')) ?><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path></svg></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
