document.addEventListener('DOMContentLoaded', function () {
    const appConfig = document.body?.dataset || {};
    const baseUrl = appConfig.baseUrl || window.BASE_URL || '/';
    const i18n = window.TOUR_DETAIL_I18N || {};
    const messages = {
        bookingProceedFailed: i18n.bookingProceedFailed || 'Could not continue booking.',
        bookingProceedFailedNow: i18n.bookingProceedFailedNow || 'Could not continue booking right now.',
        loginFailed: i18n.loginFailed || 'Login failed.',
        loginFailedNow: i18n.loginFailedNow || 'Could not sign in right now.',
        reviewFailed: i18n.reviewFailed || 'Could not submit your review.',
        reviewSent: i18n.reviewSent || 'Your review has been submitted.',
        reviewFailedNow: i18n.reviewFailedNow || 'Could not send the review right now.',
        enquiryFailed: i18n.enquiryFailed || 'Could not send enquiry right now.',
        enquirySent: i18n.enquirySent || 'Enquiry sent successfully.',
        selectDeparture: i18n.selectDeparture || 'Please choose a departure date.',
        travelersMax: i18n.travelersMax || 'Maximum {0} travelers',
        departureSlots: i18n.departureSlots || '{0} seats available for this departure.',
        currencySuffix: i18n.currencySuffix || 'đ',
    };

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

    const formatVnd = (value) => `${new Intl.NumberFormat('vi-VN').format(value)}${messages.currencySuffix}`;
    const formatTemplate = (template, value) => String(template || '').replace('{0}', String(value));

    const parseDepartureOptions = (selector) => {
        try {
            const parsed = JSON.parse(selector.dataset.departures || '[]');
            return Array.isArray(parsed) ? parsed.filter((item) => item && item.date) : [];
        } catch (error) {
            return [];
        }
    };

    const findDepartureByDate = (departures, date) =>
        departures.find((departure) => String(departure.date) === String(date)) || null;

    const applyDepartureToForm = (form, departure) => {
        if (!form || !departure) {
            return;
        }

        const setHidden = (selector, value) => {
            const input = form.querySelector(selector);
            if (input instanceof HTMLInputElement) {
                input.value = String(value ?? '');
            }
        };

        const adultPrice = Number.parseInt(departure.adult_price || '0', 10) || 0;
        const childPrice = Number.parseInt(departure.child_price || '0', 10) || 0;
        const infantPrice = Number.parseInt(departure.infant_price || '0', 10) || 0;
        const maxTravelers = Number.parseInt(departure.max_travelers || '0', 10) || 1;

        setHidden('[data-booking-departure-date-hidden]', departure.date);
        setHidden('[data-booking-departure-label-hidden]', departure.label);
        setHidden('[data-booking-price-hidden="adult"]', adultPrice);
        setHidden('[data-booking-price-hidden="child"]', childPrice);
        setHidden('[data-booking-price-hidden="infant"]', infantPrice);
        setHidden('[data-booking-max-travelers-hidden]', maxTravelers);

        const serviceArea = form.querySelector('.additional-service-area');
        if (serviceArea) {
            serviceArea.dispatchEvent(new CustomEvent('travelplus:departure-change', {
                detail: {
                    departure,
                    prices: { adult: adultPrice, child: childPrice, infant: infantPrice },
                    maxTravelers,
                },
            }));
        }
    };

    document.querySelectorAll('[data-departure-selector]').forEach((selector) => {
        const form = selector.closest('[data-booking-proceed-form]');
        const departures = parseDepartureOptions(selector);
        const toggle = selector.querySelector('[data-departure-toggle]');
        const menu = selector.querySelector('[data-departure-menu]');
        const meta = selector.querySelector('[data-departure-meta]');
        const currentLabel = selector.querySelector('[data-departure-current-label]');
        const currentPrice = selector.querySelector('[data-departure-current-price]');
        const buttons = Array.from(selector.querySelectorAll('[data-departure-option]'));

        if (!form || departures.length === 0 || !(toggle instanceof HTMLButtonElement) || !menu) {
            return;
        }

        const setMenuOpen = (isOpen) => {
            selector.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        const setActiveDeparture = (date) => {
            const departure = findDepartureByDate(departures, date);
            if (!departure) {
                return;
            }

            buttons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.departureDate === departure.date);
            });

            if (currentLabel) {
                currentLabel.textContent = departure.label || departure.date;
            }

            if (currentPrice) {
                currentPrice.textContent = formatVnd(Number.parseInt(departure.adult_price || '0', 10) || 0);
            }

            if (meta) {
                meta.textContent = formatTemplate(messages.departureSlots, departure.max_travelers || 1);
            }

            applyDepartureToForm(form, departure);
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setMenuOpen(!selector.classList.contains('is-open'));
        });

        selector.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                setActiveDeparture(button.dataset.departureDate || '');
                setMenuOpen(false);
            });
        });

        document.addEventListener('click', () => setMenuOpen(false));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        });

        setActiveDeparture(departures[0].date);
    });

    document.querySelectorAll('.additional-service-area').forEach((serviceArea) => {
        const serviceItems = Array.from(serviceArea.querySelectorAll('.booking-service-item'));
        if (serviceItems.length === 0) {
            return;
        }

        const bookingForm = serviceArea.closest('[data-booking-proceed-form]');
        let maxTravelers = Number.parseInt(serviceArea.dataset.maxTravelers || '15', 10);
        const totalElement = serviceArea.querySelector('.booking-grand-total');
        const maxTravelersLabel = serviceArea.querySelector('[data-booking-travelers-max-label]');

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
                const serviceType = item.dataset.serviceType || '';

                grandTotal += lineTotal;

                if (bookingForm && serviceType) {
                    const hiddenInput = bookingForm.querySelector(`[data-booking-quantity-hidden="${serviceType}"]`);
                    if (hiddenInput instanceof HTMLInputElement) {
                        hiddenInput.value = String(quantity);
                    }
                }
            });

            if (totalElement) {
                totalElement.textContent = formatVnd(grandTotal);
            }

            if (bookingForm) {
                const grandTotalInput = bookingForm.querySelector('[data-booking-grand-total-hidden]');
                if (grandTotalInput instanceof HTMLInputElement) {
                    grandTotalInput.value = String(grandTotal);
                }
            }
        };

        const updatePriceLabels = (prices) => {
            Object.entries(prices).forEach(([serviceType, price]) => {
                const item = serviceItems.find((serviceItem) => serviceItem.dataset.serviceType === serviceType);
                if (item) {
                    item.dataset.unitPrice = String(price);
                }

                const label = serviceArea.querySelector(`[data-booking-price-label="${serviceType}"]`);
                if (label) {
                    label.textContent = formatVnd(Number.parseInt(price || '0', 10) || 0);
                }
            });

            const unitPriceLabel = bookingForm?.querySelector('[data-booking-unit-price-label]');
            if (unitPriceLabel && prices.adult) {
                unitPriceLabel.textContent = formatVnd(Number.parseInt(prices.adult, 10) || 0);
            }
        };

        const clampQuantitiesToMax = () => {
            serviceItems.forEach((item) => {
                const input = item.querySelector('.quantity__input');
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const currentValue = Number.parseInt(input.value || '0', 10) || 0;
                setInputValue(input, currentValue);
            });
        };

        const updateMaxTravelersLabel = () => {
            if (maxTravelersLabel) {
                maxTravelersLabel.textContent = `(${formatTemplate(messages.travelersMax, maxTravelers)})`;
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

        serviceArea.addEventListener('travelplus:departure-change', (event) => {
            const detail = event.detail || {};
            const departure = detail.departure || {};
            const prices = detail.prices || {};
            maxTravelers = Number.parseInt(detail.maxTravelers || serviceArea.dataset.baseMaxTravelers || '15', 10) || 1;
            serviceArea.dataset.maxTravelers = String(maxTravelers);

            updatePriceLabels(prices);
            updateMaxTravelersLabel();

            const tourInfo = bookingForm?.querySelector('[data-booking-tour-info]');
            if (tourInfo) {
                const durationLabel = serviceArea.dataset.durationLabel || '';
                const prefix = serviceArea.dataset.departurePrefix || '';
                const departureLabel = departure.label || '';
                tourInfo.textContent = `${durationLabel}${departureLabel ? ` | ${prefix} ${departureLabel}` : ''}`;
            }

            clampQuantitiesToMax();
            updateTotals();
        });

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
        updateMaxTravelersLabel();
    });

    const bookingProceedForm = document.querySelector('[data-booking-proceed-form]');
    if (bookingProceedForm) {
        const errorBox = bookingProceedForm.querySelector('[data-booking-proceed-error]');

        bookingProceedForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (errorBox) {
                errorBox.className = 'alert alert-danger d-none mt-3';
                errorBox.textContent = '';
            }

            const departureInput = bookingProceedForm.querySelector('[name="departure_date"]');
            if (departureInput instanceof HTMLInputElement && departureInput.value.trim() === '') {
                if (errorBox) {
                    errorBox.className = 'alert alert-danger mt-3';
                    errorBox.textContent = messages.selectDeparture;
                }
                return;
            }

            const formData = new FormData(bookingProceedForm);

            try {
                const response = await fetch(bookingProceedForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    if (errorBox) {
                        errorBox.className = 'alert alert-danger mt-3';
                        errorBox.textContent = payload.message || messages.bookingProceedFailed;
                    }
                    return;
                }

                if (payload.redirect) {
                    window.location.href = payload.redirect;
                    return;
                }

                const bookingModal = document.getElementById('bookingModal');
                const proceedModal = document.getElementById('proceedBookingModal');
                if (bookingModal && proceedModal && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(bookingModal).hide();
                    bootstrap.Modal.getOrCreateInstance(proceedModal).show();
                }
            } catch (error) {
                if (errorBox) {
                    errorBox.className = 'alert alert-danger mt-3';
                    errorBox.textContent = messages.bookingProceedFailedNow;
                }
            }
        });
    }

    const bookingLoginForm = document.querySelector('[data-booking-login-form]');
    if (bookingLoginForm) {
        const loginErrorBox = bookingLoginForm.querySelector('[data-booking-login-error]');

        bookingLoginForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (loginErrorBox) {
                loginErrorBox.className = 'alert alert-danger d-none';
                loginErrorBox.textContent = '';
            }

            const formData = new FormData(bookingLoginForm);

            try {
                const response = await fetch(bookingLoginForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    if (loginErrorBox) {
                        loginErrorBox.className = 'alert alert-danger';
                        loginErrorBox.textContent = payload.message || messages.loginFailed;
                    }
                    return;
                }

                window.location.href = payload.redirect || `${baseUrl.replace(/\/$/, '')}/booking/checkout`;
            } catch (error) {
                if (loginErrorBox) {
                    loginErrorBox.className = 'alert alert-danger';
                    loginErrorBox.textContent = messages.loginFailedNow;
                }
            }
        });
    }

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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    const errors = Object.values(payload.errors || {});
                    if (errorBox) {
                        errorBox.className = 'col-md-12 alert alert-danger';
                        errorBox.innerHTML = errors.length > 0
                            ? errors.map((error) => `<div>${error}</div>`).join('')
                            : `<div>${payload.message || messages.reviewFailed}</div>`;
                    }
                    return;
                }

                if (messageBox) {
                    messageBox.className = 'col-md-12 alert alert-success';
                    messageBox.textContent = payload.message || messages.reviewSent;
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
                    errorBox.innerHTML = `<div>${messages.reviewFailedNow}</div>`;
                }
            }
        });
    }

    const enquiryForm = document.querySelector('[data-tour-enquiry-form]');
    if (enquiryForm) {
        const messageBox = enquiryForm.querySelector('[data-enquiry-message]');
        const errorBox = enquiryForm.querySelector('[data-enquiry-errors]');

        enquiryForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (messageBox) {
                messageBox.className = 'col-md-12 d-none';
                messageBox.textContent = '';
            }

            if (errorBox) {
                errorBox.className = 'col-md-12 d-none';
                errorBox.innerHTML = '';
            }

            const formData = new FormData(enquiryForm);

            try {
                const response = await fetch(enquiryForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    const errors = Object.values(payload.errors || {});
                    if (errorBox) {
                        errorBox.className = 'col-md-12 alert alert-danger';
                        errorBox.innerHTML = errors.length > 0
                            ? errors.map((error) => `<div>${error}</div>`).join('')
                            : `<div>${payload.message || messages.enquiryFailed}</div>`;
                    }
                    return;
                }

                if (messageBox) {
                    messageBox.className = 'col-md-12 alert alert-success';
                    messageBox.textContent = payload.message || messages.enquirySent;
                }

                enquiryForm.reset();

                setTimeout(() => {
                    const modalElement = document.getElementById('enquiryModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                    }
                }, 1600);
            } catch (error) {
                if (errorBox) {
                    errorBox.className = 'col-md-12 alert alert-danger';
                    errorBox.innerHTML = `<div>${messages.enquiryFailed}</div>`;
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

    document.querySelectorAll('.rating-container').forEach((container) => {
        const stars = container.querySelectorAll('.rating-star-btn');
        const input = container.querySelector('input');

        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', () => {
                stars.forEach((item, itemIndex) => {
                    item.classList.toggle('active', itemIndex <= index);
                });
            });

            star.addEventListener('click', () => {
                if (input) {
                    input.value = star.dataset.value;
                }

                stars.forEach((item, itemIndex) => {
                    item.classList.toggle('active', itemIndex <= index);
                });
            });
        });

        container.addEventListener('mouseleave', () => {
            const value = Number.parseInt(input?.value || '0', 10) || 0;
            stars.forEach((item, index) => {
                item.classList.toggle('active', index < value);
            });
        });
    });
});
