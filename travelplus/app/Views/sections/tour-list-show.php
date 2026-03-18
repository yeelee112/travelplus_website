<?php
$pagination = $pagination ?? ['total' => count($tours ?? []), 'page' => 1, 'lastPage' => 1];
$baseUrl = localized_url('tour-nuoc-ngoai');
$currentPage = (int) ($pagination['page'] ?? 1);
$lastPage = (int) ($pagination['lastPage'] ?? 1);
$total = (int) ($pagination['total'] ?? 0);
?>

<div class="package-grid-page pt-100 mb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="package-grid-top-area">
                    <span><strong><?= esc($total) ?></strong> Unforgettable Journeys Await!</span>
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
                                <a href="<?= $baseUrl . '?page=' . ($currentPage - 1) ?>">Prev</a>
                            <?php endif; ?>
                        </div>
                        <ul class="paginations">
                            <?php for ($p = 1; $p <= $lastPage; $p++): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a href="<?= $baseUrl . '?page=' . $p ?>"><?= sprintf('%02d', $p) ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                        <div class="paginations-button">
                            <?php if ($currentPage < $lastPage): ?>
                                <a href="<?= $baseUrl . '?page=' . ($currentPage + 1) ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
