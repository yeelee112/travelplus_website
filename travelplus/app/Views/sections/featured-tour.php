<?php $featuredTours = $featuredTours ?? $tours ?? getFeaturedTours(6); ?>

<?php if (! empty($featuredTours)): ?>
<div class="home2-package-slider-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc(lang('Frontend.home.featuredTour.title')) ?></h2>
                    <p><?= esc(lang('Frontend.home.featuredTour.desc')) ?></p>
                </div>
            </div>
        </div>
        <div class="row mb-40">
            <div class="col-lg-12">
                <div class="swiper home-trip-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($featuredTours as $tour): ?>
                            <div class="swiper-slide">
                                <?= view('components/tour-card', ['tour' => $tour]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <div class="swiper-pagination2 paginations"></div>
        </div>
    </div>
</div>
<?php endif; ?>
