<?php

namespace App\Controllers\Admin;

use App\Models\PromotionCodeModel;
use App\Models\PromotionCodeTourModel;

class PromotionCodes extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $model = new PromotionCodeModel();

        if (! $model->db->tableExists($model->getTable())) {
            return redirect()->to(site_url('admin'))->with('error', 'Bảng mã khuyến mãi chưa tồn tại. Hãy chạy SQL hoặc migrate trước.');
        }

        $keyword = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $scope = trim((string) $this->request->getGet('scope'));
        $hasMappingTable = $model->db->tableExists('promotion_code_tours');

        $builder = $this->baseIndexQuery($hasMappingTable);

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('pc.code', $keyword)
                ->orLike('pc.name', $keyword)
                ->orLike('pc.description', $keyword)
                ->groupEnd();
        }

        if ($status === 'active') {
            $builder->where('pc.is_active', 1);
        } elseif ($status === 'inactive') {
            $builder->where('pc.is_active', 0);
        }

        if ($hasMappingTable && $scope === 'specific') {
            $builder->where('EXISTS (SELECT 1 FROM promotion_code_tours pct2 WHERE pct2.promotion_code_id = pc.id)', null, false);
        } elseif ($hasMappingTable && $scope === 'all') {
            $builder->where('NOT EXISTS (SELECT 1 FROM promotion_code_tours pct2 WHERE pct2.promotion_code_id = pc.id)', null, false);
        }

        $codes = $builder->orderBy('pc.updated_at', 'DESC')->get()->getResultArray();

        return view('admin/promotion_codes/index', [
            'codes' => $codes,
            'stats' => $this->buildStats(),
            'keyword' => $keyword,
            'status' => $status,
            'scope' => $scope,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('admin/promotion_codes/form', [
            'pageTitle' => 'Tạo mã khuyến mãi',
            'formAction' => site_url('admin/promotion-codes'),
            'submitLabel' => 'Lưu mã',
            'formData' => $this->defaultFormData(),
            'tourOptions' => $this->loadTourOptions(),
            'selectedTourIds' => old('tour_ids') ?? [],
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function cloneCode(int $codeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $code = (new PromotionCodeModel())->find($codeId);

        if (! is_array($code)) {
            return redirect()->to(site_url('admin/promotion-codes'))->with('error', 'Không tìm thấy mã khuyến mãi để nhân bản.');
        }

        $formData = $this->mapFormData($code);
        $formData['code'] = (string) $formData['code'] . '-COPY';
        $formData['name'] = (string) $formData['name'] . ' (Copy)';
        $formData['used_count'] = 0;

        return view('admin/promotion_codes/form', [
            'pageTitle' => 'Nhân bản mã khuyến mãi',
            'formAction' => site_url('admin/promotion-codes'),
            'submitLabel' => 'Tạo từ bản sao',
            'formData' => $formData,
            'tourOptions' => $this->loadTourOptions(),
            'selectedTourIds' => $this->loadAssignedTourIds($codeId),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return $this->saveCode();
    }

    public function edit(int $codeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $code = (new PromotionCodeModel())->find($codeId);

        if (! is_array($code)) {
            return redirect()->to(site_url('admin/promotion-codes'))->with('error', 'Không tìm thấy mã khuyến mãi.');
        }

        return view('admin/promotion_codes/form', [
            'pageTitle' => 'Cập nhật mã #' . $codeId,
            'formAction' => site_url('admin/promotion-codes/' . $codeId),
            'submitLabel' => 'Cập nhật mã',
            'formData' => $this->mapFormData($code),
            'tourOptions' => $this->loadTourOptions(),
            'selectedTourIds' => old('tour_ids') ?? $this->loadAssignedTourIds($codeId),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(int $codeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $model = new PromotionCodeModel();

        if (! is_array($model->find($codeId))) {
            return redirect()->to(site_url('admin/promotion-codes'))->with('error', 'Không tìm thấy mã khuyến mãi.');
        }

        return $this->saveCode($codeId);
    }

    public function toggle(int $codeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $model = new PromotionCodeModel();
        $code = $model->find($codeId);

        if (! is_array($code)) {
            return redirect()->to(site_url('admin/promotion-codes'))->with('error', 'Không tìm thấy mã khuyến mãi.');
        }

        $nextStatus = (int) ($code['is_active'] ?? 0) === 1 ? 0 : 1;
        $model->update($codeId, ['is_active' => $nextStatus]);

        return redirect()->to($this->indexUrl())->with(
            'success',
            $nextStatus === 1 ? 'Đã kích hoạt mã khuyến mãi.' : 'Đã tạm dừng mã khuyến mãi.'
        );
    }

    public function delete(int $codeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $model = new PromotionCodeModel();
        $code = $model->find($codeId);

        if (! is_array($code)) {
            return redirect()->to(site_url('admin/promotion-codes'))->with('error', 'Không tìm thấy mã khuyến mãi.');
        }

        $this->deleteAssignedTours($codeId);
        $model->delete($codeId);

        return redirect()->to($this->indexUrl())->with('success', 'Đã xóa mã khuyến mãi #' . $codeId . '.');
    }

    private function saveCode(?int $codeId = null)
    {
        $model = new PromotionCodeModel();

        if (! $model->db->tableExists($model->getTable())) {
            return redirect()->to(site_url('admin'))->with('error', 'Bảng mã khuyến mãi chưa tồn tại. Hãy chạy SQL hoặc migrate trước.');
        }

        $rules = [
            'code' => 'required|max_length[50]',
            'name' => 'required|max_length[150]',
            'discount_type' => 'required|in_list[fixed,percent]',
            'discount_value' => 'required|decimal',
            'max_discount_amount' => 'permit_empty|decimal',
            'min_order_amount' => 'permit_empty|decimal',
            'usage_limit' => 'permit_empty|is_natural',
            'starts_at' => 'permit_empty',
            'ends_at' => 'permit_empty',
            'scope' => 'required|in_list[all,specific]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors())->with('error', 'Dữ liệu mã khuyến mãi chưa hợp lệ.');
        }

        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $existing = $model->where('code', $code)->first();

        if (is_array($existing) && (int) $existing['id'] !== (int) $codeId) {
            return redirect()->back()->withInput()->with('error', 'Mã khuyến mãi đã tồn tại.');
        }

        $scope = (string) $this->request->getPost('scope');
        $selectedTourIds = array_values(array_unique(array_map(
            static fn($value): int => (int) $value,
            (array) $this->request->getPost('tour_ids')
        )));
        $selectedTourIds = array_values(array_filter($selectedTourIds, static fn(int $value): bool => $value > 0));

        if ($scope === 'specific' && $selectedTourIds === []) {
            return redirect()->back()->withInput()->with('error', 'Hãy chọn ít nhất một tour nếu mã chỉ áp dụng cho tour cụ thể.');
        }

        $payload = [
            'code' => $code,
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'discount_type' => (string) $this->request->getPost('discount_type'),
            'discount_value' => (float) $this->request->getPost('discount_value'),
            'max_discount_amount' => $this->nullableFloat($this->request->getPost('max_discount_amount')),
            'min_order_amount' => max(0, (float) ($this->request->getPost('min_order_amount') ?: 0)),
            'usage_limit' => max(0, (int) ($this->request->getPost('usage_limit') ?: 0)),
            'starts_at' => $this->nullableDateTime($this->request->getPost('starts_at')),
            'ends_at' => $this->nullableDateTime($this->request->getPost('ends_at')),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($payload['discount_type'] === 'percent') {
            $payload['discount_value'] = min(100, max(0, $payload['discount_value']));
        } else {
            $payload['discount_value'] = max(0, $payload['discount_value']);
            $payload['max_discount_amount'] = null;
        }

        if ($codeId === null) {
            $payload['used_count'] = 0;
            $model->insert($payload);
            $codeId = (int) $model->getInsertID();
            $this->syncAssignedTours($codeId, $scope === 'specific' ? $selectedTourIds : []);

            return redirect()->to($this->indexUrl())->with('success', 'Đã tạo mã khuyến mãi mới.');
        }

        $model->update($codeId, $payload);
        $this->syncAssignedTours($codeId, $scope === 'specific' ? $selectedTourIds : []);

        return redirect()->to(site_url('admin/promotion-codes/' . $codeId . '/edit'))->with('success', 'Đã cập nhật mã khuyến mãi #' . $codeId . '.');
    }

    private function baseIndexQuery(bool $hasMappingTable)
    {
        $db = db_connect();

        $builder = $db->table('promotion_codes pc')->select('pc.*');

        if ($hasMappingTable) {
            $builder->select('COUNT(DISTINCT pct.tour_id) AS assigned_tour_count', false)
                ->join('promotion_code_tours pct', 'pct.promotion_code_id = pc.id', 'left')
                ->groupBy('pc.id');
        } else {
            $builder->select('0 AS assigned_tour_count', false);
        }

        if ($db->tableExists('bookings')) {
            $builder->select('COUNT(DISTINCT b.id) AS booking_usage_count', false)
                ->join('bookings b', 'b.coupon_id = pc.id', 'left');
        } else {
            $builder->select('0 AS booking_usage_count', false);
        }

        if (! $hasMappingTable) {
            $builder->groupBy('pc.id');
        }

        return $builder;
    }

    /**
     * @return array{total:int,active:int,inactive:int,all:int,specific:int}
     */
    private function buildStats(): array
    {
        $model = new PromotionCodeModel();
        $db = $model->db;

        $total = $model->countAllResults();
        $active = $model->where('is_active', 1)->countAllResults();
        $inactive = max(0, $total - $active);
        $all = 0;
        $specific = 0;

        if ($db->tableExists('promotion_code_tours') && $db->tableExists('promotion_codes')) {
            $specific = (int) $db->table('promotion_codes pc')
                ->select('COUNT(DISTINCT pc.id) AS total', false)
                ->join('promotion_code_tours pct', 'pct.promotion_code_id = pc.id', 'inner')
                ->get()
                ->getRow('total');
        }

        $all = max(0, $total - $specific);

        return compact('total', 'active', 'inactive', 'all', 'specific');
    }

    /**
     * @return list<array{id:int,name:string,status:string}>
     */
    private function loadTourOptions(): array
    {
        $db = db_connect();

        if (! $db->tableExists('tours') || ! $db->tableExists('tour_translations')) {
            return [];
        }

        $rows = $db->table('tours t')
            ->select('t.id, t.status, COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", t.id)) AS name', false)
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
            ->join('tour_translations tt_en', 'tt_en.tour_id = t.id AND tt_en.locale = "en"', 'left')
            ->orderBy('tt_vi.name', 'ASC')
            ->orderBy('tt_en.name', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'name' => trim((string) ($row['name'] ?? '')),
                'status' => trim((string) ($row['status'] ?? '')),
            ];
        }, $rows);
    }

    /**
     * @return list<int>
     */
    private function loadAssignedTourIds(int $codeId): array
    {
        $mappingModel = new PromotionCodeTourModel();

        if (! $mappingModel->db->tableExists($mappingModel->getTable())) {
            return [];
        }

        return array_values(array_map(
            static fn(array $row): int => (int) ($row['tour_id'] ?? 0),
            $mappingModel->select('tour_id')->where('promotion_code_id', $codeId)->findAll()
        ));
    }

    /**
     * @param list<int> $tourIds
     */
    private function syncAssignedTours(int $codeId, array $tourIds): void
    {
        $mappingModel = new PromotionCodeTourModel();

        if (! $mappingModel->db->tableExists($mappingModel->getTable())) {
            return;
        }

        $mappingModel->where('promotion_code_id', $codeId)->delete();

        foreach ($tourIds as $tourId) {
            $mappingModel->insert([
                'promotion_code_id' => $codeId,
                'tour_id' => $tourId,
            ]);
        }
    }

    private function deleteAssignedTours(int $codeId): void
    {
        $mappingModel = new PromotionCodeTourModel();

        if (! $mappingModel->db->tableExists($mappingModel->getTable())) {
            return;
        }

        $mappingModel->where('promotion_code_id', $codeId)->delete();
    }

    /**
     * @param array<string, mixed> $code
     * @return array<string, mixed>
     */
    private function mapFormData(array $code): array
    {
        $scope = $this->loadAssignedTourIds((int) ($code['id'] ?? 0)) === [] ? 'all' : 'specific';

        return [
            'code' => (string) ($code['code'] ?? ''),
            'name' => (string) ($code['name'] ?? ''),
            'description' => (string) ($code['description'] ?? ''),
            'discount_type' => (string) ($code['discount_type'] ?? 'fixed'),
            'discount_value' => (string) ($code['discount_value'] ?? '0'),
            'max_discount_amount' => (string) ($code['max_discount_amount'] ?? ''),
            'min_order_amount' => (string) ($code['min_order_amount'] ?? '0'),
            'usage_limit' => (string) ($code['usage_limit'] ?? '0'),
            'used_count' => (int) ($code['used_count'] ?? 0),
            'starts_at' => $this->formatDateTimeLocal($code['starts_at'] ?? ''),
            'ends_at' => $this->formatDateTimeLocal($code['ends_at'] ?? ''),
            'is_active' => (int) ($code['is_active'] ?? 1),
            'scope' => $scope,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormData(): array
    {
        return [
            'code' => '',
            'name' => '',
            'description' => '',
            'discount_type' => 'fixed',
            'discount_value' => '',
            'max_discount_amount' => '',
            'min_order_amount' => '0',
            'usage_limit' => '0',
            'used_count' => 0,
            'starts_at' => '',
            'ends_at' => '',
            'is_active' => 1,
            'scope' => 'all',
        ];
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function nullableFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (float) $value);
    }

    private function nullableDateTime($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
    }

    private function formatDateTimeLocal($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d\TH:i', $timestamp);
    }

    private function indexUrl(): string
    {
        $query = array_filter([
            'q' => trim((string) $this->request->getGet('q')),
            'status' => trim((string) $this->request->getGet('status')),
            'scope' => trim((string) $this->request->getGet('scope')),
        ], static fn(string $value): bool => $value !== '');

        $url = site_url('admin/promotion-codes');

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }
}
