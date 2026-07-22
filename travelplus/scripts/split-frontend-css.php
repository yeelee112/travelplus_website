<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $root . DIRECTORY_SEPARATOR . 'public/assets/css/style.css';
$targetDirectory = $root . DIRECTORY_SEPARATOR . 'public/assets/css';
$bundlePatterns = [
    'tour-detail' => '/\.(?:package-details-page|tour-detail(?:-[a-z0-9_-]+)?|booking-modal|rating-modal)\b/i',
    'mice' => '/\.mice-page(?:__[a-z0-9_-]+)?\b/i',
    'visa' => '/\.visa-(?:lead|seo|risk|stats?|why|checklist|case)[a-z0-9_-]*\b/i',
    'summer' => '/\.summer-[a-z0-9_-]+\b/i',
    'booking' => '/\.(?:travelplus-booking[a-z0-9_-]*|booking-lookup[a-z0-9_-]*|checkout-stepper[a-z0-9_-]*|booking-checkout[a-z0-9_-]*)\b/i',
    'about' => '/\.(?:about-page[a-z0-9_-]*|journey-pane|jouney-content-wrapper)\b/i',
    'contact' => '/\.(?:travelplus-contact[a-z0-9_-]*|travelplus-inline-error)\b/i',
    'home' => '/\.home-(?:page|modern|promo|search|blog|section|tour|destination|testimonial|gallery|stats?|empty|inline)[a-z0-9_-]*\b/i',
    'blog' => '/\.travelplus-blog[a-z0-9_-]*\b/i',
    'legal' => '/\.travelplus-legal[a-z0-9_-]*\b/i',
    'account' => '/\.travelplus-(?:account|membership|loyalty|auth)[a-z0-9_-]*\b/i',
];

$css = @file_get_contents($sourcePath);
if (! is_string($css)) {
    fwrite(STDERR, "Unable to read source CSS: {$sourcePath}" . PHP_EOL);
    exit(1);
}

$css = preg_replace('~/\*[^*]*\*+(?:[^/*][^*]*\*+)*/~', '', $css) ?? $css;
$bundleNames = array_keys($bundlePatterns);
$emptyResult = static function () use ($bundleNames): array {
    return array_fill_keys(array_merge(['common'], $bundleNames), '');
};

$splitSelectors = static function (string $selectors): array {
    $parts = [];
    $start = 0;
    $roundDepth = 0;
    $squareDepth = 0;
    $quote = null;
    $length = strlen($selectors);

    for ($index = 0; $index < $length; $index++) {
        $character = $selectors[$index];

        if ($quote !== null) {
            if ($character === '\\') {
                $index++;
            } elseif ($character === $quote) {
                $quote = null;
            }
            continue;
        }

        if ($character === '"' || $character === "'") {
            $quote = $character;
        } elseif ($character === '(') {
            $roundDepth++;
        } elseif ($character === ')') {
            $roundDepth = max(0, $roundDepth - 1);
        } elseif ($character === '[') {
            $squareDepth++;
        } elseif ($character === ']') {
            $squareDepth = max(0, $squareDepth - 1);
        } elseif ($character === ',' && $roundDepth === 0 && $squareDepth === 0) {
            $parts[] = trim(substr($selectors, $start, $index - $start));
            $start = $index + 1;
        }
    }

    $parts[] = trim(substr($selectors, $start));

    return array_values(array_filter($parts, static fn (string $part): bool => $part !== ''));
};

$classifySelectors = static function (string $selectors) use ($bundlePatterns, $splitSelectors): string {
    $matchedBundle = null;

    foreach ($splitSelectors($selectors) as $selector) {
        $selectorBundle = null;
        foreach ($bundlePatterns as $bundle => $pattern) {
            if (preg_match($pattern, $selector) === 1) {
                $selectorBundle = $bundle;
                break;
            }
        }

        if ($selectorBundle === null) {
            return 'common';
        }

        if ($matchedBundle !== null && $matchedBundle !== $selectorBundle) {
            return 'common';
        }

        $matchedBundle = $selectorBundle;
    }

    return $matchedBundle ?? 'common';
};

