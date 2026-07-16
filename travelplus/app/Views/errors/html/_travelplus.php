<?php
$errorCode = isset($errorCode) ? (int) $errorCode : (isset($code) ? (int) $code : 500);
$errorKey = isset($errorKey) ? (string) $errorKey : 'server';
$technicalMessage = isset($technicalMessage) ? trim((string) $technicalMessage) : '';

$localeSource = (string) ($_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?? '/');
$localePath = parse_url($localeSource, PHP_URL_PATH);
$isEnglish = is_string($localePath) && preg_match('#^/en(?:/|$)#i', $localePath) === 1;
$locale = $isEnglish ? 'en' : 'vi';

$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath = preg_replace('#/index\.php$#', '', $scriptName) ?? '';
$basePath = rtrim($basePath, '/');
$basePath = preg_replace('#/public$#i', '', $basePath) ?? $basePath;
$url = static fn (string $path): string => $basePath . '/' . ltrim($path, '/');

$copy = [
    'vi' => [
        'brandLine' => 'Đồng hành cùng mọi hành trình',
        'statusLabel' => 'Mã trạng thái',
        'home' => 'Về trang chủ',
        'tours' => 'Tìm tour phù hợp',
        'contact' => 'Liên hệ hỗ trợ',
        'supportLabel' => 'Cần hỗ trợ ngay?',
        'supportText' => 'Travel Plus sẵn sàng hỗ trợ bạn qua hotline.',
        'technical' => 'Chi tiết dành cho môi trường phát triển',
        'pages' => [
            'bad_request' => [
                'eyebrow' => 'Yêu cầu chưa hợp lệ',
                'title' => 'Đường dẫn này chưa thể xử lý',
                'description' => 'Đường dẫn hoặc dữ liệu gửi lên không đúng định dạng. Hãy kiểm tra lại và thử lần nữa.',
            ],
            'forbidden' => [
                'eyebrow' => 'Quyền truy cập bị giới hạn',
                'title' => 'Bạn không thể truy cập trang này',
                'description' => 'Nội dung này có thể yêu cầu quyền truy cập khác hoặc đường dẫn hiện tại đã bị giới hạn.',
            ],
            'not_found' => [
                'eyebrow' => 'Trang không tồn tại',
                'title' => 'Không tìm thấy trang bạn cần',
                'description' => 'Trang có thể đã được chuyển, đổi địa chỉ hoặc không còn tồn tại. Bạn có thể trở về trang chủ hoặc tiếp tục tìm tour.',
            ],
            'rate_limit' => [
                'eyebrow' => 'Quá nhiều yêu cầu',
                'title' => 'Bạn thao tác hơi nhanh',
                'description' => 'Hệ thống đang tạm giới hạn yêu cầu để bảo vệ website. Vui lòng chờ một lúc rồi thử lại.',
            ],
            'maintenance' => [
                'eyebrow' => 'Tạm thời gián đoạn',
                'title' => 'Website đang được bảo trì',
                'description' => 'Travel Plus đang thực hiện một số cập nhật. Vui lòng quay lại sau ít phút hoặc liên hệ hotline nếu cần hỗ trợ ngay.',
            ],
            'server' => [
                'eyebrow' => 'Sự cố tạm thời',
                'title' => 'Website đang gặp một chút gián đoạn',
                'description' => 'Yêu cầu của bạn chưa thể hoàn tất lúc này. Đội ngũ Travel Plus đã ghi nhận và bạn có thể thử lại sau.',
            ],
        ],
    ],
    'en' => [
        'brandLine' => 'With you on every journey',
        'statusLabel' => 'Status code',
        'home' => 'Back to homepage',
        'tours' => 'Find a tour',
        'contact' => 'Contact support',
        'supportLabel' => 'Need help now?',
        'supportText' => 'Travel Plus is available through our hotline.',
        'technical' => 'Development environment details',
        'pages' => [
            'bad_request' => [
                'eyebrow' => 'Invalid request',
                'title' => 'We could not process this address',
                'description' => 'The address or submitted data is not in a valid format. Please check it and try again.',
            ],
            'forbidden' => [
                'eyebrow' => 'Access restricted',
                'title' => 'You cannot access this page',
                'description' => 'This content may require different access permissions or the current address has been restricted.',
            ],
            'not_found' => [
                'eyebrow' => 'Page not found',
                'title' => 'We could not find the page you need',
                'description' => 'The page may have moved, changed its address, or no longer exists. Return home or continue browsing tours.',
            ],
            'rate_limit' => [
                'eyebrow' => 'Too many requests',
                'title' => 'You are moving a little too fast',
                'description' => 'Requests are temporarily limited to protect the website. Please wait a moment and try again.',
            ],
            'maintenance' => [
                'eyebrow' => 'Temporarily unavailable',
                'title' => 'The website is being maintained',
                'description' => 'Travel Plus is completing an update. Please return in a few minutes or call us if you need immediate support.',
            ],
            'server' => [
                'eyebrow' => 'Temporary issue',
                'title' => 'The website has hit a temporary interruption',
                'description' => 'Your request could not be completed right now. Travel Plus has recorded the issue and you can try again shortly.',
            ],
        ],
    ],
];

