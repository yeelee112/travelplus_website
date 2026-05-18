<?php

namespace App\Controllers\Admin;

use App\Data\LocalizedPathCatalog;
use App\Models\BookingModel;
use App\Services\BookingNotificationService;

class Bookings extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $status = trim((string) $this->request->getGet('status'));
        $keyword = trim((string) $this->request->getGet('q'));

        $query = (new BookingModel())->orderBy('created_at', 'DESC');

        if ($status !== '' && in_array($status, ['draft', 'pending_payment', 'pending_transfer', 'paid', 'cancelled', 'failed'], true)) {
            $query->where('payment_status', $status);
        }

        if ($keyword !== '') {
            $query->groupStart()
                ->like('booking_code', $keyword)
                ->orLike('tour_title', $keyword)
                ->orLike('customer_name', $keyword)
                ->orLike('customer_email', $keyword)
                ->groupEnd();
        }

        return view('admin/bookings/index', [
            'bookings' => $query->paginate(20),
            'pager' => $query->pager,
            'status' => $status,
            'keyword' => $keyword,
            'dashboardUrl' => site_url('admin'),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function exportCsv()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $status = trim((string) $this->request->getGet('status'));
        $keyword = trim((string) $this->request->getGet('q'));

        $query = (new BookingModel())->orderBy('created_at', 'DESC');

        if ($status !== '' && in_array($status, ['draft', 'pending_payment', 'pending_transfer', 'paid', 'cancelled', 'failed'], true)) {
            $query->where('payment_status', $status);
        }

        if ($keyword !== '') {
            $query->groupStart()
                ->like('booking_code', $keyword)
                ->orLike('tour_title', $keyword)
                ->orLike('customer_name', $keyword)
                ->orLike('customer_email', $keyword)
                ->groupEnd();
        }

        $bookings = $query->findAll();

        $filename = 'bookings-' . date('Ymd-His') . '.csv';
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, [
            'booking_code',
            'tour_title',
            'customer_name',
            'customer_email',
            'customer_phone',
            'payment_status',
            'payment_method',
            'payment_plan',
            'grand_total',
            'amount_paid_vnd',
            'departure_label',
            'created_at',
        ]);

        foreach ($bookings as $booking) {
            fputcsv($handle, [
                $booking['booking_code'] ?? '',
                $booking['tour_title'] ?? '',
                $booking['customer_name'] ?? '',
                $booking['customer_email'] ?? '',
                $booking['customer_phone'] ?? '',
                $booking['payment_status'] ?? '',
                $booking['payment_method'] ?? '',
                $booking['payment_plan'] ?? '',
                $booking['grand_total'] ?? '',
                $booking['amount_paid_vnd'] ?? '',
                $booking['departure_label'] ?? '',
                $booking['created_at'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    public function show(int $bookingId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $booking = (new BookingModel())->find($bookingId);

        if (! is_array($booking)) {
            return redirect()->to(LocalizedPathCatalog::url('admin.bookings'))
                ->with('error', 'Không tìm thấy booking.');
        }

        return view('admin/bookings/show', [
            'booking' => $booking,
            'statusLogs' => $this->getStatusLogs($bookingId),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function updateStatus(int $bookingId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $bookingModel = new BookingModel();
        $booking = $bookingModel->find($bookingId);

        if (! is_array($booking)) {
            return redirect()->to(LocalizedPathCatalog::url('admin.bookings'))
                ->with('error', 'Không tìm thấy booking.');
        }

        $status = trim((string) $this->request->getPost('payment_status'));
        $note = trim((string) $this->request->getPost('status_note'));
        $allowed = ['pending_payment', 'pending_transfer', 'paid', 'cancelled', 'failed'];

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Trạng thái thanh toán không hợp lệ.');
        }

        $previousStatus = (string) ($booking['payment_status'] ?? '');
        $update = [
            'payment_status' => $status,
        ];

        if ($status === 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
            $update['amount_paid_vnd'] = (float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0);
        }

        if ($status === 'cancelled') {
            $update['cancelled_at'] = date('Y-m-d H:i:s');
        }

        $bookingModel->update($bookingId, $update);
        $updated = $bookingModel->find($bookingId);

        if ($previousStatus !== $status) {
            $this->logStatusChange($bookingId, $previousStatus, $status, $note);
        }

        if (is_array($updated) && $status === 'paid' && $previousStatus !== 'paid') {
            (new BookingNotificationService())->sendBookingEmails($updated);
        }

        return redirect()->to(site_url('admin/bookings/' . $bookingId))
            ->with('success', 'Đã cập nhật trạng thái booking.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getStatusLogs(int $bookingId): array
    {
        $db = db_connect();

        if (! $db->tableExists('booking_status_logs')) {
            return [];
        }

        return $db->table('booking_status_logs')
            ->where('booking_id', $bookingId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function logStatusChange(int $bookingId, string $fromStatus, string $toStatus, string $note = ''): void
    {
        $db = db_connect();

        if (! $db->tableExists('booking_status_logs')) {
            return;
        }

        $authUser = session()->get('auth_user');
        $db->table('booking_status_logs')->insert([
            'booking_id' => $bookingId,
            'from_status' => $fromStatus !== '' ? $fromStatus : null,
            'to_status' => $toStatus,
            'actor_user_id' => is_array($authUser) ? (int) ($authUser['id'] ?? 0) ?: null : null,
            'actor_name' => is_array($authUser) ? (string) ($authUser['full_name'] ?? '') : null,
            'actor_email' => is_array($authUser) ? (string) ($authUser['email'] ?? '') : null,
            'note' => $note !== '' ? $note : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
