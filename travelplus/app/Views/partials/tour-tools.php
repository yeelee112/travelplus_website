<?php
$currentLocale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$copy = [
    'vi' => [
        'toggle' => 'Tour của bạn',
        'title' => 'Tour đã lưu',
        'subtitle' => 'Lưu tour yêu thích hoặc chọn tối đa 4 tour để so sánh nhanh.',
        'wishlist' => 'Đã lưu',
        'compare' => 'So sánh',
        'emptyWishlist' => 'Chưa có tour nào được lưu.',
        'emptyCompare' => 'Chọn tour để so sánh giá, lịch khởi hành và thời lượng.',
        'view' => 'Xem tour',
        'remove' => 'Bỏ',
        'clear' => 'Xóa danh sách',
        'price' => 'Giá',
        'duration' => 'Thời lượng',
        'departure' => 'Khởi hành',
        'destination' => 'Điểm đến',
        'type' => 'Loại tour',
        'departureFrom' => 'Bay/điểm đón',
        'travelers' => 'Số khách/chỗ',
        'room' => 'Phụ thu phòng đơn',
        'highlight' => 'Điểm nổi bật',
        'included' => 'Đã gồm',
        'saved' => 'Đã lưu tour.',
        'removed' => 'Đã bỏ khỏi danh sách.',
        'compareAdded' => 'Đã thêm vào so sánh.',
        'compareLimit' => 'Chỉ nên so sánh tối đa 4 tour cùng lúc.',
    ],
    'en' => [
        'toggle' => 'Your tours',
        'title' => 'Saved tours',
        'subtitle' => 'Save favorite tours or compare up to 4 tours quickly.',
        'wishlist' => 'Saved',
        'compare' => 'Compare',
        'emptyWishlist' => 'No saved tours yet.',
        'emptyCompare' => 'Choose tours to compare price, departure and duration.',
        'view' => 'View tour',
        'remove' => 'Remove',
        'clear' => 'Clear list',
        'price' => 'Price',
        'duration' => 'Duration',
        'departure' => 'Departure',
        'destination' => 'Destination',
        'type' => 'Tour type',
        'departureFrom' => 'From/meeting point',
        'travelers' => 'Guests/seats',
        'room' => 'Single room supplement',
        'highlight' => 'Highlight',
        'included' => 'Included',
        'saved' => 'Tour saved.',
        'removed' => 'Removed from list.',
        'compareAdded' => 'Added to comparison.',
        'compareLimit' => 'Compare up to 4 tours at once.',
    ],
][$currentLocale];
?>

