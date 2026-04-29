document.addEventListener('DOMContentLoaded', function () {


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

    const reviewForm = document.querySelector('[data-tour-review-form]');

    if (reviewForm) {
        const messageBox = reviewForm.querySelector('[data-review-message]');
        const errorBox = reviewForm.querySelector('[data-review-errors]');
        const ratingContainers = Array.from(reviewForm.querySelectorAll('[data-rating-input]'));

        const renderRating = (container, value) => {
            const stars = Array.from(container.querySelectorAll('.rating-star-btn'));
            stars.forEach((button, index) => {
                const icon = button.querySelector('.star-icon');
                const active = index < value;

                button.classList.toggle('is-active', active);
                if (icon) {
                    icon.classList.toggle('bi-star-fill', active);
                    icon.classList.toggle('bi-star', !active);
                }
            });
        };

        ratingContainers.forEach((container) => {
            const input = container.querySelector('input[type="hidden"]');
            const stars = Array.from(container.querySelectorAll('.rating-star-btn'));

            renderRating(container, Number.parseInt(input?.value || '0', 10) || 0);

            stars.forEach((button) => {
                button.addEventListener('click', () => {
                    const value = Number.parseInt(button.dataset.value || '0', 10) || 0;
                    if (input) {
                        input.value = String(value);
                    }
                    renderRating(container, value);
                });
            });
        });

        reviewForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (messageBox) {
                messageBox.className = 'col-md-12 d-none';
                messageBox.textContent = '';
            }

            if (errorBox) {
                errorBox.className = 'col-md-12 d-none';
                errorBox.innerHTML = '';
            }

            const formData = new FormData(reviewForm);

            try {
                const response = await fetch(reviewForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    const errors = Object.values(payload.errors || {});
                    if (errorBox) {
                        errorBox.className = 'col-md-12 alert alert-danger';
                        errorBox.innerHTML = errors.length > 0
                            ? errors.map((error) => `<div>${error}</div>`).join('')
                            : `<div>${payload.message || 'Review submit failed.'}</div>`;
                    }
                    return;
                }

                if (messageBox) {
                    messageBox.className = 'col-md-12 alert alert-success';
                    messageBox.textContent = payload.message || 'Review submitted.';
                }

                reviewForm.reset();
                ratingContainers.forEach((container) => renderRating(container, 0));

                setTimeout(() => {
                    const modalElement = document.getElementById('ratingModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                    }
                }, 1200);
            } catch (error) {
                if (errorBox) {
                    errorBox.className = 'col-md-12 alert alert-danger';
                    errorBox.innerHTML = '<div>Could not submit review right now.</div>';
                }
            }
        });
    }

    const reviewPagination = document.querySelector('[data-review-pagination]');

    if (reviewPagination) {
        const reviewPages = Array.from(document.querySelectorAll('[data-review-page]'));
        const pageItems = Array.from(reviewPagination.querySelectorAll('.page-item'));
        const pageLinks = Array.from(reviewPagination.querySelectorAll('[data-review-page-trigger]'));

        const setActiveReviewPage = (pageNumber) => {
            reviewPages.forEach((page) => {
                page.classList.toggle('d-none', page.dataset.reviewPage !== String(pageNumber));
            });

            pageItems.forEach((item) => {
                const link = item.querySelector('[data-review-page-trigger]');
                item.classList.toggle('active', (link?.dataset.reviewPageTrigger || '') === String(pageNumber));
            });
        };

        pageLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                setActiveReviewPage(link.dataset.reviewPageTrigger || '1');
            });
        });
    }


    document.querySelectorAll('.rating-container').forEach(container => {
    const stars = container.querySelectorAll('.rating-star-btn');
    const input = container.querySelector('input');

    stars.forEach((star, index) => {

        // hover
        star.addEventListener('mouseenter', () => {
            stars.forEach((s, i) => {
                s.classList.toggle('active', i <= index);
            });
        });

        // click (chọn)
        star.addEventListener('click', () => {
            input.value = star.dataset.value;

            stars.forEach((s, i) => {
                s.classList.toggle('active', i <= index);
            });
        });
    });

    // rời chuột → trả về trạng thái đã chọn
    container.addEventListener('mouseleave', () => {
        const value = parseInt(input.value) || 0;

        stars.forEach((s, i) => {
            s.classList.toggle('active', i < value);
        });
    });
});
});
