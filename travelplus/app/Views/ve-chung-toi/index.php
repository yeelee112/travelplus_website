<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= view('sections/about-info', ['content' => $pageContent ?? []]) ?>



<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('assets/js/about-us.js?v=' . (@filemtime(FCPATH . 'assets/js/about-us.js') ?: time())) ?>"></script>
<?= $this->endSection() ?>
