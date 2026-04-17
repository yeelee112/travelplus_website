<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= $this->include('blog/detail-blog') ?>



<?= $this->endSection() ?>
