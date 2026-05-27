<?php
$currentLocale = $currentLocale ?? service('request')->getLocale() ?? 'vi';
$siteName = 'Travel Plus';
$metaTitle = trim((string) ($meta_title ?? '')) ?: $siteName;
$metaDesc = trim((string) ($meta_desc ?? '')) ?: 'Travel Plus travel services and tour packages.';
$canonicalUrl = (string) ($canonical_url ?? current_url());
$metaRobots = (string) ($meta_robots ?? 'index,follow,max-image-preview:large');
$metaType = (string) ($meta_type ?? 'website');
$metaImage = (string) ($meta_image ?? base_url('assets/images/TravelPlus_CompanyProfile.png'));
$metaImageAlt = trim((string) ($meta_image_alt ?? '')) ?: $metaTitle;
$formatMetaDate = static function ($value): string {
    $timestamp = strtotime((string) $value);

    return $timestamp === false ? '' : date(DATE_ATOM, $timestamp);
};
$metaPublishedTime = $formatMetaDate($meta_published_time ?? '');
$metaUpdatedTime = $formatMetaDate($meta_updated_time ?? '');
$metaAuthor = trim((string) ($meta_author ?? ''));
$alternateLinks = is_array($alternate_links ?? null) ? $alternate_links : [];
$schemaGraph = is_array($schema_graph ?? null) ? array_values(array_filter($schema_graph)) : [];
$ogLocale = $currentLocale === 'en' ? 'en_US' : 'vi_VN';
$requestUri = service('request')->getUri();
$firstSegment = $requestUri->getSegment(1);
$requestPath = trim((string) $requestUri->getPath(), '/');
$bodyClass = $requestPath === '' || ($currentLocale === 'en' && $requestPath === 'en')
    ? 'is-home-page'
    : 'is-inner-page';
$showAiChatbox = ! in_array($firstSegment, ['admin', 'api'], true);
$publicPath = rtrim(FCPATH, DIRECTORY_SEPARATOR);
$styleVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/css/style.css') ?: time();
$mainJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/main.js') ?: time();
$aboutJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/about-us.js') ?: time();
?>
<!doctype html>
<html lang="<?= esc($currentLocale) ?>">
<head>
<meta charset="utf-8">
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="<?= esc($metaRobots) ?>">
<meta name="googlebot" content="<?= esc($metaRobots) ?>">
<meta name="application-name" content="<?= esc($siteName) ?>">
<meta name="theme-color" content="#0aa7df">
<meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
<meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
<link rel="canonical" href="<?= esc($canonicalUrl) ?>">

<meta property="og:site_name" content="<?= esc($siteName) ?>">
<meta property="og:type" content="<?= esc($metaType) ?>">
<meta property="og:title" content="<?= esc($metaTitle) ?>">
<meta property="og:description" content="<?= esc($metaDesc) ?>">
<meta property="og:url" content="<?= esc($canonicalUrl) ?>">
<meta property="og:image" content="<?= esc($metaImage) ?>">
<meta property="og:image:alt" content="<?= esc($metaImageAlt) ?>">
<meta property="og:locale" content="<?= esc($ogLocale) ?>">
<?php if ($metaUpdatedTime !== ''): ?>
<meta property="og:updated_time" content="<?= esc($metaUpdatedTime) ?>">
<?php endif; ?>
<?php if ($metaType === 'article' && $metaPublishedTime !== ''): ?>
<meta property="article:published_time" content="<?= esc($metaPublishedTime) ?>">
<?php endif; ?>
<?php if ($metaType === 'article' && $metaUpdatedTime !== ''): ?>
<meta property="article:modified_time" content="<?= esc($metaUpdatedTime) ?>">
<?php endif; ?>
<?php if ($metaType === 'article' && $metaAuthor !== ''): ?>
<meta property="article:author" content="<?= esc($metaAuthor) ?>">
<?php endif; ?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($metaTitle) ?>">
<meta name="twitter:description" content="<?= esc($metaDesc) ?>">
<meta name="twitter:image" content="<?= esc($metaImage) ?>">
<meta name="twitter:image:alt" content="<?= esc($metaImageAlt) ?>">

<?php foreach ($alternateLinks as $alternateLink): ?>
<?php if (!empty($alternateLink['href']) && !empty($alternateLink['hreflang'])): ?>
<link rel="alternate" hreflang="<?= esc((string) $alternateLink['hreflang']) ?>" href="<?= esc((string) $alternateLink['href']) ?>">
<?php endif; ?>
<?php endforeach; ?>

<?php if ($schemaGraph !== []): ?>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://www.google.com">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . $styleVersion) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>

<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
</head>
<body class="<?= esc($bodyClass) ?>">

<?= $this->include('partials/header') ?>
<?= $this->renderSection('content') ?>
<?= $this->include('partials/footer') ?>
<?php if ($showAiChatbox): ?>
<?= $this->include('partials/ai-chatbox') ?>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" integrity="sha512-Eak/29OTpb36LLo2r47IpVzPBLXnAMPAVypbSZiZ4Qkf8p/7S/XRG5xp7OKWPPYfJT6metI+IORkR5G8F900+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  const URL_API = "<?= localized_url() ?>";
  window.BASE_URL = "<?= base_url() ?>";
  window.CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
  window.CSRF_TOKEN = "<?= csrf_hash() ?>";
</script>
<?php $recaptchaSiteKey = trim((string) env('recaptcha.siteKey', '')); ?>
<?php if ($recaptchaSiteKey !== ''): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey) ?>"></script>
<?php endif; ?>

<script type="module" src="<?= base_url('assets/js/about-us.js?v=' . $aboutJsVersion) ?>"></script>

<script type="module" src="<?= base_url('assets/js/main.js?v=' . $mainJsVersion) ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
