<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$npx = 'npx';
if (PHP_OS_FAMILY === 'Windows') {
    exec('where npx.cmd', $npxCandidates, $npxLookupCode);
    $npx = $npxLookupCode === 0 && isset($npxCandidates[0])
        ? trim((string) $npxCandidates[0])
        : 'npx.cmd';
}
$esbuild = 'esbuild@0.25.6';

$run = static function (array $parts, string $workingDirectory): void {
    $command = implode(' ', array_map('escapeshellarg', $parts));
    $previousDirectory = getcwd();

    if (! chdir($workingDirectory)) {
        fwrite(STDERR, "Unable to use working directory: {$workingDirectory}" . PHP_EOL);
        exit(1);
    }

    passthru($command, $exitCode);

    if (is_string($previousDirectory)) {
        chdir($previousDirectory);
    }

    if ($exitCode !== 0) {
        fwrite(STDERR, "Asset build failed: {$command}" . PHP_EOL);
        exit($exitCode);
    }
};

$run([PHP_BINARY, 'scripts/optimize-static-images.php'], $root);
$run([PHP_BINARY, 'scripts/minify-css.php'], $root);
$run([PHP_BINARY, 'scripts/split-frontend-css.php'], $root);

$assets = [
    ['public/assets/css/widgets.css', 'public/assets/css/widgets.min.css'],
    ['public/assets/css/style-common.css', 'public/assets/css/style-common.min.css'],
    ['public/assets/css/style-tour-detail.css', 'public/assets/css/style-tour-detail.min.css'],
    ['public/assets/css/style-contact.css', 'public/assets/css/style-contact.min.css'],
    ['public/assets/css/style-mice.css', 'public/assets/css/style-mice.min.css'],
    ['public/assets/css/style-visa.css', 'public/assets/css/style-visa.min.css'],
    ['public/assets/css/style-summer.css', 'public/assets/css/style-summer.min.css'],
    ['public/assets/css/style-booking.css', 'public/assets/css/style-booking.min.css'],
    ['public/assets/css/style-about.css', 'public/assets/css/style-about.min.css'],
    ['public/assets/css/style-home.css', 'public/assets/css/style-home.min.css'],
    ['public/assets/css/style-blog.css', 'public/assets/css/style-blog.min.css'],
    ['public/assets/css/style-legal.css', 'public/assets/css/style-legal.min.css'],
    ['public/assets/css/style-account.css', 'public/assets/css/style-account.min.css'],
    ['public/assets/js/main.js', 'public/assets/js/main.min.js'],
    ['public/assets/js/ai-chatbox.js', 'public/assets/js/ai-chatbox.min.js'],
    ['public/assets/js/tour-tools.js', 'public/assets/js/tour-tools.min.js'],
    ['public/assets/js/cookie-consent.js', 'public/assets/js/cookie-consent.min.js'],
    ['public/assets/js/tour-detail.js', 'public/assets/js/tour-detail.min.js'],
    ['public/assets/js/contact-page.js', 'public/assets/js/contact-page.min.js'],
    ['public/assets/js/about-us.js', 'public/assets/js/about-us.min.js'],
];

foreach ($assets as [$source, $target]) {
    $run([
        $npx,
        '--yes',
        $esbuild,
        $source,
        '--minify',
        '--target=es2019',
        '--legal-comments=none',
        '--outfile=' . $target,
    ], $root);
}

fwrite(STDOUT, "Frontend assets are ready for publish." . PHP_EOL);
