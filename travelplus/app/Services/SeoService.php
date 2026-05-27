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
            '@id' => base_url('/#organization'),
            'name' => 'Travel Plus',
            'url' => base_url('/'),
            'logo' => base_url('assets/images/logo.svg'),
            'email' => 'info@travelplusvn.com',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+84795681568',
                'contactType' => 'customer service',
                'areaServed' => ['VN'],
                'availableLanguage' => ['vi', 'en'],
            ],
        ];
    }

    public function websiteSchema(string $searchUrl): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => base_url('/#website'),
            'name' => 'Travel Plus',
            'url' => base_url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchUrl,
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function webpageSchema(string $title, string $description, string $url, string $type = 'WebPage'): array
    {
        return [
            '@type' => $type,
            '@id' => $url . '#webpage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => base_url('/#website'),
                'name' => 'Travel Plus',
                'url' => base_url('/'),
            ],
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
            '@id' => $url . '#tour',
            'name' => (string) ($tour['title'] ?? 'Travel Tour'),
            'description' => $this->excerpt(
                (string) ($tour['meta_description'] ?? $tour['short_description'] ?? $tour['overview'] ?? $tour['description'] ?? ''),
                220
            ),
            'url' => $url,
            'image' => [$this->absoluteUrl((string) ($tour['image'] ?? base_url('assets/images/TravelPlus_CompanyProfile.png')))],
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Travel Plus',
            ],
            'provider' => [
                '@id' => base_url('/#organization'),
            ],
            'mainEntityOfPage' => [
                '@id' => $url . '#webpage',
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

        $modifiedAt = $this->formatIsoDate((string) ($tour['updated_at'] ?? $tour['created_at'] ?? ''));
        if ($modifiedAt !== '') {
            $schema['dateModified'] = $modifiedAt;
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

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function itemListSchema(string $name, string $url, array $items, string $itemType = 'Thing'): array
    {
        $elements = [];

        foreach (array_values($items) as $item) {
            $itemUrl = trim((string) ($item['link'] ?? $item['url'] ?? ''));
            $itemName = trim((string) ($item['title'] ?? $item['name'] ?? ''));

            if ($itemUrl === '' || $itemName === '') {
                continue;
            }

            $entity = [
                '@type' => $itemType,
                'name' => $itemName,
                'url' => $itemUrl,
            ];

            $image = trim((string) ($item['image'] ?? ''));
            if ($image !== '') {
                $entity['image'] = $this->absoluteUrl($image);
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => count($elements) + 1,
                'url' => $itemUrl,
                'item' => $entity,
            ];
        }

        if ($elements === []) {
            return [];
        }

        return [
            '@type' => 'ItemList',
            '@id' => $url . '#itemlist-' . substr(sha1($name), 0, 10),
            'name' => $name,
            'url' => $url,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }

    /**
     * @param array<string, mixed> $blog
     */
    public function articleSchema(array $blog, string $url): array
    {
        $title = trim((string) ($blog['title'] ?? ''));
        if ($title === '') {
            return [];
        }

        $description = $this->excerpt(
            (string) ($blog['meta_description'] ?? $blog['excerpt'] ?? $blog['content'] ?? ''),
            220
        );
        $publishedAt = $this->formatIsoDate((string) ($blog['published_at'] ?? ''));
        $updatedAt = $this->formatIsoDate((string) ($blog['updated_at'] ?? '')) ?: $publishedAt;
        $author = trim((string) ($blog['author'] ?? 'Travel Plus')) ?: 'Travel Plus';

        return $this->withoutEmptyValues([
            '@type' => 'BlogPosting',
            '@id' => $url . '#article',
            'headline' => $title,
            'description' => $description,
            'image' => [$this->absoluteUrl((string) ($blog['image'] ?? base_url('assets/images/TravelPlus_CompanyProfile.png')))],
            'datePublished' => $publishedAt,
            'dateModified' => $updatedAt,
            'author' => [
                '@type' => $author === 'Travel Plus' ? 'Organization' : 'Person',
                'name' => $author,
            ],
            'publisher' => [
                '@id' => base_url('/#organization'),
            ],
            'mainEntityOfPage' => [
                '@id' => $url . '#webpage',
            ],
            'url' => $url,
        ]);
    }

    public function serviceSchema(string $name, string $description, string $url, string $image = '', array $serviceTypes = []): array
    {
        $schema = [
            '@type' => 'Service',
            '@id' => $url . '#service',
            'name' => $name,
            'description' => $this->excerpt($description, 260),
            'url' => $url,
            'provider' => [
                '@id' => base_url('/#organization'),
            ],
            'areaServed' => [
                [
                    '@type' => 'Country',
                    'name' => 'Vietnam',
                ],
                [
                    '@type' => 'Place',
                    'name' => 'International',
                ],
            ],
        ];

        if ($image !== '') {
            $schema['image'] = $this->absoluteUrl($image);
        }

        $serviceTypes = array_values(array_filter(array_map(
            static fn($value): string => trim((string) $value),
            $serviceTypes
        )));
        if ($serviceTypes !== []) {
            $schema['serviceType'] = $serviceTypes;
        }

        return $this->withoutEmptyValues($schema);
    }

    /**
     * @param array<int, array<string, mixed>> $faqs
     */
    public function faqSchema(array $faqs): array
    {
        $items = [];

        foreach ($faqs as $faq) {
            $question = trim((string) ($faq['question'] ?? ''));
            $answer = $this->excerpt((string) ($faq['answer'] ?? ''), 600);

            if ($question === '' || $answer === '') {
                continue;
            }

            $items[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($items === []) {
            return [];
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => $items,
        ];
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return base_url('assets/images/TravelPlus_CompanyProfile.png');
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return base_url(ltrim($url, '/'));
    }

    private function formatIsoDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date(DATE_ATOM, $timestamp);
    }

    private function withoutEmptyValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->withoutEmptyValues($value);
            }

            if ($value === '' || $value === [] || $value === null) {
                unset($data[$key]);
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
