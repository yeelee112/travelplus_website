<?php

namespace App\Services;

class SeoService
{
    public function excerpt(?string $text, int $limit = 160): string
    {
        $plain = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';

        if ($plain === '') {
            return '';
        }

        if (mb_strlen($plain) <= $limit) {
            return $plain;
        }

        return rtrim(mb_substr($plain, 0, max(1, $limit - 3))) . '...';
    }

    public function organizationSchema(): array
    {
        return [
            '@type' => 'Organization',
            'name' => 'Travel Plus',
            'url' => base_url('/'),
            'logo' => base_url('assets/images/logo.svg'),
        ];
    }

    public function websiteSchema(string $searchUrl): array
    {
        return [
            '@type' => 'WebSite',
            'name' => 'Travel Plus',
            'url' => base_url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchUrl,
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function webpageSchema(string $title, string $description, string $url): array
    {
        return [
            '@type' => 'WebPage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
        ];
    }

    public function breadcrumbSchema(array $breadcrumbs, string $currentUrl): array
    {
        if ($breadcrumbs === []) {
            return [];
        }

        $items = [];

        foreach (array_values($breadcrumbs) as $breadcrumb) {
            $label = trim((string) ($breadcrumb['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $label,
                'item' => (string) ($breadcrumb['url'] ?? $currentUrl),
            ];
        }

        if ($items === []) {
            return [];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function tourSchema(array $tour, string $url): array
    {
        $schema = [
            '@type' => 'Product',
            'name' => (string) ($tour['title'] ?? 'Travel Tour'),
            'description' => $this->excerpt(
                (string) ($tour['meta_description'] ?? $tour['short_description'] ?? $tour['overview'] ?? $tour['description'] ?? ''),
                220
            ),
            'url' => $url,
            'image' => [$tour['image'] ?? base_url('assets/images/TravelPlus_CompanyProfile.png')],
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Travel Plus',
            ],
            'category' => (string) ($tour['tour_type'] ?? 'tour'),
        ];

        $priceAmount = (float) ($tour['price']['amount'] ?? 0);
        if ($priceAmount > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => (string) ($tour['price']['currency'] ?? 'VND'),
                'price' => number_format($priceAmount, 0, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
            ];
        }

        $reviewCount = (int) ($tour['review_summary']['count'] ?? 0);
        $ratingValue = (float) ($tour['review_summary']['overall'] ?? 0);
        if ($reviewCount > 0 && $ratingValue > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($ratingValue, 1, '.', ''),
                'reviewCount' => $reviewCount,
            ];
        }

        return $schema;
    }
}
