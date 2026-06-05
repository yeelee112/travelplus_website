<?php

namespace App\Controllers\Admin;

use App\Services\DomesticRegionService;

class Tours extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $tours = db_connect()->table('tours t')
            ->select('t.id, t.tour_type, t.status, t.base_price, t.view_count, t.updated_at, COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", t.id)) AS name')
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
            ->join('tour_translations tt_en', 'tt_en.tour_id = t.id AND tt_en.locale = "en"', 'left')
            ->orderBy('t.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/tours/index', [
            'tours' => $tours,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->renderForm([
            'pageTitle' => 'Create tour',
            'pageDesc' => 'Internal form for inserting tour data without writing SQL.',
            'formAction' => site_url('admin/tours'),
            'submitLabel' => 'Save tour',
            'tourId' => null,
            'formData' => $this->defaultFormData(),
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->saveTour();
    }

    public function edit(int $tourId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $formData = $this->loadTourFormData($tourId);

        if ($formData === null) {
            return redirect()->to(site_url('admin/tours'))->with('error', 'Không tìm thấy tour.');
        }

        return $this->renderForm([
            'pageTitle' => 'Edit tour #' . $tourId,
            'pageDesc' => 'Update tour content, itinerary, media and booking data.',
            'formAction' => site_url('admin/tours/' . $tourId),
            'submitLabel' => 'Update tour',
            'tourId' => $tourId,
            'formData' => $formData,
        ]);
    }

    public function update(int $tourId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        if ($this->loadTourFormData($tourId) === null) {
            return redirect()->to(site_url('admin/tours'))->with('error', 'Không tìm thấy tour.');
        }

        return $this->saveTour($tourId);
    }

    public function delete(int $tourId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();
        $tour = $db->table('tours')->where('id', $tourId)->get()->getRowArray();

        if (! is_array($tour)) {
            return redirect()->to(site_url('admin/tours'))->with('error', 'Không tìm thấy tour.');
        }

        $db->transStart();
        $this->deleteTourRelations($db, $tourId);
        $db->table('tours')->where('id', $tourId)->delete();
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(site_url('admin/tours'))->with('error', 'Không thể xóa tour lúc này.');
        }

        $this->deleteDirectory($this->tourUploadDirectory($tourId));

        return redirect()->to(site_url('admin/tours'))->with('success', 'Đã xóa tour #' . $tourId . '.');
    }

    /**
     * @param array<string, mixed> $viewData
     */
    private function renderForm(array $viewData)
    {
        $domesticRegionService = new DomesticRegionService();
        $domesticMenu = $domesticRegionService->getMenu('vi');
        $domesticRegions = [];
        $domesticProvincesByRegion = [];

        foreach ($domesticMenu as $key => $region) {
            $domesticRegions[] = [
                'key' => $key,
                'name' => $region['name'] ?? $key,
            ];
            $domesticProvincesByRegion[$key] = array_values($region['provinces'] ?? []);
        }

        $countries = $this->getLocationsByType('country');
        $countriesByParent = [];
        foreach ($countries as $country) {
            $countriesByParent[(int) $country['parent_id']][] = $country;
        }

        return view('admin/tours/form', array_merge($viewData, [
            'categories' => $this->getCategories(),
            'locations' => $this->getLocations(),
            'continents' => $this->getLocationsByType('continent'),
            'countries' => $countries,
            'provinces' => $this->getLocationsByType('province'),
            'countriesByParent' => $countriesByParent,
            'domesticRegions' => $domesticRegions,
            'domesticProvincesByRegion' => $domesticProvincesByRegion,
            'errors' => session()->getFlashdata('errors') ?? [],
            'success' => session()->getFlashdata('success'),
        ]));
    }

    private function deleteTourRelations($db, int $tourId): void
    {
        if ($db->tableExists('tour_translations')) {
            $db->table('tour_translations')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_destinations')) {
            $db->table('tour_destinations')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_departures')) {
            $db->table('tour_departures')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_media')) {
            $db->table('tour_media')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_reviews')) {
            $db->table('tour_reviews')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_faqs')) {
            $faqIds = $db->table('tour_faqs')->select('id')->where('tour_id', $tourId)->get()->getResultArray();
            $ids = array_map(static fn(array $row): int => (int) $row['id'], $faqIds);
            if ($ids !== [] && $db->tableExists('tour_faq_translations')) {
                $db->table('tour_faq_translations')->whereIn('faq_id', $ids)->delete();
            }
            $db->table('tour_faqs')->where('tour_id', $tourId)->delete();
        }

        if ($db->tableExists('tour_itinerary_days')) {
            $dayIds = $db->table('tour_itinerary_days')->select('id')->where('tour_id', $tourId)->get()->getResultArray();
            $ids = array_map(static fn(array $row): int => (int) $row['id'], $dayIds);
            if ($ids !== [] && $db->tableExists('tour_itinerary_day_translations')) {
                $db->table('tour_itinerary_day_translations')->whereIn('itinerary_day_id', $ids)->delete();
            }
            $db->table('tour_itinerary_days')->where('tour_id', $tourId)->delete();
        }
    }

    private function tourUploadDirectory(int $tourId): string
    {
        return rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'tours'
            . DIRECTORY_SEPARATOR . $tourId;
    }

    private function deleteDirectory(string $directory): void
    {
        if ($directory === '' || ! is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            if (is_file($path)) {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }

    private function deleteRelativeFile(string $relativePath, string $allowedPrefix): void
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath));
        $allowedPrefix = trim(str_replace('\\', '/', $allowedPrefix));
        $absolutePath = $this->resolveManagedFilePath($relativePath, $allowedPrefix);

        if ($absolutePath !== null && is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function resolveManagedFilePath(string $relativePath, string $allowedPrefix): ?string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return null;
        }

        $allowedPrefix = rtrim($allowedPrefix, '/') . '/';
        if (! str_starts_with($relativePath, $allowedPrefix)) {
            return null;
        }

        $absolutePath = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $candidate = realpath($absolutePath);
        $root = realpath(rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, trim($allowedPrefix, '/')));

        if ($candidate === false || $root === false) {
            return null;
        }

        $candidate = str_replace('\\', '/', $candidate);
        $root = rtrim(str_replace('\\', '/', $root), '/') . '/';

        return str_starts_with($candidate, $root) ? $candidate : null;
    }

    private function saveTour(?int $tourId = null)
    {
        $isUpdate = $tourId !== null;
        $rules = [
            'category_id' => 'required|is_natural_no_zero',
            'departure_location_id' => 'required|is_natural_no_zero',
            'tour_type' => 'required|in_list[inbound,outbound]',
            'duration_days' => 'required|is_natural_no_zero',
            'duration_nights' => 'required|is_natural',
            'name_vi' => 'required|min_length[3]',
            'slug_vi' => 'permit_empty|min_length[3]',
            'name_en' => 'permit_empty|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $departureErrors = $this->validateDepartureRows($post);
        if ($departureErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $departureErrors);
        }

        $db = db_connect();
        $oldTour = $tourId === null ? null : $db->table('tours')->where('id', $tourId)->get()->getRowArray();
        $oldThumbnail = trim((string) ($oldTour['thumbnail'] ?? ''));
        $oldMediaPaths = [];
        if ($tourId !== null && $db->tableExists('tour_media')) {
            $oldMediaPaths = array_map(
                static fn(array $row): string => trim((string) ($row['file_path'] ?? '')),
                $db->table('tour_media')->select('file_path')->where('tour_id', $tourId)->get()->getResultArray()
            );
            $oldMediaPaths = array_values(array_filter($oldMediaPaths));
        }
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $tourId = $this->persistTour($db, $tourId, $post, $now);
        $this->replaceTourTranslations($db, $tourId, $post);
        $this->replaceTourDestinations($db, $tourId, $post);
        $this->replaceTourDepartures($db, $tourId, $post, $now);
        $this->replaceTourMedia($db, $tourId, $post, $now);
        $this->replaceTourItinerary($db, $tourId, $post, $now);
        $this->replaceTourFaqs($db, $tourId, $post, $now);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('errors', ['Không thể lưu tour. Vui lòng kiểm tra dữ liệu.']);
        }

        $currentTour = $db->table('tours')->select('thumbnail')->where('id', $tourId)->get()->getRowArray() ?: [];
        $currentThumbnail = trim((string) ($currentTour['thumbnail'] ?? ''));
        if ($oldThumbnail !== '' && $oldThumbnail !== $currentThumbnail) {
            $this->deleteRelativeFile($oldThumbnail, 'uploads/tours/');
        }

        if ($db->tableExists('tour_media')) {
            $newMediaPaths = array_map(
                static fn(array $row): string => trim((string) ($row['file_path'] ?? '')),
                $db->table('tour_media')->select('file_path')->where('tour_id', $tourId)->get()->getResultArray()
            );
            $newMediaPaths = array_values(array_filter($newMediaPaths));

            foreach (array_diff($oldMediaPaths, $newMediaPaths) as $unusedPath) {
                $this->deleteRelativeFile((string) $unusedPath, 'uploads/tours/');
            }
        }

        return redirect()->to(site_url('admin/tours/' . $tourId . '/edit'))
            ->with('success', ($isUpdate ? 'Đã cập nhật' : 'Đã tạo') . ' tour #' . $tourId);
    }

    private function persistTour($db, ?int $tourId, array $post, string $now): int
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
            'is_promotion' => isset($post['is_promotion']) ? 1 : 0,
            'promotion_badge' => trim((string) ($post['promotion_badge'] ?? '')),
            'promotion_ends_at' => $this->nullableDateTime($post['promotion_ends_at'] ?? null),
            'promotion_sort' => (int) ($post['promotion_sort'] ?? 0),
            'status' => $post['status'] ?? 'draft',
            'updated_at' => $now,
            'sku' => trim((string) ($post['sku'] ?? '')),
            'code' => trim((string) ($post['code'] ?? '')),
            'min_travelers' => $this->nullableInt($post['min_travelers'] ?? null),
            'max_travelers' => $this->nullableInt($post['max_travelers'] ?? null),
            'base_price' => $this->nullableInt($post['base_price'] ?? null),
            'sale_price' => $this->nullableInt($post['sale_price'] ?? null),
            'child_price_rate' => $this->normalizeTravelerPriceRate($post['child_price_rate'] ?? null, 0.85),
            'infant_price_rate' => $this->normalizeTravelerPriceRate($post['infant_price_rate'] ?? null, 0.25),
            'currency' => 'VND',
            'primary_destination_id' => $this->nullableInt($post['primary_destination_id'] ?? null),
            'map_embed' => trim((string) ($post['map_embed'] ?? '')),
        ];

        $data = array_intersect_key($data, array_flip($fields));

        if ($tourId === null) {
            $data['created_at'] = $now;
            $db->table('tours')->insert($data);

            return (int) $db->insertID();
        }

        $db->table('tours')->where('id', $tourId)->update($data);

        return $tourId;
    }

    private function replaceTourTranslations($db, int $tourId, array $post): void
    {
        $db->table('tour_translations')->where('tour_id', $tourId)->delete();
        $this->insertTourTranslations($db, $tourId, $post);
    }

    private function insertTourTranslations($db, int $tourId, array $post): void
    {
        $fields = $db->getFieldNames('tour_translations');
        $locales = [
            'vi' => [
                'name' => trim((string) $post['name_vi']),
                'slug' => trim((string) ($post['slug_vi'] ?? '')) ?: $this->slugify(trim((string) $post['name_vi'])),
                'short_description' => trim((string) ($post['short_description_vi'] ?? '')),
                'description' => $this->sanitizeRichHtml((string) ($post['description_vi'] ?? '')),
                'itinerary' => $this->sanitizeRichHtml((string) ($post['itinerary_vi'] ?? '')),
                'meta_title' => trim((string) ($post['meta_title_vi'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_vi'] ?? '')),
                'overview' => $this->sanitizeRichHtml((string) ($post['overview_vi'] ?? '')),
                'booking_policy' => $this->sanitizeRichHtml((string) ($post['booking_policy_vi'] ?? '')),
                'cancellation_policy' => $this->sanitizeRichHtml((string) ($post['cancellation_policy_vi'] ?? '')),
                'price_note' => $this->sanitizeRichHtml((string) ($post['price_note_vi'] ?? '')),
            ],
            'en' => [
                'name' => trim((string) (($post['name_en'] ?? '') ?: $post['name_vi'])),
                'slug' => trim((string) ($post['slug_en'] ?? '')) ?: $this->slugify(trim((string) (($post['name_en'] ?? '') ?: $post['name_vi']))),
                'short_description' => trim((string) ($post['short_description_en'] ?? '')),
                'description' => $this->sanitizeRichHtml((string) ($post['description_en'] ?? '')),
                'itinerary' => $this->sanitizeRichHtml((string) ($post['itinerary_en'] ?? '')),
                'meta_title' => trim((string) ($post['meta_title_en'] ?? '')),
                'meta_description' => trim((string) ($post['meta_description_en'] ?? '')),
                'overview' => $this->sanitizeRichHtml((string) ($post['overview_en'] ?? '')),
                'booking_policy' => $this->sanitizeRichHtml((string) ($post['booking_policy_en'] ?? '')),
                'cancellation_policy' => $this->sanitizeRichHtml((string) ($post['cancellation_policy_en'] ?? '')),
                'price_note' => $this->sanitizeRichHtml((string) ($post['price_note_en'] ?? '')),
            ],
        ];

        foreach ($locales as $locale => $data) {
            $data['tour_id'] = $tourId;
            $data['locale'] = $locale;
            $db->table('tour_translations')->insert(array_intersect_key($data, array_flip($fields)));
        }
    }

    private function replaceTourDestinations($db, int $tourId, array $post): void
    {
        $db->table('tour_destinations')->where('tour_id', $tourId)->delete();
        $this->insertTourDestinations($db, $tourId, $post);
    }

    private function insertTourDestinations($db, int $tourId, array $post): void
    {
        $destinationIds = [];
        $destinationRows = array_values(array_filter((array) ($post['destinations'] ?? []), static fn($row) => is_array($row)));
        $tourType = (string) ($post['tour_type'] ?? 'outbound');

        foreach ($destinationRows as $row) {
            if ($tourType === 'inbound') {
                $provinceId = (int) ($row['province_id'] ?? 0);

                if ($provinceId <= 0 && ! empty($row['new_province_name_vi']) && ! empty($row['region_key'])) {
                    $provinceId = $this->createProvinceLocation($db, $row);
                }

                if ($provinceId > 0) {
                    $destinationIds[] = $provinceId;
                }

                continue;
            }

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

    private function replaceTourMedia($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_media')) {
            return;
        }

        $db->table('tour_media')->where('tour_id', $tourId)->delete();
        $this->insertTourMedia($db, $tourId, $post, $now);
    }

    private function insertTourMedia($db, int $tourId, array $post, string $now): void
    {
        $mediaFiles = $this->request->getFileMultiple('media_files') ?? [];
        $mediaRows = array_values(array_filter((array) ($post['media'] ?? []), static fn($row) => is_array($row)));

        foreach ($mediaRows as $index => $row) {
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

    private function replaceTourItinerary($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_itinerary_days') || ! $db->tableExists('tour_itinerary_day_translations')) {
            return;
        }

        $dayIds = $db->table('tour_itinerary_days')->select('id')->where('tour_id', $tourId)->get()->getResultArray();
        $ids = array_map(static fn(array $row): int => (int) $row['id'], $dayIds);
        if ($ids !== []) {
            $db->table('tour_itinerary_day_translations')->whereIn('itinerary_day_id', $ids)->delete();
            $db->table('tour_itinerary_days')->where('tour_id', $tourId)->delete();
        }

        $this->insertTourItinerary($db, $tourId, $post, $now);
    }

    private function insertTourItinerary($db, int $tourId, array $post, string $now): void
    {
        foreach (array_values((array) ($post['itinerary_days'] ?? [])) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $titleVi = trim((string) ($row['title_vi'] ?? ''));
            $descriptionVi = $this->sanitizeRichHtml((string) ($row['description_vi'] ?? ''));

            if ($titleVi === '' && $descriptionVi === '') {
                continue;
            }

            $db->table('tour_itinerary_days')->insert([
                'tour_id' => $tourId,
                'day_number' => (int) ($row['day_number'] ?? ($index + 1)),
                'meals' => '',
                'hotel_name' => '',
                'transport_summary' => '',
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

    private function replaceTourFaqs($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_faqs') || ! $db->tableExists('tour_faq_translations')) {
            return;
        }

        $faqIds = $db->table('tour_faqs')->select('id')->where('tour_id', $tourId)->get()->getResultArray();
        $ids = array_map(static fn(array $row): int => (int) $row['id'], $faqIds);
        if ($ids !== []) {
            $db->table('tour_faq_translations')->whereIn('faq_id', $ids)->delete();
            $db->table('tour_faqs')->where('tour_id', $tourId)->delete();
        }

        $this->insertTourFaqs($db, $tourId, $post, $now);
    }

    private function insertTourFaqs($db, int $tourId, array $post, string $now): void
    {
        foreach (array_values((array) ($post['faqs'] ?? [])) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $questionVi = trim(strip_tags((string) ($row['question_vi'] ?? '')));
            $answerVi = $this->sanitizeRichHtml((string) ($row['answer_vi'] ?? ''));

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
                'question' => trim(strip_tags((string) ($row['question_en'] ?? ''))) ?: $questionVi,
                'answer' => $this->sanitizeRichHtml((string) ($row['answer_en'] ?? '')) ?: $answerVi,
            ]);
        }
    }

    private function replaceTourDepartures($db, int $tourId, array $post, string $now): void
    {
        if (! $db->tableExists('tour_departures')) {
            return;
        }

        $db->table('tour_departures')->where('tour_id', $tourId)->delete();
        $this->insertTourDepartures($db, $tourId, $post, $now);
    }

    private function insertTourDepartures($db, int $tourId, array $post, string $now): void
    {
        $fields = $db->getFieldNames('tour_departures');
        $defaultPrice = $this->nullableInt($post['sale_price'] ?? null)
            ?? $this->nullableInt($post['base_price'] ?? null)
            ?? 0;

        foreach ($this->normalizeDepartureRows($post) as $row) {
            $data = [
                'tour_id' => $tourId,
                'departure_date' => $row['departure_date'],
                'available_slots' => $this->nullableInt($row['available_slots'] ?? null),
                'price' => $this->nullableInt($row['price'] ?? null) ?? $defaultPrice,
                'price_up' => $this->nullableInt($row['price_up'] ?? null),
                'status' => in_array(($row['status'] ?? 'open'), ['open', 'closed'], true) ? $row['status'] : 'open',
                'created_at' => $now,
            ];

            $db->table('tour_departures')->insert(array_intersect_key($data, array_flip($fields)));
        }
    }

    private function loadTourFormData(int $tourId): ?array
    {
        $db = db_connect();
        $tour = $db->table('tours')->where('id', $tourId)->get()->getRowArray();

        if (! is_array($tour)) {
            return null;
        }

        $translations = $db->table('tour_translations')->where('tour_id', $tourId)->get()->getResultArray();
        $translationMap = [];
        foreach ($translations as $translation) {
            $translationMap[$translation['locale']] = $translation;
        }

        $destinations = $db->table('tour_destinations td')
            ->select('l.id AS location_id, l.type, l.parent_id, COALESCE(lt_vi.name, lt_en.name) AS name')
            ->join('locations l', 'l.id = td.location_id')
            ->join('location_translations lt_vi', 'lt_vi.location_id = l.id AND lt_vi.locale = "vi"', 'left')
            ->join('location_translations lt_en', 'lt_en.location_id = l.id AND lt_en.locale = "en"', 'left')
            ->where('td.tour_id', $tourId)
            ->get()
            ->getResultArray();

        $domesticRegionService = new DomesticRegionService();
        $domesticMenu = $domesticRegionService->getMenu('vi');
        $provinceRegionMap = [];
        foreach ($domesticMenu as $regionKey => $region) {
            foreach ((array) ($region['provinces'] ?? []) as $province) {
                $provinceRegionMap[(int) $province['id']] = $regionKey;
            }
        }

        $destinationRows = [];
        foreach ($destinations as $destination) {
            if (($tour['tour_type'] ?? 'outbound') === 'inbound') {
                $destinationRows[] = [
                    'region_key' => $provinceRegionMap[(int) $destination['location_id']] ?? '',
                    'province_id' => (int) $destination['location_id'],
                ];
            } else {
                $destinationRows[] = [
                    'continent_id' => (int) $destination['parent_id'],
                    'country_id' => (int) $destination['location_id'],
                ];
            }
        }

        $departures = $db->table('tour_departures')
            ->where('tour_id', $tourId)
            ->orderBy('departure_date', 'ASC')
            ->get()
            ->getResultArray();
        $departure = $departures[0] ?? [];
        $media = $db->table('tour_media')->where('tour_id', $tourId)->orderBy('sort_order', 'ASC')->get()->getResultArray();

        $itinerary = $db->table('tour_itinerary_days d')
            ->select('d.day_number, d.sort_order, vi.title AS title_vi, vi.description AS description_vi, en.title AS title_en, en.description AS description_en')
            ->join('tour_itinerary_day_translations vi', 'vi.itinerary_day_id = d.id AND vi.locale = "vi"', 'left')
            ->join('tour_itinerary_day_translations en', 'en.itinerary_day_id = d.id AND en.locale = "en"', 'left')
            ->where('d.tour_id', $tourId)
            ->orderBy('d.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $faqs = $db->table('tour_faqs f')
            ->select('f.sort_order, vi.question AS question_vi, vi.answer AS answer_vi, en.question AS question_en, en.answer AS answer_en')
            ->join('tour_faq_translations vi', 'vi.faq_id = f.id AND vi.locale = "vi"', 'left')
            ->join('tour_faq_translations en', 'en.faq_id = f.id AND en.locale = "en"', 'left')
            ->where('f.tour_id', $tourId)
            ->orderBy('f.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'tour_type' => $tour['tour_type'] ?? 'outbound',
            'category_id' => $tour['category_id'] ?? '',
            'code' => $tour['code'] ?? '',
            'sku' => $tour['sku'] ?? '',
            'duration_days' => $tour['duration_days'] ?? 5,
            'duration_nights' => $tour['duration_nights'] ?? 4,
            'max_travelers' => $tour['max_travelers'] ?? 15,
            'min_travelers' => $tour['min_travelers'] ?? '',
            'base_price' => $tour['base_price'] ?? '',
            'sale_price' => $tour['sale_price'] ?? '',
            'child_price_rate' => $tour['child_price_rate'] ?? '0.85',
            'infant_price_rate' => $tour['infant_price_rate'] ?? '0.25',
            'thumbnail' => $tour['thumbnail'] ?? '',
            'status' => $tour['status'] ?? 'draft',
            'is_featured' => (int) ($tour['is_featured'] ?? 0),
            'is_promotion' => (int) ($tour['is_promotion'] ?? 0),
            'promotion_badge' => $tour['promotion_badge'] ?? '',
            'promotion_ends_at' => $this->formatDateTimeLocal($tour['promotion_ends_at'] ?? ''),
            'promotion_sort' => $tour['promotion_sort'] ?? 0,
            'departure_location_id' => $tour['departure_location_id'] ?? '',
            'primary_destination_id' => $tour['primary_destination_id'] ?? '',
            'name_vi' => $translationMap['vi']['name'] ?? '',
            'slug_vi' => $translationMap['vi']['slug'] ?? '',
            'short_description_vi' => $translationMap['vi']['short_description'] ?? '',
            'overview_vi' => $translationMap['vi']['overview'] ?? '',
            'description_vi' => $translationMap['vi']['description'] ?? '',
            'name_en' => $translationMap['en']['name'] ?? '',
            'slug_en' => $translationMap['en']['slug'] ?? '',
            'short_description_en' => $translationMap['en']['short_description'] ?? '',
            'overview_en' => $translationMap['en']['overview'] ?? '',
            'description_en' => $translationMap['en']['description'] ?? '',
            'departure_date' => $departure['departure_date'] ?? '',
            'available_slots' => $departure['available_slots'] ?? '',
            'departure_status' => $departure['status'] ?? 'open',
            'departures' => $departures !== [] ? array_map(static fn(array $row): array => [
                'departure_date' => $row['departure_date'] ?? '',
                'available_slots' => $row['available_slots'] ?? '',
                'price' => $row['price'] ?? '',
                'price_up' => $row['price_up'] ?? '',
                'status' => $row['status'] ?? 'open',
            ], $departures) : [$this->defaultDepartureRow()],
            'meta_title_vi' => $translationMap['vi']['meta_title'] ?? '',
            'meta_title_en' => $translationMap['en']['meta_title'] ?? '',
            'meta_description_vi' => $translationMap['vi']['meta_description'] ?? '',
            'meta_description_en' => $translationMap['en']['meta_description'] ?? '',
            'destinations' => $destinationRows !== [] ? $destinationRows : [$this->defaultDestinationRow($tour['tour_type'] ?? 'outbound')],
            'itinerary_days' => $itinerary !== [] ? $itinerary : [$this->defaultItineraryRow()],
            'media' => $media !== [] ? $media : [$this->defaultMediaRow()],
            'faqs' => $faqs !== [] ? $faqs : [$this->defaultFaqRow()],
        ];
    }

    private function defaultFormData(): array
    {
        return [
            'tour_type' => 'outbound',
            'duration_days' => 5,
            'duration_nights' => 4,
            'max_travelers' => 15,
            'child_price_rate' => '0.85',
            'infant_price_rate' => '0.25',
            'status' => 'draft',
            'promotion_sort' => 0,
            'departure_status' => 'open',
            'departures' => [$this->defaultDepartureRow()],
            'destinations' => [$this->defaultDestinationRow('outbound')],
            'itinerary_days' => [$this->defaultItineraryRow()],
            'media' => [$this->defaultMediaRow()],
            'faqs' => [$this->defaultFaqRow()],
        ];
    }

    private function defaultDepartureRow(): array
    {
        return [
            'departure_date' => '',
            'available_slots' => '',
            'price' => '',
            'price_up' => '',
            'status' => 'open',
        ];
    }

    private function validateDepartureRows(array $post): array
    {
        $errors = [];
        $seenDates = [];
        $validRows = 0;

        foreach ($this->extractDepartureRows($post) as $index => $row) {
            $rowNumber = $index + 1;
            $date = trim((string) ($row['departure_date'] ?? $row['date'] ?? ''));
            $hasData = $date !== ''
                || trim((string) ($row['available_slots'] ?? '')) !== ''
                || trim((string) ($row['price'] ?? '')) !== ''
                || trim((string) ($row['price_up'] ?? '')) !== '';

            if (! $hasData) {
                continue;
            }

            if (! $this->isValidYmdDate($date)) {
                $errors['departures.' . $index . '.departure_date'] = 'Departure row #' . $rowNumber . ' needs a valid date.';
                continue;
            }

            $validRows++;

            if (isset($seenDates[$date])) {
                $errors['departures.' . $index . '.duplicate'] = 'Departure date ' . $date . ' is duplicated.';
            }

            $seenDates[$date] = true;

            foreach (['available_slots' => 'Slots', 'price' => 'Price', 'price_up' => 'Price up'] as $field => $label) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '' && ! preg_match('/^\d+$/', $value)) {
                    $errors['departures.' . $index . '.' . $field] = $label . ' in departure row #' . $rowNumber . ' must be a non-negative number.';
                }
            }

            $status = (string) ($row['status'] ?? 'open');
            if (! in_array($status, ['open', 'closed'], true)) {
                $errors['departures.' . $index . '.status'] = 'Departure row #' . $rowNumber . ' has an invalid status.';
            }
        }

        if (($post['status'] ?? 'draft') === 'published' && $validRows === 0) {
            $errors['departures.required'] = 'Published tours need at least one departure date.';
        }

        return $errors;
    }

    private function normalizeDepartureRows(array $post): array
    {
        $rowsByDate = [];

        foreach ($this->extractDepartureRows($post) as $row) {
            $date = trim((string) ($row['departure_date'] ?? $row['date'] ?? ''));
            if (! $this->isValidYmdDate($date)) {
                continue;
            }

            $rowsByDate[$date] = [
                'departure_date' => $date,
                'available_slots' => trim((string) ($row['available_slots'] ?? '')),
                'price' => trim((string) ($row['price'] ?? '')),
                'price_up' => trim((string) ($row['price_up'] ?? '')),
                'status' => in_array(($row['status'] ?? 'open'), ['open', 'closed'], true) ? $row['status'] : 'open',
            ];
        }

        ksort($rowsByDate);

        return array_values($rowsByDate);
    }

    private function extractDepartureRows(array $post): array
    {
        $rows = array_values(array_filter((array) ($post['departures'] ?? []), static fn($row): bool => is_array($row)));

        if ($rows === [] && ! empty($post['departure_date'])) {
            $rows[] = [
                'departure_date' => $post['departure_date'],
                'available_slots' => $post['available_slots'] ?? '',
                'price' => $post['departure_price'] ?? $post['base_price'] ?? '',
                'price_up' => $post['price_up'] ?? '',
                'status' => $post['departure_status'] ?? 'open',
            ];
        }

        return $rows;
    }

    private function isValidYmdDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $parsed = \DateTime::createFromFormat('!Y-m-d', $date);

        return $parsed instanceof \DateTime && $parsed->format('Y-m-d') === $date;
    }

    private function defaultDestinationRow(string $tourType): array
    {
        return $tourType === 'inbound'
            ? ['region_key' => '', 'province_id' => 0]
            : ['continent_id' => 0, 'country_id' => 0];
    }

    private function defaultItineraryRow(): array
    {
        return [
            'day_number' => 1,
            'title_vi' => '',
            'title_en' => '',
            'description_vi' => '',
            'description_en' => '',
        ];
    }

    private function defaultMediaRow(): array
    {
        return [
            'type' => 'gallery',
            'file_path' => '',
            'alt_text' => '',
        ];
    }

    private function defaultFaqRow(): array
    {
        return [
            'question_vi' => '',
            'question_en' => '',
            'answer_vi' => '',
            'answer_en' => '',
        ];
    }

    private function createCountryLocation($db, array $row): int
    {
        $now = date('Y-m-d H:i:s');
        $continentId = (int) ($row['continent_id'] ?? 0);
        $nameVi = trim((string) ($row['new_country_name_vi'] ?? ''));
        $nameEn = trim((string) ($row['new_country_name_en'] ?? $nameVi));
        $slugVi = trim((string) ($row['new_country_slug_vi'] ?? '')) ?: $this->slugify($nameVi);
        $slugEn = trim((string) ($row['new_country_slug_en'] ?? '')) ?: $this->slugify($nameEn);
        $code = strtoupper(trim((string) ($row['new_country_code'] ?? '')));

        if ($continentId <= 0 || $nameVi === '') {
            return 0;
        }

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

    private function createProvinceLocation($db, array $row): int
    {
        $now = date('Y-m-d H:i:s');
        $parentCountryId = $this->getVietnamCountryId($db);
        $nameVi = trim((string) ($row['new_province_name_vi'] ?? ''));
        $nameEn = trim((string) ($row['new_province_name_en'] ?? $nameVi));
        $slugVi = trim((string) ($row['new_province_slug_vi'] ?? '')) ?: $this->slugify($nameVi);
        $slugEn = trim((string) ($row['new_province_slug_en'] ?? '')) ?: $this->slugify($nameEn);
        $code = strtoupper(trim((string) ($row['new_province_code'] ?? '')));

        if ($parentCountryId <= 0 || $nameVi === '') {
            return 0;
        }

        $db->table('locations')->insert([
            'parent_id' => $parentCountryId,
            'type' => 'province',
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

    private function getVietnamCountryId($db): int
    {
        $row = $db->table('locations')
            ->select('id')
            ->where('type', 'country')
            ->where('code', 'VN')
            ->limit(1)
            ->get()
            ->getRowArray();

        return (int) ($row['id'] ?? 0);
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

    private function sanitizeRichHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<(script|style|iframe|object|embed|link|meta)\b[^>]*>.*?<\/\1>/is', '', $html) ?? '';
        $html = preg_replace('/<\s*(script|style|iframe|object|embed|link|meta)\b[^>]*\/?\s*>/i', '', $html) ?? '';
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

    private function normalizeTravelerPriceRate($value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $value = str_replace(',', '.', trim((string) $value));
        $rate = (float) $value;

        if ($rate < 0 || $rate > 1) {
            return $default;
        }

        return round($rate, 4);
    }

    private function nullableDateTime($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function formatDateTimeLocal($value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d\TH:i', $timestamp);
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
