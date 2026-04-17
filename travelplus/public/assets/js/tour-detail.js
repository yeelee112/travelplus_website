document.addEventListener('DOMContentLoaded', function () {
    const sliderElement = document.querySelector('.home2-banner-slider');

    if (sliderElement && typeof Swiper !== 'undefined') {
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
    }

    if (document.querySelector('.package-dt-location-slider') && typeof Swiper !== 'undefined') {
        new Swiper('.package-dt-location-slider', {
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
            breakpoints: {
                0: { slidesPerView: 1.2 },
                576: { slidesPerView: 2 },
                768: { slidesPerView: 2.5 },
                992: { slidesPerView: 3 },
                1200: { slidesPerView: 3.5 },
            },
        });
    }

    if (document.querySelector('.home1-trip-slider') && typeof Swiper !== 'undefined') {
        new Swiper('.home1-trip-slider', {
            slidesPerView: 'auto',
            speed: 1500,
            spaceBetween: 24,
            autoplay: {
                delay: 2500,
                pauseOnMouseEnter: true,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination2',
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

    // const bookingDateInput = document.querySelector('#bookingModal .tour-booking-date-input');
    // if (bookingDateInput && typeof flatpickr !== 'undefined') {
    //     flatpickr(bookingDateInput, {
    //         mode: 'range',
    //         dateFormat: 'd/m/Y',
    //         minDate: 'today',
    //         disableMobile: true,
    //         locale: 'vn',
    //     });
    // }

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('hide.bs.modal', () => {
            const activeElement = document.activeElement;

            if (activeElement instanceof HTMLElement && modal.contains(activeElement)) {
                activeElement.blur();
            }
        });

        modal.addEventListener('hidden.bs.modal', () => {
            if (document.activeElement instanceof HTMLElement && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }

            if (document.body instanceof HTMLElement) {
                document.body.focus();
            }
        });
    });

    const formatVnd = (value) => `${new Intl.NumberFormat('vi-VN').format(value)}₫`;

    document.querySelectorAll('.additional-service-area').forEach((serviceArea) => {
        const serviceItems = Array.from(serviceArea.querySelectorAll('.booking-service-item'));
        if (serviceItems.length === 0) {
            return;
        }

        const maxTravelers = Number.parseInt(serviceArea.dataset.maxTravelers || '15', 10);
        const totalElement = serviceArea.querySelector('.booking-grand-total');

        const getTotalTravelers = () =>
            serviceItems.reduce((sum, item) => {
                const input = item.querySelector('.quantity__input');
                const value = Number.parseInt(input?.value || '0', 10);
                return sum + (Number.isNaN(value) ? 0 : value);
            }, 0);

        const updateTotals = () => {
            let grandTotal = 0;

            serviceItems.forEach((item) => {
                const input = item.querySelector('.quantity__input');
                const quantity = Number.parseInt(input?.value || '0', 10) || 0;
                const unitPrice = Number.parseInt(item.dataset.unitPrice || '0', 10) || 0;
                const lineTotal = quantity * unitPrice;

                grandTotal += lineTotal;
            });

            if (totalElement) {
                totalElement.textContent = formatVnd(grandTotal);
            }
        };

        const setInputValue = (input, nextValue) => {
            const minValue = Number.parseInt(input.dataset.min || '0', 10) || 0;
            const currentValue = Number.parseInt(input.value || '0', 10) || 0;
            const totalWithoutCurrent = getTotalTravelers() - currentValue;
            const maxAllowed = Math.max(minValue, maxTravelers - totalWithoutCurrent);
            const normalizedValue = Math.max(minValue, Math.min(nextValue, maxAllowed));

            input.value = String(normalizedValue);
            updateTotals();
        };

        serviceItems.forEach((item) => {
            const input = item.querySelector('.quantity__input');
            const minusButton = item.querySelector('.quantity__minus');
            const plusButton = item.querySelector('.quantity__plus');

            if (!input || !minusButton || !plusButton) {
                return;
            }

            minusButton.addEventListener('click', (event) => {
                event.preventDefault();
                const currentValue = Number.parseInt(input.value || '0', 10) || 0;
                setInputValue(input, currentValue - 1);
            });

            plusButton.addEventListener('click', (event) => {
                event.preventDefault();
                const currentValue = Number.parseInt(input.value || '0', 10) || 0;
                setInputValue(input, currentValue + 1);
            });

            input.addEventListener('input', () => {
                const currentValue = Number.parseInt(input.value || '0', 10);
                setInputValue(input, Number.isNaN(currentValue) ? 0 : currentValue);
            });
        });

        updateTotals();
    });
});