<div class="tp-tour-tools" data-tour-tools hidden>
    <button type="button" class="tp-tour-tools__toggle" data-tour-tools-toggle aria-expanded="false" aria-controls="tp-tour-tools-panel">
        <span class="tp-tour-tools__toggle-icon"><i class="bi bi-suitcase2" aria-hidden="true"></i></span>
        <span class="tp-tour-tools__toggle-copy">
            <strong><?= esc($copy['toggle']) ?></strong>
            <small><span data-tour-tools-total>0</span> tour</small>
        </span>
    </button>

    <section class="tp-tour-tools__panel" id="tp-tour-tools-panel" data-tour-tools-panel hidden>
        <header class="tp-tour-tools__header">
            <div>
                <h3><?= esc($copy['title']) ?></h3>
                <p><?= esc($copy['subtitle']) ?></p>
            </div>
            <button type="button" class="tp-tour-tools__close" data-tour-tools-close aria-label="Close">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>

        <div class="tp-tour-tools__tabs" role="tablist" aria-label="<?= esc($copy['title'], 'attr') ?>">
            <button type="button" data-tour-tools-tab="wishlist" aria-selected="true">
                <?= esc($copy['wishlist']) ?> <span data-tour-tools-count="wishlist">0</span>
            </button>
            <button type="button" data-tour-tools-tab="compare" aria-selected="false">
                <?= esc($copy['compare']) ?> <span data-tour-tools-count="compare">0</span>
            </button>
        </div>

        <div class="tp-tour-tools__body">
            <div data-tour-tools-view="wishlist"></div>
            <div data-tour-tools-view="compare" hidden></div>
        </div>
    </section>

    <div class="tp-tour-tools__status" data-tour-tools-status role="status" aria-live="polite" hidden></div>

    <script type="application/json" data-tour-tools-i18n><?= json_encode($copy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</div>

<script>
(function () {
    const root = document.querySelector('[data-tour-tools]');

    if (!root) {
        return;
    }

    const storageKey = 'travelplus_tour_tools_v1';
    const maxCompare = 4;
    const i18nNode = root.querySelector('[data-tour-tools-i18n]');
    const labels = i18nNode ? JSON.parse(i18nNode.textContent || '{}') : {};
    const panel = root.querySelector('[data-tour-tools-panel]');
    const toggle = root.querySelector('[data-tour-tools-toggle]');
    const totalNode = root.querySelector('[data-tour-tools-total]');
    const statusNode = root.querySelector('[data-tour-tools-status]');
    const views = {
        wishlist: root.querySelector('[data-tour-tools-view="wishlist"]'),
        compare: root.querySelector('[data-tour-tools-view="compare"]')
    };
    const isTourPage = !!document.querySelector('.package-details-page');
    let activeTab = 'wishlist';
    let statusTimer = 0;
    let state = readState();

    if (isTourPage) {
        root.classList.add('is-tour-page');
    }

    function readState() {
        try {
            const parsed = JSON.parse(window.localStorage.getItem(storageKey) || '{}');
            return {
                wishlist: Array.isArray(parsed.wishlist) ? parsed.wishlist : [],
                compare: Array.isArray(parsed.compare) ? parsed.compare : []
            };
        } catch (error) {
            return {wishlist: [], compare: []};
        }
    }

    function writeState() {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(state));
        } catch (error) {
            return;
        }
    }

    function clean(value, fallback) {
        const text = String(value || '').replace(/\s+/g, ' ').trim();
        return (text || fallback || '').slice(0, 280);
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
        });
    }

    function getItem(button) {
        const source = button.closest('[data-tour-tools-source]');

        if (!source) {
            return null;
        }

        return {
            id: clean(source.dataset.tourId, source.dataset.tourUrl || source.dataset.tourTitle),
            title: clean(source.dataset.tourTitle, 'Travel Plus tour'),
            url: clean(source.dataset.tourUrl, '#'),
            image: clean(source.dataset.tourImage, ''),
            price: clean(source.dataset.tourPrice, '-'),
            duration: clean(source.dataset.tourDuration, '-'),
            departure: clean(source.dataset.tourDeparture, '-'),
            destination: clean(source.dataset.tourDestination, '-'),
            type: clean(source.dataset.tourType, '-'),
            departureFrom: clean(source.dataset.tourDepartureFrom, '-'),
            travelers: clean(source.dataset.tourTravelers, '-'),
            room: clean(source.dataset.tourRoom, '-'),
            highlight: clean(source.dataset.tourHighlight, '-'),
            included: clean(source.dataset.tourIncluded, '-')
        };
    }

    function hasItem(list, id) {
        if (!Array.isArray(state[list])) {
            return false;
        }

        return state[list].some(function (item) {
            return item.id === id;
        });
    }

    function removeItem(list, id) {
        if (!Array.isArray(state[list])) {
            return;
        }

        state[list] = state[list].filter(function (item) {
            return item.id !== id;
        });
    }

    function addItem(list, item) {
        removeItem(list, item.id);
        state[list].unshift(item);
        state[list] = state[list].slice(0, list === 'compare' ? maxCompare : 20);
    }

    function setStatus(message) {
        if (!statusNode || !message) {
            return;
        }

        window.clearTimeout(statusTimer);
        statusNode.textContent = message;
        statusNode.hidden = false;
        statusTimer = window.setTimeout(function () {
            statusNode.hidden = true;
        }, 2400);
    }

    function toggleItem(action, item) {
        if (hasItem(action, item.id)) {
            removeItem(action, item.id);
            setStatus(labels.removed);
        } else if (action === 'compare' && state.compare.length >= maxCompare) {
            setStatus(labels.compareLimit);
            return;
        } else {
            addItem(action, item);
            setStatus(action === 'compare' ? labels.compareAdded : labels.saved);
        }

        writeState();
        render();
    }

    function itemCard(item, list) {
        const image = item.image
            ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title) + '" loading="lazy" decoding="async">'
            : '<span class="tp-tour-tools__item-placeholder"><i class="bi bi-suitcase2" aria-hidden="true"></i></span>';

        return '<article class="tp-tour-tools__item">'
            + '<a class="tp-tour-tools__item-media" href="' + escapeHtml(item.url) + '">' + image + '</a>'
            + '<div class="tp-tour-tools__item-copy">'
            + '<h4><a href="' + escapeHtml(item.url) + '">' + escapeHtml(item.title) + '</a></h4>'
            + '<p>' + escapeHtml(item.destination) + ' - ' + escapeHtml(item.duration) + '</p>'
            + '<strong>' + escapeHtml(item.price) + '</strong>'
            + '<div class="tp-tour-tools__item-actions">'
            + '<a href="' + escapeHtml(item.url) + '">' + escapeHtml(labels.view) + '</a>'
            + '<button type="button" data-tour-tools-remove="' + escapeHtml(list) + '" data-tour-id="' + escapeHtml(item.id) + '">' + escapeHtml(labels.remove) + '</button>'
            + '</div>'
            + '</div>'
            + '</article>';
    }

    function renderWishlist() {
        if (!views.wishlist) {
            return;
        }

        if (state.wishlist.length === 0) {
            views.wishlist.innerHTML = '<div class="tp-tour-tools__empty">' + escapeHtml(labels.emptyWishlist) + '</div>';
            return;
        }

        views.wishlist.innerHTML = '<div class="tp-tour-tools__list">'
            + state.wishlist.map(function (item) { return itemCard(item, 'wishlist'); }).join('')
            + '</div><button type="button" class="tp-tour-tools__clear" data-tour-tools-clear="wishlist">' + escapeHtml(labels.clear) + '</button>';
    }

    function renderCompare() {
        if (!views.compare) {
            return;
        }

        if (state.compare.length === 0) {
            views.compare.innerHTML = '<div class="tp-tour-tools__empty">' + escapeHtml(labels.emptyCompare) + '</div>';
            return;
        }

        const rows = [
            ['type', labels.type],
            ['price', labels.price],
            ['duration', labels.duration],
            ['departure', labels.departure],
            ['departureFrom', labels.departureFrom],
            ['destination', labels.destination],
            ['travelers', labels.travelers],
            ['room', labels.room],
            ['highlight', labels.highlight],
            ['included', labels.included]
        ];
        const head = '<tr><th></th>' + state.compare.map(function (item) {
            return '<th><a href="' + escapeHtml(item.url) + '">' + escapeHtml(item.title) + '</a></th>';
        }).join('') + '</tr>';
        const body = rows.map(function (row) {
            return '<tr><th>' + escapeHtml(row[1]) + '</th>' + state.compare.map(function (item) {
                return '<td>' + escapeHtml(item[row[0]] || '-') + '</td>';
            }).join('') + '</tr>';
        }).join('');
        const removeRow = '<tr><th></th>' + state.compare.map(function (item) {
            return '<td><button type="button" data-tour-tools-remove="compare" data-tour-id="' + escapeHtml(item.id) + '">' + escapeHtml(labels.remove) + '</button></td>';
        }).join('') + '</tr>';

        views.compare.innerHTML = '<div class="tp-tour-tools__compare-table"><table><thead>' + head + '</thead><tbody>' + body + removeRow + '</tbody></table></div>'
            + '<button type="button" class="tp-tour-tools__clear" data-tour-tools-clear="compare">' + escapeHtml(labels.clear) + '</button>';
    }

    function updateButtons() {
        document.querySelectorAll('[data-tour-action]').forEach(function (button) {
            const item = getItem(button);
            const action = button.dataset.tourAction;
            const active = item && (action === 'wishlist' || action === 'compare') ? hasItem(action, item.id) : false;
            const text = button.querySelector('[data-tour-action-text]');
            const icon = button.querySelector('i');

            button.classList.toggle('is-active', Boolean(active));
            button.setAttribute('aria-pressed', active ? 'true' : 'false');

            if (text) {
                text.textContent = active ? (button.dataset.labelRemove || '') : (button.dataset.labelAdd || '');
            }

            if (icon) {
                icon.className = action === 'wishlist'
                    ? (active ? 'bi bi-heart-fill' : 'bi bi-heart')
                    : (active ? 'bi bi-bar-chart-fill' : 'bi bi-bar-chart');
            }
        });
    }

    function setTab(tab) {
        activeTab = tab === 'compare' ? 'compare' : 'wishlist';
        root.querySelectorAll('[data-tour-tools-tab]').forEach(function (button) {
            const selected = button.dataset.tourToolsTab === activeTab;
            button.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
        Object.keys(views).forEach(function (key) {
            if (views[key]) {
                views[key].hidden = key !== activeTab;
            }
        });
    }

    function render() {
        const total = state.wishlist.length + state.compare.length;
        root.hidden = total === 0;
        if (total === 0 && panel) {
            panel.hidden = true;
            root.classList.remove('is-open');
            toggle?.setAttribute('aria-expanded', 'false');
        }

        if (totalNode) {
            totalNode.textContent = String(total);
        }

        root.querySelectorAll('[data-tour-tools-count]').forEach(function (node) {
            const key = node.dataset.tourToolsCount;
            node.textContent = String(state[key]?.length || 0);
        });

        renderWishlist();
        renderCompare();
        setTab(activeTab);
        updateButtons();
    }

    function setOpen(open) {
        if (root.hidden && open) {
            return;
        }

        root.classList.toggle('is-open', Boolean(open));
        if (panel) {
            panel.hidden = !open;
        }
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    document.addEventListener('click', function (event) {
        const actionButton = event.target.closest('[data-tour-action]');
        const removeButton = event.target.closest('[data-tour-tools-remove]');
        const clearButton = event.target.closest('[data-tour-tools-clear]');
        const tabButton = event.target.closest('[data-tour-tools-tab]');

        if (actionButton) {
            const action = actionButton.dataset.tourAction;
            const item = getItem(actionButton);
            if (item && (action === 'wishlist' || action === 'compare')) {
                toggleItem(action, item);
                if (action === 'compare') {
                    setTab('compare');
                    setOpen(true);
                }
            }
            return;
        }

        if (removeButton) {
            removeItem(removeButton.dataset.tourToolsRemove, removeButton.dataset.tourId);
            writeState();
            render();
            setStatus(labels.removed);
            return;
        }

        if (clearButton) {
            state[clearButton.dataset.tourToolsClear] = [];
            writeState();
            render();
            setStatus(labels.removed);
            return;
        }

        if (tabButton) {
            setTab(tabButton.dataset.tourToolsTab);
        }
    });

    toggle?.addEventListener('click', function () {
        setOpen(!root.classList.contains('is-open'));
    });

    root.querySelector('[data-tour-tools-close]')?.addEventListener('click', function () {
        setOpen(false);
    });

    render();
})();
</script>
