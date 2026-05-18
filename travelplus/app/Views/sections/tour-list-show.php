<?php
$locale = service('request')->getLocale() ?: 'vi';
$pagination = $pagination ?? ['total' => count($tours ?? []), 'page' => 1, 'lastPage' => 1];
$baseUrl = current_url();
$queryParams = $_GET ?? [];
$buildPageUrl = static function (int $pageNumber) use ($baseUrl, $queryParams): string {
    $params = $queryParams;
    $params['page'] = $pageNumber;

    return $baseUrl . '?' . http_build_query($params);
};
$currentPage = (int) ($pagination['page'] ?? 1);
$lastPage = (int) ($pagination['lastPage'] ?? 1);
$total = (int) ($pagination['total'] ?? 0);
?>

<div class="package-grid-page mb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="package-grid-top-area">
                    <span><?= lang('Frontend.listing.matchingExperiences', [esc((string) $total)], $locale) ?></span>
                </div>
                <div class="list-grid-product-wrap column-2-wrapper">
                    <div class="row g-4 mb-40">
                        <?php foreach ($tours as $tour): ?>
                            <div class="col-lg-4 col-md-6 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
                                <?= view('components/tour-card', ['tour' => $tour]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($lastPage > 1): ?>
                    <div class="pagination-area mt-60 wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                        <div class="paginations-button">
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= esc($buildPageUrl($currentPage - 1)) ?>"><?= esc(lang('Frontend.pagination.prev', [], $locale)) ?></a>
                            <?php endif; ?>
                        </div>
                        <ul class="paginations">
                            <?php for ($p = 1; $p <= $lastPage; $p++): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a href="<?= esc($buildPageUrl($p)) ?>"><?= sprintf('%02d', $p) ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                        <div class="paginations-button">
                            <?php if ($currentPage < $lastPage): ?>
                                <a href="<?= esc($buildPageUrl($currentPage + 1)) ?>"><?= esc(lang('Frontend.pagination.next', [], $locale)) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
