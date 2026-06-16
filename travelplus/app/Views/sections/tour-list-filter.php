<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$searchState = array_merge([
    'q' => (string) (service('request')->getGet('q') ?? ''),
    'departure_from' => (string) (service('request')->getGet('departure_from') ?? ''),
    'departure_to' => (string) (service('request')->getGet('departure_to') ?? ''),
    'departure_date' => (string) (service('request')->getGet('departure_date') ?? ''),
    'tour_type' => (string) (service('request')->getGet('tour_type') ?? ''),
    'promotion_only' => (string) (service('request')->getGet('promotion') ?? '') === '1',
], is_array($listingSearch ?? null) ? $listingSearch : []);

if (trim((string) $searchState['departure_date']) !== '') {
    $searchState['departure_from'] = $searchState['departure_from'] !== '' ? $searchState['departure_from'] : $searchState['departure_date'];
    $searchState['departure_to'] = $searchState['departure_to'] !== '' ? $searchState['departure_to'] : $searchState['departure_date'];
}

$searchState['tour_type'] = in_array($searchState['tour_type'], ['outbound', 'inbound'], true) ? $searchState['tour_type'] : '';

$parseDateValue = static function (string $value): ?\DateTime {
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    foreach (['Y-m-d', 'd/m/Y'] as $dateFormat) {
        $parsedDate = \DateTime::createFromFormat($dateFormat, $value);
        if ($parsedDate instanceof \DateTime) {
            return $parsedDate;
        }
    }

    return null;
};

$departureFromDate = $parseDateValue((string) $searchState['departure_from']);
$departureToDate = $parseDateValue((string) $searchState['departure_to']);
$departureFromSubmitValue = $departureFromDate instanceof \DateTime ? $departureFromDate->format('Y-m-d') : '';
$departureToSubmitValue = $departureToDate instanceof \DateTime ? $departureToDate->format('Y-m-d') : '';

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Find tours',
        'title' => 'Where would you like to go?',
        'destination' => 'Destination or keyword',
        'destinationPlaceholder' => 'Japan, Europe, Da Nang...',
        'date' => 'Departure window',
        'dateEmpty' => 'Choose a travel window',
        'dateFrom' => 'From date',
        'dateTo' => 'To date',
        'dateUnset' => 'Not selected',
        'dateHint' => 'Choose a rough travel window instead of one exact departure date.',
        'dateClear' => 'Clear',
        'allTypes' => 'All tours',
        'outbound' => 'Outbound tours',
        'inbound' => 'Domestic tours',
        'submit' => 'Search tours',
        'clear' => 'Clear',
        'mobileToggle' => 'Search',
    ]
    : [
        'eyebrow' => 'Tìm tour',
        'title' => 'Bạn muốn đi đâu?',
        'destination' => 'Điểm đến hoặc từ khóa',
        'destinationPlaceholder' => 'Nhật Bản, Châu Âu, Đà Nẵng...',
        'date' => 'Khoảng ngày khởi hành',
        'dateEmpty' => 'Chọn khoảng ngày đi',
        'dateFrom' => 'Từ ngày',
        'dateTo' => 'Đến ngày',
        'dateUnset' => 'Chưa chọn',
        'dateHint' => 'Chọn khoảng thời gian dự kiến thay vì phải biết chính xác ngày tour khởi hành.',
        'dateClear' => 'Xóa',
        'allTypes' => 'Tất cả tour',
        'outbound' => 'Tour nước ngoài',
        'inbound' => 'Tour trong nước',
        'submit' => 'Tìm tour',
        'clear' => 'Xóa lọc',
        'mobileToggle' => 'Tìm tour',
    ];

$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$clearUrl = $searchState['promotion_only'] ? ($searchUrl . '?promotion=1') : $searchUrl;
?>

