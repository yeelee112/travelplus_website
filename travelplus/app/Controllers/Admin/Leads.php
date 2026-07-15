<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;
use App\Models\CrmLeadModel;
use App\Services\CrmLeadCaptureService;

class Leads extends BaseAdminController
{
    private const STAGES = ['new', 'consulting', 'won', 'lost'];
    private const SOURCES = ['contact_form', 'tour_enquiry', 'ai_chat', 'booking', 'manual'];
    private const PER_PAGE = 12;
    private const STATS_CACHE_KEY = 'admin_crm_leads_stage_stats';
    private const STATS_CACHE_TTL = 60;

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $stage = $this->normalizeStage((string) $this->request->getGet('stage'));
        $source = $this->normalizeSource((string) $this->request->getGet('source'));
        $keyword = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) $this->request->getGet('page'));

        if ((string) $this->request->getGet('sync_bookings') === '1') {
            $synced = $this->syncOpenBookings();
            $this->clearStatsCache();
            $query = http_build_query(array_filter([
                'stage' => $stage,
                'source' => $source,
                'q' => $keyword,
            ], static fn ($value) => $value !== ''));

            return redirect()->to(site_url('admin/leads' . ($query !== '' ? '?' . $query : '')))
                ->with('success', 'Đã đồng bộ ' . $synced . ' booking vào CRM leads.');
        }

        $query = (new CrmLeadModel())
            ->select('id, source, stage, priority, customer_name, customer_email, customer_phone, interest_title, interest_url, destination, travel_date, travelers, message, booking_code, last_contacted_at, internal_note, created_at, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC');
        $this->applyFilters($query, $stage, $source, $keyword);
        $leads = $query->findAll(self::PER_PAGE + 1, ($page - 1) * self::PER_PAGE);
        $hasNextPage = count($leads) > self::PER_PAGE;

        return view('admin/leads/index', [
            'leads' => array_slice($leads, 0, self::PER_PAGE),
            'currentPage' => $page,
            'hasNextPage' => $hasNextPage,
            'stage' => $stage,
            'source' => $source,
            'keyword' => $keyword,
            'stageOptions' => self::STAGES,
            'sourceOptions' => self::SOURCES,
            'stats' => $this->buildStats(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function update(int $leadId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $model = new CrmLeadModel();
        $lead = $model->find($leadId);

        if (! is_array($lead)) {
            return redirect()->to(site_url('admin/leads'))->with('error', 'Không tìm thấy lead.');
        }

        $stage = $this->normalizeStage((string) $this->request->getPost('stage'));
        $priority = $this->normalizePriority((string) $this->request->getPost('priority'));
        $note = trim((string) $this->request->getPost('internal_note'));
        $markContacted = (string) $this->request->getPost('mark_contacted') === '1';

        if ($stage === '') {
            return redirect()->back()->with('error', 'Stage không hợp lệ.');
        }

        $update = [
            'stage' => $stage,
            'priority' => $priority,
            'internal_note' => $note !== '' ? $note : null,
        ];

        if ($markContacted) {
            $update['last_contacted_at'] = date('Y-m-d H:i:s');
        }

        $model->update($leadId, $update);
        $this->clearStatsCache();

        return redirect()->back()->with('success', 'Đã cập nhật lead.');
    }

    private function syncOpenBookings(): int
    {
        $db = db_connect();

        if (! $db->tableExists('bookings') || ! $db->tableExists('crm_leads')) {
            return 0;
        }

        $bookings = (new BookingModel())
            ->select('id, booking_code, customer_name, customer_email, customer_phone, customer_note, tour_title, tour_link, departure_label, adult_quantity, child_quantity, infant_quantity, payment_status, payment_method, amount_due_vnd, grand_total')
            ->whereIn('payment_status', ['pending_payment', 'pending_transfer', 'failed', 'cancelled'])
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        $capture = new CrmLeadCaptureService();
        $synced = 0;

        foreach ($bookings as $booking) {
            if (is_array($booking)) {
                $capture->captureBooking($booking);
                $synced++;
            }
        }

        return $synced;
    }

    private function applyFilters(CrmLeadModel $query, string $stage, string $source, string $keyword): void
    {
        if ($stage !== '') {
            $query->where('stage', $stage);
        }

        if ($source !== '') {
            $query->where('source', $source);
        }

        if ($keyword !== '') {
            $query->groupStart()
                ->like('customer_name', $keyword)
                ->orLike('customer_email', $keyword)
                ->orLike('customer_phone', $keyword)
                ->orLike('interest_title', $keyword)
                ->orLike('destination', $keyword)
                ->orLike('booking_code', $keyword)
                ->groupEnd();
        }
    }

    /**
     * @return array<string, int>
     */
    private function buildStats(): array
    {
        $defaults = ['new' => 0, 'consulting' => 0, 'won' => 0, 'lost' => 0, 'total' => 0];
        $cached = cache()->get(self::STATS_CACHE_KEY);

        if (is_array($cached)) {
            return array_merge($defaults, $cached);
        }

        $db = db_connect();

        if (! $db->tableExists('crm_leads')) {
            return $defaults;
        }

        $stats = $defaults;
        $rows = $db->table('crm_leads')
            ->select('stage, COUNT(*) AS total')
            ->groupBy('stage')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $stage = (string) ($row['stage'] ?? '');
            if (array_key_exists($stage, $stats)) {
                $stats[$stage] = (int) ($row['total'] ?? 0);
                $stats['total'] += $stats[$stage];
            }
        }

        cache()->save(self::STATS_CACHE_KEY, $stats, self::STATS_CACHE_TTL);

        return $stats;
    }

    private function clearStatsCache(): void
    {
        cache()->delete(self::STATS_CACHE_KEY);
    }

    private function normalizeStage(string $stage): string
    {
        $stage = trim($stage);

        return in_array($stage, self::STAGES, true) ? $stage : '';
    }

    private function normalizeSource(string $source): string
    {
        $source = trim($source);

        return in_array($source, self::SOURCES, true) ? $source : '';
    }

    private function normalizePriority(string $priority): string
    {
        return in_array($priority, ['low', 'normal', 'high'], true) ? $priority : 'normal';
    }
}
