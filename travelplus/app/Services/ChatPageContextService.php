<?php

namespace App\Services;

class ChatPageContextService
{
    public function resolve(string $locale, string $url): array
    {
        $route = $this->parseTourRoute($url);
        if ($route === []) {
            return [];
        }
        $tour = (new TourCatalogService())->findTourBySlug($locale, $route['slug'], $route['type']);
        if ($tour === null || empty($tour['title'])) {
            return [];
        }
        return ['title' => (string) $tour['title'], 'url' => (string) ($tour['url'] ?? ''),
            'slug' => $route['slug'], 'type' => $route['type']];
    }

    public function parseTourRoute(string $url): array
    {
        $parts = parse_url($url);
        if (! is_array($parts) || isset($parts['user']) || isset($parts['pass'])) {
            return [];
        }
        if (isset($parts['host'])) {
            $hosts = array_filter([parse_url(config('App')->baseURL, PHP_URL_HOST), 'travelplusvn.com', 'www.travelplusvn.com']);
            if (! in_array(strtolower($parts['host']), $hosts, true)
                || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
                return [];
            }
        }
        $path = $parts['path'] ?? '';
        if (preg_match('~^/(?:en/)?tour-nuoc-ngoai/[a-z0-9-]+/([a-z0-9-]+)/?$~D', $path, $match)) {
            return ['type' => 'outbound', 'slug' => $match[1]];
        }
        if (preg_match('~^/(?:en/)?tour-trong-nuoc/[a-z0-9-]+/tour/([a-z0-9-]+)/?$~D', $path, $match)) {
            return ['type' => 'inbound', 'slug' => $match[1]];
        }
        return [];
    }
}
