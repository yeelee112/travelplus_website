document.addEventListener('DOMContentLoaded', function () {
    const sliderElement = document.querySelector('.home2-banner-slider');

    if (!sliderElement || typeof Swiper === 'undefined') {
        return;
    }

    new Swiper(sliderElement, {
        slidesPerView: 1,
        speed: 1500,
        loop: true,
        autoplay: {
            delay: 3000,
            pauseOnMouseEnter: true,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true,
        },
        navigation: {
            nextEl: '.banner-slider-next',
            prevEl: '.banner-slider-prev',
        },
    });

    const locationSlider = new Swiper('.package-dt-location-slider', {
        slidesPerView: 'auto',
        spaceBetween: 24,
        speed: 1500,
        loop: true,

        autoplay: {
            delay: 3000,
            pauseOnMouseEnter: true,
            disableOnInteraction: false,
        },

        navigation: {
            nextEl: '.location-slider-next',
            prevEl: '.location-slider-prev',
        },

        // responsive chuẩn giống web travel
        breakpoints: {
            0: {
                slidesPerView: 1.2,
            },
            576: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 2.5,
            },
            992: {
                slidesPerView: 3,
            },
            1200: {
                slidesPerView: 3.5,
            }
        }
    });
});
