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

        $value = self::decodeTextEntities($value);
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

            $leading = '';
            $trailing = '';

            if (preg_match('/^[ \t\r\n]+/', $part, $matches) === 1) {
                $leading = (string) ($matches[0] ?? '');
            }

            if (preg_match('/[ \t\r\n]+$/', $part, $matches) === 1) {
                $trailing = (string) ($matches[0] ?? '');
            }

            $core = substr($part, strlen($leading), strlen($part) - strlen($leading) - strlen($trailing));
            $parts[$index] = $leading . self::repair($core) . $trailing;
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

    private static function decodeTextEntities(string $value): string
    {
        if (! str_contains($value, '&')) {
            return $value;
        }

        $value = preg_replace_callback('/&#(?:x([0-9a-f]+)|([0-9]+));/iu', static function (array $matches): string {
            $codepoint = isset($matches[1]) && $matches[1] !== ''
                ? hexdec((string) $matches[1])
                : (int) ($matches[2] ?? 0);

            if ($codepoint < 0 || $codepoint > 0x10FFFF) {
                return (string) ($matches[0] ?? '');
            }

            return mb_chr($codepoint, 'UTF-8');
        }, $value) ?? $value;

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function mojibakeScore(string $value): int
    {
        $patterns = [
            '/\x{FFFD}/u',
            '/\x{00C3}[\x{0080}-\x{00BF}]/u',
            '/\x{00E1}[\x{00BA}\x{00BB}]/u',
            '/\x{00C4}[\x{0080}-\x{00BF}\x{0192}\x{2018}]/u',
            '/\x{00C6}[\x{00A1}\x{00B0}]/u',
            '/\x{00C2}[\x{0080}-\x{00BF}]/u',
            '/\x{00E2}(?:\x{0080}|\x{20AC})/u',
        ];

        $score = 0;
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $value, $matches) !== false) {
                $score += count($matches[0]);
            }
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
        $candidate = self::decodeMojibakeCandidate($value);

        if ($candidate !== null) {
            $candidateScore = self::mojibakeScore($candidate);
            if ($candidateScore < $bestScore) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private static function decodeMojibakeCandidate(string $value): ?string
    {
        if (preg_match_all('/./us', $value, $matches) === false) {
            return null;
        }

        $output = '';
        $bytes = '';
        $original = '';
        foreach ($matches[0] as $char) {
            $codepoint = self::unicodeCodepoint($char);
            if ($codepoint === null) {
                return null;
            }

            $windowsByte = self::windows1252Byte($codepoint);
            if ($windowsByte !== null) {
                $bytes .= chr($windowsByte);
                $original .= $char;
                continue;
            }

            if ($codepoint > 255) {
                $output .= self::decodeByteSegment($bytes, $original);
                $bytes = '';
                $original = '';
                $output .= $char;
                continue;
            }

            $bytes .= chr($codepoint);
            $original .= $char;
        }

        $output .= self::decodeByteSegment($bytes, $original);

        return $output !== $value ? $output : null;
    }

    private static function decodeByteSegment(string $bytes, string $original): string
    {
        if ($bytes === '') {
            return '';
        }

        return preg_match('//u', $bytes) === 1 ? $bytes : $original;
    }

    private static function unicodeCodepoint(string $char): ?int
    {
        $bytes = array_values(unpack('C*', $char) ?: []);
        $count = count($bytes);
        if ($count === 0) {
            return null;
        }

        $first = $bytes[0];
        if ($first < 0x80) {
            return $first;
        }

        if (($first & 0xE0) === 0xC0 && $count >= 2) {
            return (($first & 0x1F) << 6) | ($bytes[1] & 0x3F);
        }

        if (($first & 0xF0) === 0xE0 && $count >= 3) {
            return (($first & 0x0F) << 12) | (($bytes[1] & 0x3F) << 6) | ($bytes[2] & 0x3F);
        }

        if (($first & 0xF8) === 0xF0 && $count >= 4) {
            return (($first & 0x07) << 18) | (($bytes[1] & 0x3F) << 12) | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
        }

        return null;
    }

    private static function windows1252Byte(int $codepoint): ?int
    {
        $map = [
            0x20AC => 0x80,
            0x201A => 0x82,
            0x0192 => 0x83,
            0x201E => 0x84,
            0x2026 => 0x85,
            0x2020 => 0x86,
            0x2021 => 0x87,
            0x02C6 => 0x88,
            0x2030 => 0x89,
            0x0160 => 0x8A,
            0x2039 => 0x8B,
            0x0152 => 0x8C,
            0x017D => 0x8E,
            0x2018 => 0x91,
            0x2019 => 0x92,
            0x201C => 0x93,
            0x201D => 0x94,
            0x2022 => 0x95,
            0x2013 => 0x96,
            0x2014 => 0x97,
            0x02DC => 0x98,
            0x2122 => 0x99,
            0x0161 => 0x9A,
            0x203A => 0x9B,
            0x0153 => 0x9C,
            0x017E => 0x9E,
            0x0178 => 0x9F,
        ];

        return $map[$codepoint] ?? null;
    }
}
