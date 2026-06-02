<?php

namespace App\Services;

class TextEncodingService
{
    /**
     * Repairs common UTF-8 text that was accidentally decoded as Windows-1252.
     */
    public static function repair(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $current = $value;

        for ($i = 0; $i < 2; $i++) {
            if (! self::looksMojibake($current)) {
                break;
            }

            $candidate = self::bestDecodedCandidate($current);
            if ($candidate === null) {
                break;
            }

            if (self::mojibakeScore($candidate) >= self::mojibakeScore($current)) {
                break;
            }

            $current = $candidate;
        }

        return $current;
    }

    public static function repairNullable(?string $value): string
    {
        return self::repair((string) $value);
    }

    public static function repairHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (! is_array($parts)) {
            return self::repair($html);
        }

        foreach ($parts as $index => $part) {
            if ($part === '' || str_starts_with($part, '<')) {
                continue;
            }

            if (preg_match('/^(\s*)(.*?)(\s*)$/us', $part, $matches) === 1) {
                $parts[$index] = $matches[1] . self::repair($matches[2]) . $matches[3];
                continue;
            }

            $parts[$index] = self::repair($part);
        }

        return implode('', $parts);
    }

    public static function repairNullableHtml(?string $html): string
    {
        return self::repairHtml((string) $html);
    }

    private static function looksMojibake(string $value): bool
    {
        return self::mojibakeScore($value) > 0;
    }

    private static function mojibakeScore(string $value): int
    {
        $markers = [
            'Ã',
            'Â',
            'â€',
            'â€™',
            'â€œ',
            'â€',
            'â€“',
            'â€”',
            'Ä‘',
            'Ä',
            'áº',
            'á»',
            'Æ°',
            'Æ¡',
        ];

        $score = 0;
        foreach ($markers as $marker) {
            $score += substr_count($value, $marker);
        }

        $controlMatches = [];
        if (preg_match_all('/[\x{0080}-\x{009F}]/u', $value, $controlMatches) !== false) {
            $score += count($controlMatches[0]);
        }

        return $score;
    }

    private static function bestDecodedCandidate(string $value): ?string
    {
        $currentScore = self::mojibakeScore($value);
        $best = null;
        $bestScore = $currentScore;

        foreach (['Windows-1252', 'ISO-8859-1'] as $sourceEncoding) {
            $candidate = @iconv('UTF-8', $sourceEncoding . '//IGNORE', $value);
            if (! is_string($candidate) || $candidate === '' || preg_match('//u', $candidate) !== 1) {
                continue;
            }

            $candidateScore = self::mojibakeScore($candidate);
            if ($candidateScore < $bestScore) {
                $best = $candidate;
                $bestScore = $candidateScore;
            }
        }

        return $best;
    }
}
