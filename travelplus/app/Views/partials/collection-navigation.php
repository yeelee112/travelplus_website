<?php if (!empty($navigationCollections)): ?>
<div class="collection-nav" aria-label="<?= $locale === 'en' ? 'Tour collections' : 'Bộ sưu tập tour' ?>">
    <strong><i class="bi bi-compass" aria-hidden="true"></i> <?= $locale === 'en' ? 'Collections' : 'Bộ sưu tập' ?></strong>
    <div class="collection-nav__links">
    <?php foreach ($navigationCollections as $collection): ?>
        <a href="<?= esc($collection['url'], 'attr') ?>"><?= esc($collection['label']) ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    <?php endforeach ?>
    </div>
</div>
<?php endif ?>
