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
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-center">
                <div class="swiper-pagination2 paginations"></div>
            </div>
        </div>
    </div><img alt="" loading="lazy" width="70" height="220" decoding="async" data-nimg="1" class="vector1"
        style="color:transparent" src="../assets/img/home2/vector/home2-package-slider-vector1.svg"><img alt=""
        loading="lazy" width="68" height="220" decoding="async" data-nimg="1" class="vector2" style="color:transparent"
        src="../assets/img/home2/vector/home2-package-slider-vector2.svg"><img alt="" loading="lazy" width="60"
        height="220" decoding="async" data-nimg="1" class="vector3" style="color:transparent"
        src="../assets/img/home2/vector/home2-package-slider-vector3.svg">
</div>