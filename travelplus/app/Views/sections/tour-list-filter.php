<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$searchState = array_merge([
    'q' => (string) (service('request')->getGet('q') ?? ''),
    'departure_date' => (string) (service('request')->getGet('departure_date') ?? ''),
    'tour_type' => (string) (service('request')->getGet('tour_type') ?? ''),
    'promotion_only' => (string) (service('request')->getGet('promotion') ?? '') === '1',
], is_array($listingSearch ?? null) ? $listingSearch : []);

$searchState['tour_type'] = in_array($searchState['tour_type'], ['outbound', 'inbound'], true) ? $searchState['tour_type'] : '';
$departureValue = trim((string) ($searchState['departure_date'] ?? ''));
$departureDateObject = null;

foreach (['Y-m-d', 'd/m/Y'] as $dateFormat) {
    $parsedDate = \DateTime::createFromFormat($dateFormat, $departureValue);

    if ($parsedDate instanceof \DateTime) {
        $departureDateObject = $parsedDate;
        break;
    }
}

$departureSubmitValue = $departureDateObject instanceof \DateTime ? $departureDateObject->format('Y-m-d') : '';
$departureDisplayValue = $departureDateObject instanceof \DateTime ? $departureDateObject->format('d/m/Y') : '';

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Find tours',
        'title' => 'Where would you like to go?',
        'destination' => 'Destination or keyword',
        'destinationPlaceholder' => 'Japan, Europe, Da Nang...',
        'date' => 'Departure date',
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
        'date' => 'Ngày khởi hành',
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
                    <div class="home-search-date tour-list-filter__date-picker" data-listing-date-picker data-locale="<?= esc($locale, 'attr') ?>">
                        <input type="hidden" name="departure_date" value="<?= esc($departureSubmitValue, 'attr') ?>" data-listing-date-input>
                        <button
                            type="button"
                            class="home-search-date__trigger<?= $departureDisplayValue !== '' ? ' is-selected' : '' ?>"
                            data-listing-date-trigger
                            data-empty-label="dd/mm/yyyy"
                            aria-expanded="false"
                            aria-haspopup="dialog">
                            <span class="home-search-date__value" data-listing-date-display><?= esc($departureDisplayValue !== '' ? $departureDisplayValue : 'dd/mm/yyyy') ?></span>
                        </button>
                        <div class="home-search-date__panel" data-listing-date-panel hidden>
                            <div class="home-search-date__calendar" role="dialog" aria-label="<?= esc($copy['date'], 'attr') ?>">
                                <div class="home-search-date__calendar-head">
                                    <button type="button" class="home-search-date__nav" data-listing-date-prev aria-label="Previous month">&lsaquo;</button>
                                    <strong class="home-search-date__month" data-listing-date-month></strong>
                                    <button type="button" class="home-search-date__nav" data-listing-date-next aria-label="Next month">&rsaquo;</button>
                                </div>
                                <div class="home-search-date__weekdays" data-listing-date-weekdays aria-hidden="true"></div>
                                <div class="home-search-date__days" data-listing-date-days></div>
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
