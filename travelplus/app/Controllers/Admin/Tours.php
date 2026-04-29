<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Tours extends BaseController
{
    public function create()
    {
        return view('admin/tours/create', [
            'categories' => $this->getCategories(),
            'locations' => $this->getLocations(),
            'continents' => $this->getLocationsByType('continent'),
            'countries' => $this->getLocationsByType('country'),
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function store()
    {
        $rules = [
            'category_id' => 'required|is_natural_no_zero',
            'departure_location_id' => 'required|is_natural_no_zero',
            'tour_type' => 'required|in_list[inbound,outbound]',
            'duration_days' => 'required|is_natural_no_zero',
            'duration_nights' => 'required|is_natural',
            'name_vi' => 'required|min_length[3]',
            'slug_vi' => 'required|min_length[3]',
            'name_en' => 'permit_empty|min_length[3]',
            'departure_date' => 'permit_empty|valid_date[Y-m-d]',
            'price' => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();
        $now = date('Y-m-d H:i:s');
        $post = $this->request->getPost();

        $db->transStart();

        $tourId = $this->insertTour($db, $post, $now);
        $this->insertTourTranslations($db, $tourId, $post);
        $this->insertTourDestinations($db, $tourId, $post);
        $this->insertTourDeparture($db, $tourId, $post, $now);
        $this->insertTourMedia($db, $tourId, $post, $now);
        $this->insertTourItinerary($db, $tourId, $post, $now);
        $this->insertTourFaqs($db, $tourId, $post, $now);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('errors', ['Không thể lưu tour. Vui lòng kiểm tra dữ liệu.']);
        }

        return redirect()->to(site_url('admin/tours/create'))->with('success', 'Đã tạo tour #' . $tourId);
    }

    private function insertTour($db, array $post, string $now): int
    {
        $fields = $db->getFieldNames('tours');
        $data = [
            'category_id' => (int) $post['category_id'],
            'departure_location_id' => (int) $post['departure_location_id'],
            'tour_type' => $post['tour_type'],
            'duration_days' => (int) $post['duration_days'],
            'duration_nights' => (int) $post['duration_nights'],
            'thumbnail' => trim((string) ($post['thumbnail'] ?? '')),
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
            'status' => $post['status'] ?? 'draft',
            'created_at' => $now,
            'updated_at' => $now,
            'sku' => trim((string) ($post['sku'] ?? '')),
            'code' => trim((string) ($post['code'] ?? '')),
            'min_travelers' => $this->nullableInt($post['min_travelers'] ?? null),
            'max_travelers' => $this->nullableInt($post['max_travelers'] ?? null),
            'base_price' => $this->nullableInt($post['base_price'] ?? null),
            'sale_price' => $this->nullableInt($post['sale_price'] ?? null),
            'currency' => 'VND',
            'primary_destination_id' => $this->nullableInt($post['primary_destination_id'] ?? null),
            'map_embed' => trim((string) ($post['map_embed'] ?? '')),
        ];

        $data = array_intersect_key($data, array_flip($fields));
        $db->table('tours')->insert($data);

        return (int) $db->insertID();
    }

    private function insertTourTranslations($db, int $tourId, array $post): void
    {
        $fields = $db->getFieldNames('tour_translations');
        $locales = [
            'vi' => [
                'name' => trim((string) $post['name_vi']),
                'slug' => trim((string) $post['slug_vi']),
                'short_description' => trim((string) ($post['short_description_vi'] ?? '')),
                'description' => trim((string) ($post['description_vi'] ?? '')),
                'itinerary' => trim((string) ($post['itinerary_vi'] ?? '')),
                'meta_title' => trim((string) ($post['meta_title_vi'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_vi'] ?? '')),
                'overview' => trim((string) ($post['overview_vi'] ?? '')),
                'booking_policy' => trim((string) ($post['booking_policy_vi'] ?? '')),
                'cancellation_policy' => trim((string) ($post['cancellation_policy_vi'] ?? '')),
                'price_note' => trim((string) ($post['price_note_vi'] ?? '')),
            ],
            'en' => [
                'name' => trim((string) ($post['name_en'] ?: $post['name_vi'])),
                'slug' => trim((string) ($post['slug_en'] ?: $post['slug_vi'])),
                'short_description' => trim((string) ($post['short_description_en'] ?? '')),
                'description' => trim((string) ($post['description_en'] ?? '')),
                'itinerary' => trim((string) ($post['itinerary_en'] ?? '')),
                'meta_title' => trim((string) ($post['meta_title_en'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_en'] ?? '')),
                'overview' => trim((string) ($post['overview_en'] ?? '')),
                'booking_policy' => trim((string) ($post['booking_policy_en'] ?? '')),
                'cancellation_policy' => trim((string) ($post['cancellation_policy_en'] ?? '')),
                'price_note' => trim((string) ($post['price_note_en'] ?? '')),
            ],
        ];

        foreach ($locales as $locale => $data) {
            $data['tour_id'] = $tourId;
            $data['locale'] = $locale;
            $db->table('tour_translations')->insert(array_intersect_key($data, array_flip($fields)));
        }
    }

    private function insertTourDestinations($db, int $tourId, array $post): void
    {
        $destinationIds = array_filter(array_map('intval', (array) ($post['destination_ids'] ?? [])));
        $destinationRows = (array) ($post['destinations'] ?? []);

        foreach ($destinationRows as $row) {
            $countryId = (int) ($row['country_id'] ?? 0);

            if ($countryId <= 0 && ! empty($row['new_country_name_vi']) && ! empty($row['continent_id'])) {
                $countryId = $this->createCountryLocation($db, $row);
            }

            if ($countryId > 0) {
                $destinationIds[] = $countryId;
            }
        }

        foreach (array_unique($destinationIds) as $locationId) {
            $db->table('tour_destinations')->insert([
                'tour_id' => $tourId,
                'location_id' => $locationId,
            ]);
        }
    }

    private function createCountryLocation($db, array $row): int
    {
        $now = date('Y-m-d H:i:s');
        $continentId = (int) $row['continent_id'];
        $nameVi = trim((string) ($row['new_country_name_vi'] ?? ''));
        $nameEn = trim((string) ($row['new_country_name_en'] ?? $nameVi));
        $slugVi = trim((string) ($row['new_country_slug_vi'] ?? '')) ?: $this->slugify($nameVi);
        $slugEn = trim((string) ($row['new_country_slug_en'] ?? '')) ?: $this->slugify($nameEn);
        $code = strtoupper(trim((string) ($row['new_country_code'] ?? '')));

        $db->table('locations')->insert([
            'parent_id' => $continentId,
            'type' => 'country',
            'code' => $code ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $locationId = (int) $db->insertID();

        $db->table('location_translations')->insert([
            'location_id' => $locationId,
            'locale' => 'vi',
            'name' => $nameVi,
            'slug' => $slugVi,
        ]);

        $db->table('location_translations')->insert([
            'location_id' => $locationId,
            'locale' => 'en',
            'name' => $nameEn,
            'slug' => $slugEn,
        ]);

        return $locationId;
    }

    private function insertTourMedia($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_media')) {
            return;
        }

        $mediaFiles = $this->request->getFileMultiple('media_files') ?? [];

        foreach ((array) ($post['media'] ?? []) as $index => $row) {
            $filePath = trim((string) ($row['file_path'] ?? ''));
            $file = $mediaFiles[$index] ?? null;
            $type = $row['type'] ?? 'gallery';

            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $filePath = $this->storeTourMediaFile($tourId, $type, $file);
            }

            if ($filePath === '') {
                continue;
            }

            $db->table('tour_media')->insert([
                'tour_id' => $tourId,
                'type' => $type,
                'file_path' => $filePath,
                'alt_text' => trim((string) ($row['alt_text'] ?? '')),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function storeTourMediaFile(int $tourId, string $type, $file): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower($file->getClientExtension());
        $mimeType = $file->getMimeType();

        if (! in_array($extension, $allowedExtensions, true) || ! in_array($mimeType, $allowedMimeTypes, true)) {
            return '';
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return '';
        }

        $safeType = in_array($type, ['cover', 'gallery', 'banner', 'video'], true) ? $type : 'gallery';
        $relativeDir = 'uploads/tours/' . $tourId . '/' . $safeType;
        $absoluteDir = FCPATH . $relativeDir;

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $fileName = 'tour-' . $tourId . '-' . $safeType . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($absoluteDir, $fileName);

        return $relativeDir . '/' . $fileName;
    }

    private function insertTourItinerary($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_itinerary_days') || ! $db->tableExists('tour_itinerary_day_translations')) {
            return;
        }

        foreach ((array) ($post['itinerary_days'] ?? []) as $index => $row) {
            $titleVi = trim((string) ($row['title_vi'] ?? ''));
            $descriptionVi = $this->sanitizeRichHtml((string) ($row['description_vi'] ?? ''));

            if ($titleVi === '' && $descriptionVi === '') {
                continue;
            }

            $db->table('tour_itinerary_days')->insert([
                'tour_id' => $tourId,
                'day_number' => (int) ($row['day_number'] ?? ($index + 1)),
                'meals' => trim((string) ($row['meals'] ?? '')),
                'hotel_name' => trim((string) ($row['hotel_name'] ?? '')),
                'transport_summary' => trim((string) ($row['transport_summary'] ?? '')),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $dayId = (int) $db->insertID();

            $db->table('tour_itinerary_day_translations')->insert([
                'itinerary_day_id' => $dayId,
                'locale' => 'vi',
                'title' => $titleVi ?: 'Ngày ' . ($index + 1),
                'description' => $descriptionVi,
            ]);

            $titleEn = trim((string) ($row['title_en'] ?? '')) ?: $titleVi;
            $descriptionEn = $this->sanitizeRichHtml((string) ($row['description_en'] ?? '')) ?: $descriptionVi;

            $db->table('tour_itinerary_day_translations')->insert([
                'itinerary_day_id' => $dayId,
                'locale' => 'en',
                'title' => $titleEn,
                'description' => $descriptionEn,
            ]);
        }
    }

    private function insertTourFaqs($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_faqs') || ! $db->tableExists('tour_faq_translations')) {
            return;
        }

        foreach ((array) ($post['faqs'] ?? []) as $index => $row) {
            $questionVi = trim((string) ($row['question_vi'] ?? ''));
            $answerVi = trim((string) ($row['answer_vi'] ?? ''));

            if ($questionVi === '' && $answerVi === '') {
                continue;
            }

            $db->table('tour_faqs')->insert([
                'tour_id' => $tourId,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $faqId = (int) $db->insertID();

            $db->table('tour_faq_translations')->insert([
                'faq_id' => $faqId,
                'locale' => 'vi',
                'question' => $questionVi,
                'answer' => $answerVi,
            ]);

            $db->table('tour_faq_translations')->insert([
                'faq_id' => $faqId,
                'locale' => 'en',
                'question' => trim((string) ($row['question_en'] ?? '')) ?: $questionVi,
                'answer' => trim((string) ($row['answer_en'] ?? '')) ?: $answerVi,
            ]);
        }
    }

    private function insertTourDeparture($db, int $tourId, array $post, string $now): void
    {
        if (empty($post['departure_date'])) {
            return;
        }

        $fields = $db->getFieldNames('tour_departures');
        $data = [
            'tour_id' => $tourId,
            'departure_date' => $post['departure_date'],
            'available_slots' => $this->nullableInt($post['available_slots'] ?? null),
            'price' => (int) ($post['price'] ?? 0),
            'price_up' => $this->nullableInt($post['price_up'] ?? null),
            'status' => $post['departure_status'] ?? 'open',
            'created_at' => $now,
        ];

        $db->table('tour_departures')->insert(array_intersect_key($data, array_flip($fields)));
    }

    private function sanitizeRichHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<div\b[^>]*>/i', '<p>', $html) ?? '';
        $html = str_ireplace('</div>', '</p>', $html);
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li>');
        $html = preg_replace('/<([a-z][a-z0-9]*)(?:\s[^>]*)?>/i', '<$1>', $html) ?? '';
        $html = str_ireplace(['<b>', '</b>', '<i>', '</i>'], ['<strong>', '</strong>', '<em>', '</em>'], $html);

        return trim($html);
    }

    private function getCategories(): array
    {
        return db_connect()->table('tour_categories tc')
            ->select('tc.id, tc.type, COALESCE(tct_vi.name, tct_en.name, CONCAT("Category #", tc.id)) AS name')
            ->join('tour_category_translations tct_vi', 'tct_vi.category_id = tc.id AND tct_vi.locale = "vi"', 'left')
            ->join('tour_category_translations tct_en', 'tct_en.category_id = tc.id AND tct_en.locale = "en"', 'left')
            ->orderBy('tc.type', 'ASC')
            ->orderBy('tc.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getLocations(): array
    {
        return db_connect()->table('locations l')
            ->select('l.id, l.type, l.parent_id, COALESCE(lt_vi.name, lt_en.name, CONCAT("Location #", l.id)) AS name')
            ->join('location_translations lt_vi', 'lt_vi.location_id = l.id AND lt_vi.locale = "vi"', 'left')
            ->join('location_translations lt_en', 'lt_en.location_id = l.id AND lt_en.locale = "en"', 'left')
            ->orderBy('l.type', 'ASC')
            ->orderBy('l.parent_id', 'ASC')
            ->orderBy('l.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getLocationsByType(string $type): array
    {
        return db_connect()->table('locations l')
            ->select('l.id, l.type, l.parent_id, l.code, COALESCE(lt_vi.name, lt_en.name, CONCAT("Location #", l.id)) AS name')
            ->join('location_translations lt_vi', 'lt_vi.location_id = l.id AND lt_vi.locale = "vi"', 'left')
            ->join('location_translations lt_en', 'lt_en.location_id = l.id AND lt_en.locale = "en"', 'left')
            ->where('l.type', $type)
            ->orderBy('l.parent_id', 'ASC')
            ->orderBy('l.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'location-' . time();
    }
}
