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

    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            bootstrap.Tooltip.getOrCreateInstance(element);
        });
    }

    const anchorNavScroller = document.querySelector('.tour-detail-anchor-nav__scroller');
    if (anchorNavScroller) {
        const anchorLinks = Array.from(anchorNavScroller.querySelectorAll('a[href^="#"]'));
        const anchorTargets = anchorLinks
            .map((link) => {
                const id = decodeURIComponent(link.getAttribute('href') || '').slice(1);
                const section = id ? document.getElementById(id) : null;

                return section ? { id, link, section } : null;
            })
            .filter(Boolean);

        let activeAnchorId = '';

        const centerActiveAnchor = (link) => {
            if (!window.matchMedia('(max-width: 767px)').matches) {
                return;
            }

            const scrollerRect = anchorNavScroller.getBoundingClientRect();
            const linkRect = link.getBoundingClientRect();
            const targetLeft = anchorNavScroller.scrollLeft
                + linkRect.left
                - scrollerRect.left
                - ((scrollerRect.width - linkRect.width) / 2);

            anchorNavScroller.scrollTo({
                left: Math.max(0, targetLeft),
                behavior: 'smooth',
            });
        };

        const setActiveAnchor = (id) => {
            if (!id || id === activeAnchorId) {
                return;
            }

            activeAnchorId = id;
            anchorTargets.forEach(({ id: targetId, link }) => {
                const isActive = targetId === id;
                link.classList.toggle('is-active', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'true');
                    centerActiveAnchor(link);
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        const updateActiveAnchorFromScroll = () => {
            const navHeight = document.querySelector('.tour-detail-anchor-nav')?.offsetHeight || 0;
            const marker = navHeight + Math.round(window.innerHeight * 0.28);
            let current = anchorTargets[0]?.id || '';

            anchorTargets.forEach(({ id, section }) => {
                if (section.getBoundingClientRect().top <= marker) {
                    current = id;
                }
            });

            setActiveAnchor(current);
        };

        let scrollTicking = false;
        const requestAnchorUpdate = () => {
            if (scrollTicking) {
                return;
            }

            scrollTicking = true;
            window.requestAnimationFrame(() => {
                updateActiveAnchorFromScroll();
                scrollTicking = false;
            });
        };

        anchorLinks.forEach((link) => {
            link.addEventListener('click', () => {
                const id = decodeURIComponent(link.getAttribute('href') || '').slice(1);
                setActiveAnchor(id);
            });
        });

        window.addEventListener('scroll', requestAnchorUpdate, { passive: true });
        window.addEventListener('resize', requestAnchorUpdate);
        updateActiveAnchorFromScroll();
    }

    document.querySelectorAll('[data-listing-date-picker]').forEach((datePicker) => {
        const input = datePicker.querySelector('[data-listing-date-input]');
        const trigger = datePicker.querySelector('[data-listing-date-trigger]');
        const display = datePicker.querySelector('[data-listing-date-display]');
        const panel = datePicker.querySelector('[data-listing-date-panel]');
        const monthEl = datePicker.querySelector('[data-listing-date-month]');
        const prevBtn = datePicker.querySelector('[data-listing-date-prev]');
        const nextBtn = datePicker.querySelector('[data-listing-date-next]');
        const weekdaysEl = datePicker.querySelector('[data-listing-date-weekdays]');
        const daysEl = datePicker.querySelector('[data-listing-date-days]');

        if (!(input instanceof HTMLInputElement) || !(trigger instanceof HTMLButtonElement) || !display || !panel || !monthEl || !prevBtn || !nextBtn || !weekdaysEl || !daysEl) {
            return;
        }

        const locale = datePicker.dataset.locale === 'en' ? 'en' : 'vi';
        const valueFormat = datePicker.dataset.valueFormat || 'iso';
        const today = new Date();
        const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        let selectedDate = null;
        let viewDate = new Date(todayDate.getFullYear(), todayDate.getMonth(), 1);

        const weekdayNames = locale === 'en'
            ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            : ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
        const monthFormatter = new Intl.DateTimeFormat(locale === 'en' ? 'en-US' : 'vi-VN', {
            month: 'long',
            year: 'numeric',
        });
        const dateFormatter = new Intl.DateTimeFormat(locale === 'en' ? 'en-US' : 'vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });

        const formatIsoDate = (date) => {
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${date.getFullYear()}-${month}-${day}`;
        };

        const formatDisplayDate = (date) => dateFormatter.format(date);

        const isSameDate = (dateA, dateB) => (
            dateA instanceof Date
            && dateB instanceof Date
            && dateA.getFullYear() === dateB.getFullYear()
            && dateA.getMonth() === dateB.getMonth()
            && dateA.getDate() === dateB.getDate()
        );

        const closeDatePicker = () => {
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        };

        const updateSummary = () => {
            input.value = selectedDate
                ? (valueFormat === 'display' ? formatDisplayDate(selectedDate) : formatIsoDate(selectedDate))
                : '';

            if (selectedDate) {
                display.textContent = formatDisplayDate(selectedDate);
                trigger.classList.add('is-selected');
            } else {
                display.textContent = display.dataset.emptyLabel || display.textContent || 'dd/mm/yyyy';
                trigger.classList.remove('is-selected');
            }
        };

        const renderDatePicker = () => {
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const monthStartOffset = (firstDay.getDay() + 6) % 7;
            const lastDay = new Date(year, month + 1, 0).getDate();
            const currentMonthStart = new Date(year, month, 1);
            const todayMonthStart = new Date(todayDate.getFullYear(), todayDate.getMonth(), 1);

            monthEl.textContent = monthFormatter.format(new Date(year, month, 1));
            prevBtn.disabled = currentMonthStart <= todayMonthStart;
            daysEl.innerHTML = '';

            for (let index = 0; index < monthStartOffset; index += 1) {
                const blankDay = document.createElement('span');
                blankDay.className = 'home-search-date__day home-search-date__day--blank';
                blankDay.setAttribute('aria-hidden', 'true');
                daysEl.appendChild(blankDay);
            }

            for (let day = 1; day <= lastDay; day += 1) {
                const date = new Date(year, month, day);
                const dateButton = document.createElement('button');

                dateButton.type = 'button';
                dateButton.className = 'home-search-date__day';
                dateButton.textContent = String(day);
                dateButton.disabled = date < todayDate;
                dateButton.setAttribute('aria-label', formatDisplayDate(date));

                if (isSameDate(date, selectedDate)) {
                    dateButton.classList.add('is-selected');
                    dateButton.setAttribute('aria-current', 'date');
                }

                dateButton.addEventListener('click', () => {
                    selectedDate = date;
                    updateSummary();
                    renderDatePicker();
                    closeDatePicker();
                });

                daysEl.appendChild(dateButton);
            }
        };

        if (weekdaysEl.children.length === 0) {
            weekdayNames.forEach((weekday) => {
                const weekdayEl = document.createElement('span');
                weekdayEl.textContent = weekday;
                weekdaysEl.appendChild(weekdayEl);
            });
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            document.querySelectorAll('[data-listing-date-panel]').forEach((otherPanel) => {
                if (otherPanel !== panel) {
                    otherPanel.hidden = true;
                }
            });

            if (panel.hidden) {
                renderDatePicker();
                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                closeDatePicker();
            }
        });

        prevBtn.addEventListener('click', () => {
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
            renderDatePicker();
        });

        nextBtn.addEventListener('click', () => {
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
            renderDatePicker();
        });

        datePicker.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', closeDatePicker);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDatePicker();
            }
        });

        updateSummary();
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
        const singleRoomToggle = bookingForm?.querySelector('[data-booking-single-room-toggle]');
        const singleRoomHidden = bookingForm?.querySelector('[data-booking-single-room-hidden]');
        const singleRoomSupplement = Number.parseInt(String(bookingForm?.dataset.singleRoomSupplement || '0'), 10) || 0;

        const getTotalTravelers = () =>
            serviceItems.reduce((sum, item) => {
                const input = item.querySelector('.quantity__input');
                const value = Number.parseInt(input?.value || '0', 10);
                return sum + (Number.isNaN(value) ? 0 : value);
            }, 0);

        const getQuantityByType = (serviceType) => {
            const item = serviceItems.find((serviceItem) => serviceItem.dataset.serviceType === serviceType);
            const input = item?.querySelector('.quantity__input');

            return Number.parseInt(input?.value || '0', 10) || 0;
        };

        const getInputByType = (serviceType) =>
            serviceItems.find((serviceItem) => serviceItem.dataset.serviceType === serviceType)?.querySelector('.quantity__input') || null;

        const clampTravelerMix = () => {
            const adultInput = getInputByType('adult');
            const childInput = getInputByType('child');
            const infantInput = getInputByType('infant');

            if (!(adultInput instanceof HTMLInputElement) || !(childInput instanceof HTMLInputElement) || !(infantInput instanceof HTMLInputElement)) {
                return;
            }

            const adultQty = Math.max(1, Number.parseInt(adultInput.value || '1', 10) || 1);
            let childQty = Math.max(0, Number.parseInt(childInput.value || '0', 10) || 0);
            let infantQty = Math.max(0, Number.parseInt(infantInput.value || '0', 10) || 0);

            const maxDependentTravelers = adultQty;
            if ((childQty + infantQty) > maxDependentTravelers) {
                infantQty = Math.min(infantQty, maxDependentTravelers);
                childQty = Math.max(0, maxDependentTravelers - infantQty);
            }

            adultInput.value = String(adultQty);
            childInput.value = String(childQty);
            infantInput.value = String(infantQty);
        };

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

            if (singleRoomHidden instanceof HTMLInputElement) {
                singleRoomHidden.value = singleRoomToggle instanceof HTMLInputElement && singleRoomToggle.checked ? '1' : '0';
            }

            if (singleRoomToggle instanceof HTMLInputElement && singleRoomToggle.checked) {
                grandTotal += singleRoomSupplement;
            }

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

            clampTravelerMix();
            updateTotals();
        };

        const updateMaxTravelersLabel = () => {
            if (maxTravelersLabel) {
                maxTravelersLabel.textContent = `(${formatTemplate(messages.travelersMax, maxTravelers)})`;
            }
        };

        const setInputValue = (input, nextValue) => {
            const serviceItem = input.closest('.booking-service-item');
            const serviceType = serviceItem?.dataset.serviceType || '';
            const minValue = Number.parseInt(input.dataset.min || '0', 10) || 0;
            const currentValue = Number.parseInt(input.value || '0', 10) || 0;
            const totalWithoutCurrent = getTotalTravelers() - currentValue;
            let maxAllowed = Math.max(minValue, maxTravelers - totalWithoutCurrent);
            const adultQty = getQuantityByType('adult');
            const childQty = getQuantityByType('child');
            const infantQty = getQuantityByType('infant');

            if (serviceType === 'child') {
                maxAllowed = Math.min(maxAllowed, Math.max(0, adultQty - infantQty));
            }

            if (serviceType === 'infant') {
                maxAllowed = Math.min(maxAllowed, Math.max(0, adultQty - childQty));
            }

            const normalizedValue = Math.max(minValue, Math.min(nextValue, maxAllowed));

            input.value = String(normalizedValue);

            if (serviceType === 'adult') {
                clampTravelerMix();
            }

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

        if (singleRoomToggle instanceof HTMLInputElement) {
            singleRoomToggle.addEventListener('change', () => {
                if (singleRoomHidden instanceof HTMLInputElement) {
                    singleRoomHidden.value = singleRoomToggle.checked ? '1' : '0';
                }
                updateTotals();
            });
        }
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

                if (payload.analytics_event && typeof window.travelplusTrackEvent === 'function') {
                    window.travelplusTrackEvent(
                        payload.analytics_event.name,
                        payload.analytics_event.params || {}
                    );
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
