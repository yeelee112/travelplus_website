<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>

<section class="hero">
    <div class="hero-inner container">
        <h1><?= lang('Frontend.site_title') ?></h1>
        <p><?= lang('Frontend.featured_tours') ?></p>
        <form class="search-form" action="<?= site_url('search') ?>" method="get">
            <input type="text" name="q" placeholder="<?= lang('Frontend.search') ?>">
            <button type="submit"><?= lang('Frontend.search') ?></button>
        </form>
    </div>
</section>

<section class="featured container">
    <h2><?= lang('Frontend.featured_tours') ?></h2>
    <div class="cards">
        <?php if (! empty($featured)): foreach ($featured as $tour): ?>
            <article class="card">
                <img src="<?= base_url('public/assets/images/' . ($tour['image'] ?? 'placeholder-360x200.svg')) ?>" alt="<?= esc($tour['title_' . service('request')->getLocale()] ?? $tour['title_en']) ?>">
                <h3><?= esc($tour['title_' . service('request')->getLocale()] ?? $tour['title_en']) ?></h3>
                <p><strong>$<?= number_format($tour['price'],2) ?></strong> / person</p>
                <a class="btn" href="<?= site_url((service('request')->getLocale() ? service('request')->getLocale().'/' : '').'tour/'.$tour['slug']) ?>"><?= lang('Frontend.read_more') ?></a>
            </article>
        <?php endforeach; else: ?>
            <p>No featured tours yet.</p>
        <?php endif; ?>
    </div>
</section>

<section class="categories container">
    <h2><?= lang('Frontend.top_categories') ?></h2>
    <div class="category-grid">
        <?php if (! empty($categories)): foreach ($categories as $cat): ?>
            <div class="category-item">
                <img src="<?= base_url('public/assets/images/' . ($cat['image'] ?? 'placeholder-360x200.svg')) ?>" alt="<?= esc($cat['name_' . service('request')->getLocale()] ?? $cat['name_en']) ?>" style="width:100%;height:90px;object-fit:cover;border-radius:6px;margin-bottom:8px">
                <div><?= esc($cat['name_' . service('request')->getLocale()] ?? $cat['name_en']) ?></div>
            </div>
        <?php endforeach; else: ?>
            <p>No categories yet.</p>
        <?php endif; ?>
    </div>
</section>

<section class="why container">
    <h2><?= lang('Frontend.why_travel') ?></h2>
    <div class="columns">
        <div class="col">Security Assurance<br/>Easy Booking</div>
        <div class="col">Support 24/7<br/>Best Price</div>
    </div>
</section>

<?= $this->endSection() ?>