$findOpeningDelimiter = static function (string $input, int $start): array {
    $quote = null;
    $roundDepth = 0;
    $squareDepth = 0;
    $length = strlen($input);

    for ($index = $start; $index < $length; $index++) {
        $character = $input[$index];

        if ($quote !== null) {
            if ($character === '\\') {
                $index++;
            } elseif ($character === $quote) {
                $quote = null;
            }
            continue;
        }

        if ($character === '"' || $character === "'") {
            $quote = $character;
        } elseif ($character === '(') {
            $roundDepth++;
        } elseif ($character === ')') {
            $roundDepth = max(0, $roundDepth - 1);
        } elseif ($character === '[') {
            $squareDepth++;
        } elseif ($character === ']') {
            $squareDepth = max(0, $squareDepth - 1);
        } elseif ($roundDepth === 0 && $squareDepth === 0 && ($character === '{' || $character === ';')) {
            return [$index, $character];
        }
    }

    return [$length, ''];
};

$findClosingBrace = static function (string $input, int $openingBrace): int {
    $depth = 1;
    $quote = null;
    $length = strlen($input);

    for ($index = $openingBrace + 1; $index < $length; $index++) {
        $character = $input[$index];

        if ($quote !== null) {
            if ($character === '\\') {
                $index++;
            } elseif ($character === $quote) {
                $quote = null;
            }
            continue;
        }

        if ($character === '"' || $character === "'") {
            $quote = $character;
        } elseif ($character === '{') {
            $depth++;
        } elseif ($character === '}') {
            $depth--;
            if ($depth === 0) {
                return $index;
            }
        }
    }

    return $length - 1;
};

$splitCss = null;
$splitCss = static function (string $input) use (&$splitCss, $emptyResult, $classifySelectors, $findOpeningDelimiter, $findClosingBrace): array {
    $result = $emptyResult();
    $length = strlen($input);
    $index = 0;

    while ($index < $length) {
        while ($index < $length && ctype_space($input[$index])) {
            $index++;
        }
        if ($index >= $length) {
            break;
        }

        [$delimiterIndex, $delimiter] = $findOpeningDelimiter($input, $index);
        if ($delimiter === '') {
            $result['common'] .= trim(substr($input, $index));
            break;
        }

        $prelude = trim(substr($input, $index, $delimiterIndex - $index));
        if ($delimiter === ';') {
            $result['common'] .= $prelude . ';' . PHP_EOL;
            $index = $delimiterIndex + 1;
            continue;
        }

        $closingBrace = $findClosingBrace($input, $delimiterIndex);
        $body = substr($input, $delimiterIndex + 1, $closingBrace - $delimiterIndex - 1);
        $wholeRule = $prelude . '{' . $body . '}' . PHP_EOL;
        $lowerPrelude = strtolower($prelude);

        if (str_starts_with($lowerPrelude, '@media ')
            || str_starts_with($lowerPrelude, '@supports ')
            || str_starts_with($lowerPrelude, '@container ')
            || str_starts_with($lowerPrelude, '@layer ')) {
            $children = $splitCss($body);
            foreach ($children as $bundle => $bundleBody) {
                if (trim($bundleBody) !== '') {
                    $result[$bundle] .= $prelude . '{' . $bundleBody . '}' . PHP_EOL;
                }
            }
        } elseif (str_starts_with($prelude, '@')) {
            $result['common'] .= $wholeRule;
        } else {
            $result[$classifySelectors($prelude)] .= $wholeRule;
        }

        $index = $closingBrace + 1;
    }

    return $result;
};

$bundles = $splitCss($css);
$sourceBytes = strlen($css);
$outputBytes = 0;

foreach ($bundles as $bundle => $bundleCss) {
    $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . 'style-' . $bundle . '.css';
    $bundleCss = trim($bundleCss) . PHP_EOL;
    if (@file_put_contents($targetPath, $bundleCss) === false) {
        fwrite(STDERR, "Unable to write CSS bundle: {$targetPath}" . PHP_EOL);
        exit(1);
    }

    $bytes = strlen($bundleCss);
    $outputBytes += $bytes;
    echo sprintf("%-12s %8s bytes%s", $bundle, number_format($bytes), PHP_EOL);
}

$difference = $outputBytes - $sourceBytes;
echo sprintf(
    "Split %s source bytes into %s bytes (%+d wrapper bytes).%s",
    number_format($sourceBytes),
    number_format($outputBytes),
    $difference,
    PHP_EOL
);
