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
