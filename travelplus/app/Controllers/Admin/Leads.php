<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;
use App\Models\CrmLeadModel;
use App\Services\CrmLeadCaptureService;

class Leads extends BaseAdminController
{
    private const STAGES = ['new', 'consulting', 'won', 'lost'];
    private const SOURCES = ['contact_form', 'tour_enquiry', 'ai_chat', 'booking', 'manual'];

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $this->syncOpenBookings();

        $stage = $this->normalizeStage((string) $this->request->getGet('stage'));
        $source = $this->normalizeSource((string) $this->request->getGet('source'));
        $keyword = trim((string) $this->request->getGet('q'));

        $query = (new CrmLeadModel())->orderBy('updated_at', 'DESC')->orderBy('created_at', 'DESC');
        $this->applyFilters($query, $stage, $source, $keyword);

        return view('admin/leads/index', [
            'leads' => $query->paginate(24),
            'pager' => $query->pager,
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

        return redirect()->back()->with('success', 'Đã cập nhật lead.');
    }

    private function syncOpenBookings(): void
    {
        $db = db_connect();

        if (! $db->tableExists('bookings') || ! $db->tableExists('crm_leads')) {
            return;
        }

        $bookings = (new BookingModel())
            ->whereIn('payment_status', ['pending_payment', 'pending_transfer', 'failed', 'cancelled'])
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        $capture = new CrmLeadCaptureService();

        foreach ($bookings as $booking) {
            if (is_array($booking)) {
                $capture->captureBooking($booking);
            }
        }
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
        $db = db_connect();

        if (! $db->tableExists('crm_leads')) {
            return ['new' => 0, 'consulting' => 0, 'won' => 0, 'lost' => 0, 'total' => 0];
        }

        $stats = ['new' => 0, 'consulting' => 0, 'won' => 0, 'lost' => 0, 'total' => 0];
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

        return $stats;
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
