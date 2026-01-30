<?php

if (! function_exists('seo_meta')) {
    /**
     * Build basic SEO meta tags and OpenGraph tags from data array
     * Usage: echo seo_meta(['title' => '...', 'description' => '...', 'image' => '/path.jpg', 'canonical' => '...']);
     */
    function seo_meta(array $data = []): string
    {
        $title = esc($data['title'] ?? setting('App.siteName') ?? 'TravelPlus');
        $desc = esc($data['description'] ?? 'Explore the world with TravelPlus');
        $image = esc($data['image'] ?? base_url('public/assets/images/og-default.jpg'));
        $canonical = esc($data['canonical'] ?? current_url());
        $locale = service('request')->getLocale() ?? config('App')->defaultLocale;

        $meta = "<title>{$title}</title>\n";
        $meta .= "<meta name=\"description\" content=\"{$desc}\" />\n";
        $meta .= "<link rel=\"canonical\" href=\"{$canonical}\" />\n";
        $meta .= "<meta property=\"og:title\" content=\"{$title}\" />\n";
        $meta .= "<meta property=\"og:description\" content=\"{$desc}\" />\n";
        $meta .= "<meta property=\"og:image\" content=\"{$image}\" />\n";
        $meta .= "<meta property=\"og:url\" content=\"{$canonical}\" />\n";
        $meta .= "<meta property=\"og:locale\" content=\"{$locale}\" />\n";
        $meta .= "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";

        return $meta;
    }
}
