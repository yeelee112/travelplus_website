<?php
$currentLocale = $currentLocale ?? service('request')->getLocale() ?? 'vi';
$siteName = 'Travel Plus';
$metaTitle = trim((string) ($meta_title ?? '')) ?: $siteName;
$metaDesc = trim((string) ($meta_desc ?? '')) ?: 'Travel Plus travel services and tour packages.';
$canonicalUrl = (string) ($canonical_url ?? current_url());
$metaRobots = (string) ($meta_robots ?? 'index,follow,max-image-preview:large');
$metaType = (string) ($meta_type ?? 'website');
$metaImage = (string) ($meta_image ?? base_url('assets/images/TravelPlus_CompanyProfile.png'));
$alternateLinks = is_array($alternate_links ?? null) ? $alternate_links : [];
$schemaGraph = is_array($schema_graph ?? null) ? array_values(array_filter($schema_graph)) : [];
$ogLocale = $currentLocale === 'en' ? 'en_US' : 'vi_VN';
?>
<!doctype html>
<html lang="<?= esc($currentLocale) ?>">
<head>
<meta charset="utf-8">
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="googlebot" content="<?= esc($metaRobots) ?>">
<link rel="canonical" href="<?= esc($canonicalUrl) ?>">

<meta property="og:site_name" content="<?= esc($siteName) ?>">
<meta property="og:type" content="<?= esc($metaType) ?>">
<meta property="og:title" content="<?= esc($metaTitle) ?>">
<meta property="og:description" content="<?= esc($metaDesc) ?>">
<meta property="og:url" content="<?= esc($canonicalUrl) ?>">
<meta property="og:image" content="<?= esc($metaImage) ?>">
<meta property="og:locale" content="<?= esc($ogLocale) ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($metaTitle) ?>">
<meta name="twitter:description" content="<?= esc($metaDesc) ?>">
<meta name="twitter:image" content="<?= esc($metaImage) ?>">

<?php foreach ($alternateLinks as $alternateLink): ?>
<?php if (!empty($alternateLink['href']) && !empty($alternateLink['hreflang'])): ?>
<link rel="alternate" hreflang="<?= esc((string) $alternateLink['hreflang']) ?>" href="<?= esc((string) $alternateLink['href']) ?>">
<?php endif; ?>
<?php endforeach; ?>

<?php if ($schemaGraph !== []): ?>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

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

<script type="module" src="<?= base_url('assets/js/about-us.js?v=20260506-1') ?>"></script>

<script type="module" src="<?= base_url('assets/js/main.js?v=20260506-1') ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
