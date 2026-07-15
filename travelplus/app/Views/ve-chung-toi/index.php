<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= view('sections/about-info', ['content' => $pageContent ?? []]) ?>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= esc(frontend_asset_url('assets/js/about-us.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
