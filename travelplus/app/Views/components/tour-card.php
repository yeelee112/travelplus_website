<?php
$title = (string) ($tour['title'] ?? '');
$link = (string) ($tour['link'] ?? '#');
$image = (string) ($tour['image'] ?? '');
$badge = $tour['badge'] ?? null;
$locationName = (string) ($tour['continent'] ?? '');
$locationLink = (string) ($tour['continent_link'] ?? '#');
$durationLabel = (string) ($tour['duration']['label'] ?? trim(($tour['duration']['days'] ?? '') . ' Days ' . ($tour['duration']['nights'] ?? '') . ' Nights'));
$departureLabel = (string) ($tour['departure'] ?? '');
$priceLabel = (string) ($tour['price']['label'] ?? '');
?>

<div class="package-card">
    <div class="package-img-wrap">
        <a class="package-img" href="<?= esc($link) ?>">
            <img src="<?= esc($image) ?>" alt="<?= esc($title) ?>">
        </a>

        <?php if (!empty($badge)): ?>
            <div class="batch"><span><?= esc($badge) ?></span></div>
        <?php endif; ?>
    </div>
    <div class="h-100">
        <div class="package-content d-flex flex-column">
            <h5 class="clamp-2">
                <a href="<?= esc($link) ?>">
                    <?= esc($title) ?>
                </a>
            </h5>

            <div class="location-and-time">
                <div class="location"><i class="bi bi-geo-alt"></i><a href="<?= esc($locationLink) ?>"><?= esc($locationName) ?> </a>
                </div>
                <svg class="arrow" width="25" height="6" viewBox="0 0 25 6" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 3L5 5.88675V0.113249L0 3ZM25 3L20 0.113249V5.88675L25 3ZM4.5 3.5H20.5V2.5H4.5V3.5Z">
                    </path>
                </svg>
                <span>
                    <?= esc($durationLabel) ?>
                </span>
            </div>

            <?php if ($departureLabel !== ''): ?>
                <div class="location-and-time mb-3">
                    <div class="location">
                        <i class="bi bi-calendar"></i>
                        <a href="<?= esc($link) ?>">Departure dates: </a>
                        <span><?= esc($departureLabel) ?> </span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="btn-and-price-area mt-auto">
                <div class="price-area">
                    <h6>Price from</h6>
                    <span><?= esc($priceLabel) ?></span>
                </div>
                <a class="primary-btn1" href="<?= esc($link) ?>">
                    <span>
                        Book Now
                        <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                            </path>
                        </svg>
                    </span>
                    <span>
                        Book Now
                        <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                            </path>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
