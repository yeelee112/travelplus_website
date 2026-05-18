<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= view('sections/about-info', ['content' => $pageContent ?? []]) ?>



<?= $this->endSection() ?>
