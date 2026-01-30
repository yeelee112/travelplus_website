<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Sitemap extends Controller
{
    public function index()
    {
        // Build simple XML sitemap for home page localized
        $locales = ['en', 'vi'];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($locales as $loc) {
            $url = $xml->addChild('url');
            $url->addChild('loc', esc(site_url($loc), 'xml')); 
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.9');
        }

        return $this->response->setContentType('application/xml')->setBody($xml->asXML());
    }
}
