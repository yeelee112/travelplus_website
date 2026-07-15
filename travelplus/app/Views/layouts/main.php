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
$paginationLinks = is_array($pagination_links ?? null) ? $pagination_links : [];
$schemaGraph = is_array($schema_graph ?? null) ? array_values(array_filter($schema_graph)) : [];
$ogLocale = $currentLocale === 'en' ? 'en_US' : 'vi_VN';
$requestUri = service('request')->getUri();
$firstSegment = $requestUri->getSegment(1);
$routeSegment = $currentLocale === 'en' && $firstSegment === 'en'
    ? $requestUri->getSegment(2)
    : $firstSegment;
$requestPath = trim((string) $requestUri->getPath(), '/');
$bodyClass = $requestPath === '' || ($currentLocale === 'en' && $requestPath === 'en')
    ? 'is-home-page'
    : 'is-inner-page';
$isContactPage = $requestPath === 'contact' || str_ends_with($requestPath, '/contact');
$isFocusedFlow = in_array($routeSegment, ['account', 'auth', 'booking'], true);
$showAiChatbox = ! in_array($routeSegment, ['admin', 'api'], true) && ! $isFocusedFlow;
$showTourTools = ! in_array($routeSegment, ['admin', 'api'], true) && ! $isFocusedFlow;
$showCookieConsent = ! in_array($routeSegment, ['admin', 'api'], true);
$contentSection = $this->renderSection('content');
$usesSwiper = str_contains($contentSection, 'swiper-wrapper');
$publicPath = rtrim(FCPATH, DIRECTORY_SEPARATOR);
$requestHost = strtolower($requestUri->getHost());
$isLocalRequest = in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true);
$googleAnalyticsId = 'G-W2FBGJD5YK';
if (str_starts_with($requestHost, 'demo.') || $isFocusedFlow) {
    $metaRobots = 'noindex,nofollow,max-image-preview:large';
}
$hasMinifiedStyle = is_file($publicPath . DIRECTORY_SEPARATOR . 'assets/css/style.min.css');
$styleAsset = (! $isLocalRequest && $hasMinifiedStyle)
    ? 'assets/css/style.min.css'
    : 'assets/css/style.css';
$styleVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . $styleAsset) ?: time();
$mainJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/main.js') ?: time();
$widgetCssVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/css/widgets.css') ?: time();
$aiChatboxJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/ai-chatbox.js') ?: time();
$tourToolsJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/tour-tools.js') ?: time();
$cookieConsentJsVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/js/cookie-consent.js') ?: time();
$faviconVersion = @filemtime($publicPath . DIRECTORY_SEPARATOR . 'assets/images/icon/favicon.svg') ?: time();
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
<link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/icon/favicon.svg?v=' . $faviconVersion) ?>">
<link rel="shortcut icon" type="image/svg+xml" href="<?= base_url('assets/images/icon/favicon.svg?v=' . $faviconVersion) ?>">
<meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
<meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
<?php if (! $isLocalRequest && $googleAnalyticsId !== ''): ?>
<script type="text/plain" data-cookie-category="analytics" async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($googleAnalyticsId, 'url') ?>"></script>
<script type="text/plain" data-cookie-category="analytics">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= esc($googleAnalyticsId) ?>');
</script>
<?php endif; ?>
<link rel="canonical" href="<?= esc($canonicalUrl) ?>">
<?php if (! empty($paginationLinks['prev'])): ?>
<link rel="prev" href="<?= esc((string) $paginationLinks['prev']) ?>">
<?php endif; ?>
<?php if (! empty($paginationLinks['next'])): ?>
<link rel="next" href="<?= esc((string) $paginationLinks['next']) ?>">
<?php endif; ?>

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

<?php if ($bodyClass === 'is-home-page'): ?>
<link rel="preload" as="image" href="<?= base_url('assets/images/home/banner01.webp') ?>" type="image/webp" fetchpriority="high">
<?php endif; ?>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php if ($isContactPage): ?>
<link rel="preconnect" href="https://www.google.com">
<?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap">
<link rel="stylesheet" href="<?= base_url($styleAsset . '?v=' . $styleVersion) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/widgets.css?v=' . $widgetCssVersion) ?>">
<?php if ($usesSwiper): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>

<script defer src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<?php endif; ?>
</head>
<body
    class="<?= esc($bodyClass) ?>"
    data-base-url="<?= esc(base_url(), 'attr') ?>"
    data-localized-url="<?= esc(localized_url(), 'attr') ?>"
    data-csrf-token-name="<?= esc(csrf_token(), 'attr') ?>"
    data-csrf-token="<?= esc(csrf_hash(), 'attr') ?>">

<?= $this->include('partials/header') ?>
<?= $contentSection ?>
<?= $this->include('partials/footer') ?>
<?php if ($showAiChatbox): ?>
<?= $this->include('partials/ai-chatbox') ?>
<script defer src="<?= base_url('assets/js/ai-chatbox.js?v=' . $aiChatboxJsVersion) ?>"></script>
<?php endif; ?>
<?php if ($showTourTools): ?>
<?= $this->include('partials/tour-tools') ?>
<script defer src="<?= base_url('assets/js/tour-tools.js?v=' . $tourToolsJsVersion) ?>"></script>
<?php endif; ?>
<?php if ($showCookieConsent): ?>
<?= $this->include('partials/cookie-consent') ?>
<script defer src="<?= base_url('assets/js/cookie-consent.js?v=' . $cookieConsentJsVersion) ?>"></script>
<?php endif; ?>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script type="module" src="<?= base_url('assets/js/main.js?v=' . $mainJsVersion) ?>"></script>
<?= $this->renderSection('scripts') ?>

</body>
</html>
