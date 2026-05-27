<?php $featuredDestinations = $featuredDestinations ?? []; ?>
<div class="home1-destination-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-60 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-lg-10">
                <div class="section-title text-center">
                    <h2><?= esc(lang('Frontend.home.featuredDestination.title')) ?></h2>
                </div>
                <?php if ($featuredDestinations !== []): ?>
                    <ul class="nav nav-pills mb-3" id="featured-destination-tabs">
                        <?php foreach ($featuredDestinations as $index => $tab): ?>
                            <li class="nav-item">
                                <button
                                    class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                    data-bs-toggle="pill"
                                    data-bs-target="#featured-destination-<?= esc((string) $tab['key']) ?>"
                                    type="button">
                                    <?= esc((string) $tab['label']) ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content" id="featured-destination-contents">
                        <?php foreach ($featuredDestinations as $index => $tab): ?>
                            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="featured-destination-<?= esc((string) $tab['key']) ?>">
                                <div class="row g-xl-4 g-lg-3 gy-4 featured-destination-grid">
                                    <?php foreach (($tab['items'] ?? []) as $item): ?>
                                        <div class="<?= esc((string) ($item['col'] ?? 'col-lg-4 col-md-6')) ?>">
                                            <a href="<?= esc((string) ($item['link'] ?? '#')) ?>">
                                                <div class="destination-card2 four">
                                                    <div class="destination-img">
                                                        <img src="<?= esc((string) ($item['image'] ?? base_url('assets/images/avt-tour-01.jpg'))) ?>" alt="<?= esc((string) ($item['title'] ?? '')) ?>" loading="lazy" decoding="async" width="640" height="420">
                                                    </div>
                                                    <div class="destination-content-wrap">
                                                        <div class="destination-content">
                                                            <span class="text-white-50 d-block small mb-1"><?= esc((string) ($item['subtitle'] ?? '')) ?></span>
                                                            <h5 class="text-white"><?= esc((string) ($item['title'] ?? '')) ?></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
