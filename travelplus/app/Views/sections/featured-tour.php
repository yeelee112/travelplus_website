<div class="home2-package-slider-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms"
            data-wow-duration="1500ms">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2>Featured Tour</h2>
                    <p>Favourite destinations based on customer reviews</p>
                </div>
            </div>
        </div>
        <div class="row mb-40">
            <div class="col-lg-12">
                <div class="swiper home-trip-slider">
                    <div class="swiper-wrapper">
                            <?php foreach ($tours as $tour): ?>
                                <div class="swiper-slide">
                                    <?= view('components/tour-card', ['tour' => $tour]) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class=" d-flex justify-content-center">
                <div class="swiper-pagination2 paginations"></div>
            </div>
    </div>
</div>