<section class="tour-list-filter" aria-labelledby="tour-list-filter-title">
    <button class="tour-list-filter__mobile-toggle" type="button" data-tour-filter-toggle aria-expanded="false" aria-controls="tour-list-filter-panel">
        <i class="bi bi-search"></i>
        <?= esc($copy['mobileToggle']) ?>
    </button>

    <div class="container">
        <div class="tour-list-filter__panel" id="tour-list-filter-panel" data-tour-filter-panel>
            <div class="tour-list-filter__copy">
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="tour-list-filter-title"><?= esc($copy['title']) ?></h2>
            </div>

            <form class="tour-list-filter__form" action="<?= esc($searchUrl, 'attr') ?>" method="get">
                <?php if (! empty($searchState['promotion_only'])): ?>
                    <input type="hidden" name="promotion" value="1">
                <?php endif; ?>

                <label class="tour-list-filter__field">
                    <span><?= esc($copy['destination']) ?></span>
                    <input type="search" name="q" value="<?= esc($searchState['q'], 'attr') ?>" placeholder="<?= esc($copy['destinationPlaceholder'], 'attr') ?>" autocomplete="off">
                </label>

                <label class="tour-list-filter__field tour-list-filter__field--date">
                    <span><?= esc($copy['date']) ?></span>
                    <div class="home-search-date tour-list-filter__date-picker" data-date-range-picker data-locale="<?= esc($locale, 'attr') ?>">
                        <input type="hidden" name="departure_from" value="<?= esc($departureFromSubmitValue, 'attr') ?>" data-date-range-input-start>
                        <input type="hidden" name="departure_to" value="<?= esc($departureToSubmitValue, 'attr') ?>" data-date-range-input-end>
                        <button
                            type="button"
                            class="home-search-date__trigger<?= $departureFromSubmitValue !== '' || $departureToSubmitValue !== '' ? ' is-selected' : '' ?>"
                            data-date-range-trigger
                            data-empty-label="<?= esc($copy['dateEmpty'], 'attr') ?>"
                            data-start-empty-label="<?= esc($copy['dateUnset'], 'attr') ?>"
                            data-end-empty-label="<?= esc($copy['dateUnset'], 'attr') ?>"
                            aria-expanded="false"
                            aria-haspopup="dialog">
                            <span class="home-search-date__value" data-date-range-display><?= esc($copy['dateEmpty']) ?></span>
                        </button>
                        <div class="home-search-date__panel" data-date-range-panel hidden>
                            <div class="home-search-date__calendar" role="dialog" aria-label="<?= esc($copy['date'], 'attr') ?>">
                                <div class="home-search-date__selection">
                                    <div class="home-search-date__selection-item">
                                        <span><?= esc($copy['dateFrom']) ?></span>
                                        <strong data-date-range-preview-start><?= esc($copy['dateUnset']) ?></strong>
                                    </div>
                                    <div class="home-search-date__selection-item">
                                        <span><?= esc($copy['dateTo']) ?></span>
                                        <strong data-date-range-preview-end><?= esc($copy['dateUnset']) ?></strong>
                                    </div>
                                </div>
                                <div class="home-search-date__calendar-head">
                                    <button type="button" class="home-search-date__nav" data-date-range-prev aria-label="Previous month">&lsaquo;</button>
                                    <strong class="home-search-date__month" data-date-range-month></strong>
                                    <button type="button" class="home-search-date__nav" data-date-range-next aria-label="Next month">&rsaquo;</button>
                                </div>
                                <div class="home-search-date__weekdays" data-date-range-weekdays aria-hidden="true"></div>
                                <div class="home-search-date__days" data-date-range-days></div>
                                <div class="home-search-date__footer">
                                    <p><?= esc($copy['dateHint']) ?></p>
                                    <button type="button" class="home-search-date__clear" data-date-range-clear><?= esc($copy['dateClear']) ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="tour-list-filter__field tour-list-filter__field--select">
                    <span><?= esc(lang('Frontend.hero.search.tours', [], $locale)) ?></span>
                    <select name="tour_type">
                        <option value=""<?= $searchState['tour_type'] === '' ? ' selected' : '' ?>><?= esc($copy['allTypes']) ?></option>
                        <option value="outbound"<?= $searchState['tour_type'] === 'outbound' ? ' selected' : '' ?>><?= esc($copy['outbound']) ?></option>
                        <option value="inbound"<?= $searchState['tour_type'] === 'inbound' ? ' selected' : '' ?>><?= esc($copy['inbound']) ?></option>
                    </select>
                </label>

                <div class="tour-list-filter__actions">
                    <button type="submit">
                        <i class="bi bi-search"></i>
                        <?= esc($copy['submit']) ?>
                    </button>
                    <a href="<?= esc($clearUrl, 'attr') ?>"><?= esc($copy['clear']) ?></a>
                </div>
            </form>
        </div>
    </div>
</section>
