<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= esc($meta_title ?? 'Travel Plus') ?></title>
<meta name="description" content="<?= esc($meta_desc ?? '') ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
</head>
<body>

<?= $this->include('partials/header') ?>
<?= $this->renderSection('content') ?>
<?= $this->include('partials/footer') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" integrity="sha512-Eak/29OTpb36LLo2r47IpVzPBLXnAMPAVypbSZiZ4Qkf8p/7S/XRG5xp7OKWPPYfJT6metI+IORkR5G8F900+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  const URL_API = "<?= localized_url() ?>";
  window.BASE_URL = "<?= base_url() ?>";
</script>
<script src="https://www.google.com/recaptcha/api.js?render=6LfgBncsAAAAAEmWNoT1xtCidf_t3tQEK7YkhWvw"></script>

<script type="module" src="<?= base_url('assets/js/about-us.js') ?>"></script>

<script type="module" src="<?= base_url('assets/js/main.js') ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
