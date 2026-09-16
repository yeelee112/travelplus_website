<section class="inbound-featured" aria-labelledby="inbound-featured-title">
    <div class="container">
        <div class="inbound-featured__head">
            <div><span class="inbound-section-kicker">Selected journeys</span><h2 id="inbound-featured-title">Find your<br><em>next adventure.</em></h2></div>
            <div><p>Compare a few highlights, then open a journey for its itinerary and pricing.</p><a href="#inbound-tours">Explore all tours <i class="bi bi-arrow-down-right"></i></a></div>
        </div>
        <div class="inbound-featured__grid">
            <?php foreach ($featuredInboundTours as $index => $featuredTour): ?>
                <?php
                $title = (string) ($featuredTour['title'] ?? '');
                $url = (string) ($featuredTour['link'] ?? '#');
                $image = (string) ($featuredTour['image'] ?? base_url('assets/images/destination/ha-noi.webp'));
                $srcset = responsive_image_srcset($image, [480, 960]);
                $duration = trim((string) ($featuredTour['duration']['label'] ?? ''));
                $amount = (float) ($featuredTour['price']['amount'] ?? 0);
                ?>
                <article class="inbound-featured-card">
                    <a class="inbound-featured-card__image" href="<?= esc($url, 'attr') ?>" aria-label="<?= esc('Explore ' . $title, 'attr') ?>">
                        <img src="<?= esc($image, 'attr') ?>" <?php if ($srcset !== ''): ?>srcset="<?= esc($srcset, 'attr') ?>" sizes="(max-width: 820px) 80vw, 33vw"<?php endif; ?> alt="<?= esc($title, 'attr') ?>" width="600" height="700" loading="lazy" decoding="async">
                        <span class="inbound-featured-card__number">JOURNEY <?= sprintf('%02d', $index + 1) ?></span>
                        <?php if ($duration !== ''): ?><span class="inbound-featured-card__duration"><i class="bi bi-clock"></i><?= esc($duration) ?></span><?php endif; ?>
                        <span class="inbound-featured-card__arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </a>
                    <div class="inbound-featured-card__body">
                        <h3><a href="<?= esc($url, 'attr') ?>"><?= esc($title) ?></a></h3>
                        <div class="inbound-featured-card__bottom">
                            <div class="inbound-featured-card__price">
                                <?php if ($amount > 0): ?><span>From / per person</span><strong><?= esc(number_format($amount, 0, '.', ',')) ?> <small>VND</small></strong><?php else: ?><span>Designed for your trip</span><strong>Request a quote</strong><?php endif; ?>
                            </div>
                            <a href="<?= esc($url, 'attr') ?>">View journey <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
