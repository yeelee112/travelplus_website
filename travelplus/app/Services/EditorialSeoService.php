<?php
namespace App\Services;

/** Editorial metadata, deliberately not emitted as meta keywords. */
class EditorialSeoService
{
    public const FIELDS = ['focus_keyword', 'secondary_keywords', 'social_hashtags'];

    public static function normalize(array $post, string $locale): array
    {
        $read = static fn(string $key): string => is_scalar($post[$key . '_' . $locale] ?? '')
            ? trim((string) ($post[$key . '_' . $locale] ?? '')) : '';
        return [
            'focus_keyword' => preg_replace('/\s+/u', ' ', $read('focus_keyword')) ?? '',
            'secondary_keywords' => self::list($read('secondary_keywords'), false),
            'social_hashtags' => self::list($read('social_hashtags'), true),
        ];
    }

    private static function list(string $value, bool $hashtags): string
    {
        $items = preg_split($hashtags ? '/[\s,;#]+/u' : '/[,;\r\n]+/u', $value) ?: [];
        $unique = [];
        foreach ($items as $item) {
            $item = trim($item);
            if ($hashtags) $item = preg_replace('/[^\p{L}\p{N}_]/u', '', $item) ?? '';
            if ($item === '') continue;
            $key = mb_strtolower($item, 'UTF-8');
            if (!isset($unique[$key])) $unique[$key] = ($hashtags ? '#' : '') . $item;
        }
        return implode($hashtags ? ' ' : ', ', $unique);
    }
}
