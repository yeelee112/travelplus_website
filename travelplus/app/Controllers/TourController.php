<?php

namespace App\Controllers;

use App\Services\EntityViewService;
use App\Services\SeoService;
use App\Services\TourCatalogService;
use App\Services\TourEnquiryNotificationService;
use CodeIgniter\Exceptions\PageNotFoundException;

class TourController extends BaseController
{
    public function featured()
    {
        $tours = $this->getFeaturedTours();

        return view('sections/featured-tour', ['tours' => $tours]);
    }

    public function homeTour()
    {
        $tourService = new TourCatalogService();
        $tours = $tourService->getHomeTours($this->request->getLocale(), 6);

        return view('sections/home-tour', ['tours' => $tours]);
    }

    public function detail(string $tourType, string $locale, string $locationSlug, string $tourSlug)
    {
        $tourService = new TourCatalogService();
        $seo = new SeoService();
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $tour = $tourService->findTourBySlug($locale, $tourSlug, $tourType);

        if ($tour === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        (new EntityViewService())->incrementOncePerSession('tours', (int) ($tour['id'] ?? 0), 'tour');

        $listPath = $tourType === 'inbound' ? 'tour-trong-nuoc' : 'tour-nuoc-ngoai';
        $listLabel = $tourType === 'inbound'
            ? $t('common.domesticTours')
            : $t('common.outboundTours');
        $canonicalUrl = current_url();
        $metaTitle = trim((string) ($tour['meta_title'] ?? '')) ?: (string) $tour['title'];
        if ($metaTitle !== '' && stripos($metaTitle, 'Travel Plus') === false) {
            $metaTitle .= ' | Travel Plus';
        }
        $metaDesc = $seo->excerpt(
            trim((string) ($tour['meta_description'] ?? '')) !== ''
                ? (string) $tour['meta_description']
                : (string) ($tour['short_description'] ?? $tour['overview'] ?? $tour['description'] ?? ''),
            160
        );
        $breadcrumbs = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $listLabel, 'url' => localized_url($listPath)],
        ];
        $continentLabel = trim((string) ($tour['continent'] ?? ''));

        if ($continentLabel !== '') {
            $continentUrl = trim((string) ($tour['continent_link'] ?? ''));
            $continentCrumb = ['label' => $continentLabel];

            if ($continentUrl !== '') {
                $continentCrumb['url'] = $continentUrl;
            }

            $breadcrumbs[] = $continentCrumb;
        }

        $breadcrumbs[] = ['label' => (string) $tour['title']];

        return view('tour/index', [
            'tour' => $tour,
            'relatedTours' => $tourService->getRelatedTours($locale, $tour, 6),
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'canonical_url' => $canonicalUrl,
            'meta_image' => (string) ($tour['image'] ?? base_url('assets/images/TravelPlus_CompanyProfile.png')),
            'meta_image_alt' => (string) ($tour['title'] ?? 'Travel Plus tour'),
            'meta_updated_time' => (string) ($tour['updated_at'] ?? $tour['created_at'] ?? ''),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => switch_locale_url('vi')],
                ['hreflang' => 'en', 'href' => switch_locale_url('en')],
                ['hreflang' => 'x-default', 'href' => switch_locale_url('vi')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($metaTitle, $metaDesc, $canonicalUrl),
                $seo->tourSchema($tour, $canonicalUrl),
                $seo->faqSchema((array) ($tour['faqs'] ?? [])),
            ],
        ]);
    }

    public function submitReview()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $rules = [
            'tour_id' => 'required|is_natural_no_zero',
            'reviewer_name' => 'required|min_length[2]|max_length[255]',
            'reviewer_email' => 'permit_empty|valid_email|max_length[255]',
            'content' => 'permit_empty|max_length[5000]',
            'rating_overall' => 'required|decimal|greater_than[0]|less_than_equal_to[5]',
            'rating_destination' => 'required|decimal|greater_than[0]|less_than_equal_to[5]',
            'rating_transport' => 'required|decimal|greater_than[0]|less_than_equal_to[5]',
            'rating_value' => 'required|decimal|greater_than[0]|less_than_equal_to[5]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => lang('Frontend.tour.review.invalid', [], $locale),
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => lang('Frontend.tour.review.tableMissing', [], $locale),
            ]);
        }

        $post = $this->request->getPost();
        $now = date('Y-m-d H:i:s');

        $db->table('tour_reviews')->insert([
            'tour_id' => (int) $post['tour_id'],
            'reviewer_name' => trim((string) $post['reviewer_name']),
            'reviewer_email' => trim((string) ($post['reviewer_email'] ?? '')) ?: null,
            'rating_overall' => (float) $post['rating_overall'],
            'rating_destination' => (float) $post['rating_destination'],
            'rating_transport' => (float) $post['rating_transport'],
            'rating_value' => (float) $post['rating_value'],
            'content' => trim((string) ($post['content'] ?? '')),
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Frontend.tour.review.success', [], $locale),
        ]);
    }

    public function submitEnquiry()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $rules = [
            'tour_id' => 'required|is_natural_no_zero',
            'tour_title' => 'required|max_length[255]',
            'tour_link' => 'permit_empty|max_length[500]',
            'full_name' => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email|max_length[255]',
            'phone' => 'required|min_length[8]|max_length[30]',
            'travel_date' => 'permit_empty|max_length[50]',
            'travelers' => 'permit_empty|max_length[50]',
            'message' => 'required|min_length[10]|max_length[3000]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => lang('Frontend.tour.enquiry.invalid', [], $locale),
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $post = $this->request->getPost();
        $enquiry = [
            'tour_id' => (int) $post['tour_id'],
            'tour_title' => trim((string) $post['tour_title']),
            'tour_link' => trim((string) ($post['tour_link'] ?? '')),
            'full_name' => trim((string) $post['full_name']),
            'email' => trim((string) $post['email']),
            'phone' => trim((string) $post['phone']),
            'travel_date' => trim((string) ($post['travel_date'] ?? '')),
            'travelers' => trim((string) ($post['travelers'] ?? '')),
            'message' => trim((string) ($post['message'] ?? '')),
        ];

        $notificationService = new TourEnquiryNotificationService();
        if (! $notificationService->sendEnquiryEmails($enquiry)) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => lang('Frontend.tour.enquiry.mailFailed', [], $locale),
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => lang('Frontend.tour.enquiry.success', [], $locale),
        ]);
    }

    private function getFeaturedTours(int $limit = 6): array
    {
        $tourService = new TourCatalogService();

        return $tourService->getHomeTours($this->request->getLocale(), $limit);
    }
}
