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

      if (document.querySelector(".home1-trip-slider")) {
    new Swiper(".home1-trip-slider", {
      slidesPerView: "auto",
      speed: 1500,
      spaceBetween: 24,

      autoplay: {
        delay: 2500,
        pauseOnMouseEnter: true,
        disableOnInteraction: false,
      },

      pagination: {
        el: ".swiper-pagination2",
        clickable: true,
      },

      breakpoints: {
        280: { slidesPerView: 1 },
        386: { slidesPerView: 1 },
        576: { slidesPerView: 1 },
        768: { slidesPerView: 2, spaceBetween: 15 },
        992: { slidesPerView: 3 },
        1200: { slidesPerView: 3 },
        1400: { slidesPerView: 3 },
      },
    });
  }
});
