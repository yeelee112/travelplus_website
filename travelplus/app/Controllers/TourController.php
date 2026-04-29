<?php
namespace App\Controllers;

use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class TourController extends BaseController
{
    public function preview()
    {
        return view('tour/index', [
            'featuredTours' => $this->getFeaturedTours(),
        ]);
    }

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
        $tour = $tourService->findTourBySlug($locale, $tourSlug, $tourType);

        if ($tour === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $listPath = $tourType === 'inbound' ? 'tour-trong-nuoc' : 'tour-nuoc-ngoai';
        $listLabel = $tourType === 'inbound'
            ? ($locale === 'en' ? 'Domestic Tours' : 'Tour trong nuoc')
            : ($locale === 'en' ? 'Outbound Tours' : 'Tour nuoc ngoai');

        return view('tour/index', [
            'tour' => $tour,
            'relatedTours' => $tourService->getRelatedTours($locale, $tour, 6),
            'breadcrumbs' => [
                ['label' => $locale === 'en' ? 'Home' : 'Trang chu', 'url' => localized_url('/')],
                ['label' => $listLabel, 'url' => localized_url($listPath)],
                ['label' => (string) ($tour['continent'] ?? ''), 'url' => (string) ($tour['continent_link'] ?? '')],
                ['label' => (string) $tour['title']],
            ],
        ]);
    }

    public function submitReview()
    {
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
                'message' => 'Dữ liệu review chưa hợp lệ.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Bảng tour_reviews chưa tồn tại.',
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
            'message' => 'Đã gửi đánh giá. Review sẽ hiển thị sau khi được duyệt.',
        ]);
    }

    private function getFeaturedTours(int $limit = 6): array
    {
        $tourService = new TourCatalogService();

        return $tourService->getHomeTours($this->request->getLocale(), $limit);
    }
}
