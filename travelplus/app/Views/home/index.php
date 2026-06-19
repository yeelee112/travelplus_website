<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
@media (max-width: 575px) {
    .home-page__blog .container,
    .home-page__gallery .container {
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
    }

    .home-page__blog .home-section-head,
    .home-page__gallery .home-section-head {
        padding-left: 12px;
        padding-right: 12px;
    }

    .home-page__blog .home-section-link,
    .home-page__gallery .home-section-link {
        width: 100%;
        justify-content: center;
    }

    .home-page__blog .home-blog-layout,
    .home-page__gallery .home-gallery-grid {
        display: grid;
        grid-auto-columns: minmax(286px, 84vw);
        grid-auto-flow: column;
        grid-template-columns: none;
        gap: 14px;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-inline: contain;
        padding: 2px 12px 16px;
        scroll-padding-inline: 12px;
        scroll-snap-type: inline mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .home-page__blog .home-blog-layout::-webkit-scrollbar,
    .home-page__gallery .home-gallery-grid::-webkit-scrollbar {
        display: none;
    }

    .home-page__blog .home-blog-list {
        display: contents;
    }

    .home-page__blog .home-blog-feature,
    .home-page__blog .home-blog-card {
        min-width: 0;
        min-height: 100%;
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
        scroll-snap-align: start;
    }

    .home-page__blog .home-blog-feature__media img,
    .home-page__blog .home-blog-card__media img {
        aspect-ratio: 1.58 / 1;
        height: auto;
    }

    .home-page__blog .home-blog-feature__body,
    .home-page__blog .home-blog-card__body {
        align-content: start;
        gap: 10px;
        padding: 16px;
    }

    .home-page__blog .home-blog-feature h3,
    .home-page__blog .home-blog-card h3 {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        font-size: 19px;
        line-height: 1.25;
    }

    .home-page__blog .home-blog-feature p {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        font-size: 14px;
        line-height: 1.55;
    }

    .home-page__gallery .home-gallery-grid {
        grid-auto-rows: 218px;
    }

    .home-page__gallery .home-gallery-item {
        min-width: 0;
        scroll-snap-align: start;
    }

    .home-page__gallery .home-gallery-item,
    .home-page__gallery .home-gallery-item--wide {
        grid-column: auto;
        grid-row: auto;
    }

    .home-page__blog .home-blog-layout::after,
    .home-page__gallery .home-gallery-grid::after {
        content: "";
        width: 1px;
    }
}
</style>

<main class="home-page">
    <?= $this->include('sections/hero-search') ?>
    <?= $this->include('sections/home-promotions') ?>
    <?= $this->include('sections/home-tour') ?>
    <?= $this->include('sections/featured-destination') ?>
    <?= $this->include('sections/home-blog') ?>
    <?= $this->include('sections/testimonial') ?>
    <?= $this->include('sections/counter') ?>
    <?= $this->include('sections/gallery-home') ?>
</main>



<?= $this->endSection() ?>