$languageCopy = $copy[$locale];
$pageCopy = $languageCopy['pages'][$errorKey] ?? $languageCopy['pages']['server'];
$homeUrl = $url($isEnglish ? 'en/' : '');
$tourUrl = $url($isEnglish ? 'en/tour-search' : 'tim-kiem-tour');
$contactUrl = $url($isEnglish ? 'en/contact' : 'contact');
$logoUrl = $url('assets/images/logo.svg');
$faviconUrl = $url('assets/images/icon/favicon.svg');
try {
    $websiteSettings = new \App\Services\WebsiteSettingsService();
    $contactPhone = $websiteSettings->get('hotline_e164');
    $phoneDisplay = $websiteSettings->phoneDisplay($locale);
} catch (\Throwable) {
    $contactPhone = '+84795681568';
    $phoneDisplay = $isEnglish ? '(+84) 79 568 1 568' : '079 568 1 568';
}
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$errorClass = 'error-code-' . max(0, $errorCode);
?>
<!doctype html>
<html lang="<?= $escape($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="color-scheme" content="light">
    <title><?= $escape($errorCode . ' | ' . $pageCopy['title'] . ' | Travel Plus') ?></title>
    <link rel="icon" href="<?= $escape($faviconUrl) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">
    <style>
        :root {
            --tp-blue: #009cde;
            --tp-blue-dark: #007db4;
            --tp-green: #87af45;
            --tp-ink: #102a3c;
            --tp-text: #4d6474;
            --tp-line: #d9e8ef;
            --tp-surface: #f4f9fb;
            --tp-accent: #009cde;
            --tp-accent-soft: #e4f5fc;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100vh;
            background: var(--tp-surface);
            color: var(--tp-ink);
            font-family: "Google Sans", Arial, Helvetica, sans-serif;
            letter-spacing: 0;
        }
        body.error-code-400 { --tp-accent: #d99000; --tp-accent-soft: #fff4d6; }
        body.error-code-401,
        body.error-code-403 { --tp-accent: #e24a4f; --tp-accent-soft: #fff0f1; }
        body.error-code-429 { --tp-accent: #7a57c2; --tp-accent-soft: #f2edfb; }
        body.error-code-500,
        body.error-code-502,
        body.error-code-504 { --tp-accent: #e24a4f; --tp-accent-soft: #fff0f1; }
        body.error-code-503 { --tp-accent: #d99000; --tp-accent-soft: #fff4d6; }
        a { color: inherit; }
        .error-site {
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh;
            min-height: 100dvh;
        }
        .error-container {
            width: min(1180px, calc(100% - 48px));
            margin: 0 auto;
        }
        .error-header {
            border-bottom: 1px solid var(--tp-line);
            background: #fff;
        }
        .error-header__inner {
            display: flex;
            min-height: 84px;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }
        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
        }
        .error-brand img {
            display: block;
            width: 154px;
            height: auto;
        }
        .error-brand span {
            padding-left: 16px;
            border-left: 1px solid var(--tp-line);
            color: var(--tp-text);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }
        .error-hotline {
            display: grid;
            gap: 2px;
            text-align: right;
            text-decoration: none;
        }
        .error-hotline span {
            color: var(--tp-text);
            font-size: 12px;
            font-weight: 700;
        }
        .error-hotline strong {
            color: var(--tp-blue-dark);
            font-size: 17px;
            line-height: 1.2;
        }
        .error-main {
            display: flex;
            align-items: center;
            padding: 64px 0;
        }
        .error-layout {
            display: grid;
            grid-template-columns: minmax(250px, .72fr) minmax(0, 1.28fr);
            gap: 72px;
            align-items: center;
        }
        .error-code-block {
            position: relative;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 34px 0 34px 38px;
            border-left: 6px solid var(--tp-accent);
        }
        .error-code-block::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 0;
            bottom: 20px;
            height: 10px;
            background: var(--tp-accent-soft);
        }
        .error-code-block span {
            color: var(--tp-text);
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
        }
        .error-code-block strong {
            position: relative;
            z-index: 1;
            margin-top: 8px;
            color: var(--tp-accent);
            font-size: 132px;
            font-weight: 900;
            line-height: .86;
        }
        .error-copy__eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 10px;
            border: 1px solid #c7dde7;
            border-radius: 6px;
            background: var(--tp-accent-soft);
            color: var(--tp-accent);
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .error-copy h1 {
            max-width: 720px;
            margin: 18px 0 0;
            color: var(--tp-ink);
            font-size: 46px;
            font-weight: 800;
            line-height: 1.12;
        }
        .error-copy p {
            max-width: 690px;
            margin: 20px 0 0;
            color: var(--tp-text);
            font-size: 18px;
            line-height: 1.65;
        }
        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }
        .error-button {
            display: inline-flex;
            min-height: 50px;
            align-items: center;
            justify-content: center;
            padding: 12px 19px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
            text-align: center;
            text-decoration: none;
        }
        .error-button--primary {
            border-color: var(--tp-blue-dark);
            background: var(--tp-blue);
            color: #fff;
            box-shadow: 0 12px 26px rgba(0, 125, 180, .2);
        }
        .error-button--primary:hover { background: var(--tp-blue-dark); }
        .error-button--secondary {
            border-color: #b9d0dc;
            background: #fff;
            color: var(--tp-ink);
        }
        .error-button--secondary:hover {
            border-color: var(--tp-green);
            color: #537b20;
        }
        .error-technical {
            max-width: 720px;
            margin-top: 24px;
            padding: 14px 16px;
            border: 1px solid var(--tp-line);
            border-radius: 8px;
            background: #fff;
            color: var(--tp-text);
        }
        .error-technical summary { cursor: pointer; font-weight: 800; }
        .error-technical pre {
            margin: 12px 0 0;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .error-footer {
            border-top: 1px solid var(--tp-line);
            background: #fff;
        }
        .error-footer__inner {
            display: flex;
            min-height: 92px;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }
        .error-support {
            display: grid;
            gap: 4px;
        }
        .error-support strong { font-size: 15px; }
        .error-support span {
            color: var(--tp-text);
            font-size: 14px;
        }
        .error-footer nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px 20px;
        }
        .error-footer nav a {
            color: var(--tp-blue-dark);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }
        .error-footer nav a:hover { text-decoration: underline; }
        @media (max-width: 860px) {
            .error-main { padding: 48px 0; }
            .error-layout { grid-template-columns: 1fr; gap: 34px; }
            .error-code-block { min-height: 170px; padding: 24px 0 24px 28px; }
            .error-code-block strong { font-size: 96px; }
            .error-code-block::after { bottom: 10px; }
            .error-copy h1 { font-size: 38px; }
        }
        @media (max-width: 620px) {
            .error-container { width: min(100% - 32px, 1180px); }
            .error-header__inner { min-height: 72px; }
            .error-brand img { width: 130px; }
            .error-brand span { display: none; }
            .error-hotline span { display: none; }
            .error-hotline strong { font-size: 14px; }
            .error-main { align-items: flex-start; padding: 38px 0 42px; }
            .error-layout { gap: 28px; }
            .error-code-block { min-height: 138px; padding: 20px 0 20px 22px; border-left-width: 5px; }
            .error-code-block strong { font-size: 76px; }
            .error-copy h1 { margin-top: 14px; font-size: 32px; line-height: 1.16; }
            .error-copy p { margin-top: 16px; font-size: 16px; line-height: 1.6; }
            .error-actions { display: grid; grid-template-columns: 1fr; margin-top: 24px; }
            .error-button { width: 100%; }
            .error-footer__inner { align-items: flex-start; flex-direction: column; padding: 22px 0; }
            .error-footer nav { justify-content: flex-start; }
        }
    </style>
</head>
<body class="<?= $escape($errorClass) ?>">
<div class="error-site">
    <header class="error-header">
        <div class="error-container error-header__inner">
            <a class="error-brand" href="<?= $escape($homeUrl) ?>" aria-label="Travel Plus">
                <img src="<?= $escape($logoUrl) ?>" alt="Travel Plus" width="154" height="45">
                <span><?= $escape($languageCopy['brandLine']) ?></span>
            </a>
            <a class="error-hotline" href="tel:<?= $escape($contactPhone) ?>">
                <span><?= $escape($languageCopy['supportLabel']) ?></span>
                <strong><?= $escape($phoneDisplay) ?></strong>
            </a>
        </div>
    </header>

    <main class="error-main">
        <div class="error-container error-layout">
            <div class="error-code-block" aria-label="<?= $escape($languageCopy['statusLabel'] . ' ' . $errorCode) ?>">
                <span><?= $escape($languageCopy['statusLabel']) ?></span>
                <strong><?= $escape($errorCode) ?></strong>
            </div>
            <section class="error-copy">
                <span class="error-copy__eyebrow"><?= $escape($pageCopy['eyebrow']) ?></span>
                <h1><?= $escape($pageCopy['title']) ?></h1>
                <p><?= $escape($pageCopy['description']) ?></p>
                <div class="error-actions">
                    <a class="error-button error-button--primary" href="<?= $escape($homeUrl) ?>"><?= $escape($languageCopy['home']) ?></a>
                    <a class="error-button error-button--secondary" href="<?= $escape($tourUrl) ?>"><?= $escape($languageCopy['tours']) ?></a>
                </div>
                <?php if ($technicalMessage !== ''): ?>
                    <details class="error-technical">
                        <summary><?= $escape($languageCopy['technical']) ?></summary>
                        <pre><?= $escape($technicalMessage) ?></pre>
                    </details>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="error-footer">
        <div class="error-container error-footer__inner">
            <div class="error-support">
                <strong><?= $escape($languageCopy['supportLabel']) ?></strong>
                <span><?= $escape($languageCopy['supportText']) ?></span>
            </div>
            <nav aria-label="Support links">
                <a href="<?= $escape($homeUrl) ?>"><?= $escape($languageCopy['home']) ?></a>
                <a href="<?= $escape($tourUrl) ?>"><?= $escape($languageCopy['tours']) ?></a>
                <a href="<?= $escape($contactUrl) ?>"><?= $escape($languageCopy['contact']) ?></a>
            </nav>
        </div>
    </footer>
</div>
</body>
</html>
