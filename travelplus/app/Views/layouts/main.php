<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= esc($meta_title ?? 'Travel Plus') ?></title>
<meta name="description" content="<?= esc($meta_desc ?? '') ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css" integrity="sha512-t7Few9xlddEmgd3oKZQahkNI4dS6l80+eGEzFQiqtyVYdvcSG2D3Iub77R20BdotfRPA9caaRkg1tyaJiPmO0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<?= $this->include('partials/header') ?>
<?= $this->renderSection('content') ?>
<?= $this->include('partials/footer') ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
