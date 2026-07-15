<?php

namespace App\Controllers\Admin;

use App\Data\LocalizedPathCatalog;
use App\Models\BookingModel;
use App\Models\PromotionCodeModel;
use App\Services\BookingNotificationService;

class Bookings extends BaseAdminController
{
    private const PAYMENT_STATUSES = ['draft', 'pending_payment', 'pending_transfer', 'paid', 'cancelled', 'failed'];
    private const PAYMENT_METHODS = ['paypal', 'vnpay', 'vietqr'];
    private const RECONCILIATION_FILTERS = ['needs_reconciliation', 'online_paid', 'failed_or_cancelled'];

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $status = $this->normalizeStatus((string) $this->request->getGet('status'));
        $method = $this->normalizePaymentMethod((string) $this->request->getGet('method'));
        $reconciliation = $this->normalizeReconciliationFilter((string) $this->request->getGet('reconciliation'));
        $keyword = trim((string) $this->request->getGet('q'));

        $query = (new BookingModel())->orderBy('created_at', 'DESC');
        $this->applyBookingFilters($query, $status, $method, $reconciliation, $keyword);

        return view('admin/bookings/index', [
            'bookings' => $query->paginate(20),
            'pager' => $query->pager,
            'status' => $status,
            'method' => $method,
            'reconciliation' => $reconciliation,
            'keyword' => $keyword,
            'statusOptions' => self::PAYMENT_STATUSES,
            'methodOptions' => self::PAYMENT_METHODS,
            'reconciliationStats' => $this->buildReconciliationStats(),
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

        $status = $this->normalizeStatus((string) $this->request->getGet('status'));
        $method = $this->normalizePaymentMethod((string) $this->request->getGet('method'));
        $reconciliation = $this->normalizeReconciliationFilter((string) $this->request->getGet('reconciliation'));
        $keyword = trim((string) $this->request->getGet('q'));

        $query = (new BookingModel())->orderBy('created_at', 'DESC');
        $this->applyBookingFilters($query, $status, $method, $reconciliation, $keyword);

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
            'coupon_code',
            'discount_amount_vnd',
            'grand_total',
            'amount_due_vnd',
            'amount_paid_vnd',
            'provider_reference',
            'paid_at',
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
                $booking['coupon_code'] ?? '',
                $booking['discount_amount_vnd'] ?? '',
                $booking['grand_total'] ?? '',
                $booking['amount_due_vnd'] ?? '',
                $booking['amount_paid_vnd'] ?? '',
                $booking['provider_reference'] ?? '',
                $booking['paid_at'] ?? '',
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
            'statusOptions' => array_values(array_diff(self::PAYMENT_STATUSES, ['draft'])),
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
        $amountPaid = $this->parseVndAmount((string) $this->request->getPost('amount_paid_vnd'));
        $providerReference = trim((string) $this->request->getPost('provider_reference'));
        $confirmedPayment = (string) $this->request->getPost('confirm_payment') === '1';
        $sendBookingEmail = $this->request->getPost('send_booking_email') !== null;
        $allowed = array_values(array_diff(self::PAYMENT_STATUSES, ['draft']));

        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Trạng thái thanh toán không hợp lệ.');
        }

        $previousStatus = (string) ($booking['payment_status'] ?? '');
        $update = [
            'payment_status' => $status,
        ];

        if ($status === 'paid') {
            if ($previousStatus !== 'paid' && ! $confirmedPayment) {
                return redirect()->back()->with('error', 'Vui lòng xác nhận đã đối soát giao dịch trước khi chuyển booking sang đã thanh toán.');
            }

            if ($amountPaid <= 0) {
                $amountPaid = (float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0);
            }

            if ($amountPaid <= 0) {
                return redirect()->back()->with('error', 'Số tiền đã thanh toán không hợp lệ.');
            }

            $update['paid_at'] = (string) ($booking['paid_at'] ?? '') !== '' ? $booking['paid_at'] : date('Y-m-d H:i:s');
            $update['amount_paid_vnd'] = $amountPaid;
            $update['provider_reference'] = $providerReference !== '' ? $providerReference : ($booking['provider_reference'] ?? null);
        }

        if ($status === 'cancelled') {
            $update['cancelled_at'] = (string) ($booking['cancelled_at'] ?? '') !== '' ? $booking['cancelled_at'] : date('Y-m-d H:i:s');
        }

        if ($status !== 'paid' && $providerReference !== '') {
            $update['provider_reference'] = $providerReference;
        }

        $bookingModel->update($bookingId, $update);
        $updated = $bookingModel->find($bookingId);

        if ($previousStatus !== $status || $note !== '' || $status === 'paid') {
            $this->logStatusChange(
                $bookingId,
                $previousStatus,
                $status,
                $note,
                $status === 'paid' ? (float) ($update['amount_paid_vnd'] ?? 0) : null,
                (string) ($update['provider_reference'] ?? $booking['provider_reference'] ?? '')
            );
        }

        if (is_array($updated) && $status === 'paid' && $previousStatus !== 'paid') {
            $this->incrementCouponUsage($updated);
        }

        if (is_array($updated) && $sendBookingEmail && $previousStatus !== $status) {
            $notifier = new BookingNotificationService();

            if ($status === 'paid' && $previousStatus !== 'paid') {
                $notifier->sendBookingEmails($updated);
            } else {
                $notifier->sendStatusUpdateEmail($updated, $status, $note);
            }
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

    private function logStatusChange(
        int $bookingId,
        string $fromStatus,
        string $toStatus,
        string $note = '',
        ?float $amountPaid = null,
        string $providerReference = ''
    ): void {
        $db = db_connect();

        if (! $db->tableExists('booking_status_logs')) {
            return;
        }

        $authUser = session()->get('auth_user');
        $payload = [
            'booking_id' => $bookingId,
            'from_status' => $fromStatus !== '' ? $fromStatus : null,
            'to_status' => $toStatus,
            'amount_paid_vnd' => $amountPaid,
            'provider_reference' => $providerReference !== '' ? $providerReference : null,
            'actor_user_id' => is_array($authUser) ? (int) ($authUser['id'] ?? 0) ?: null : null,
            'actor_name' => is_array($authUser) ? (string) ($authUser['full_name'] ?? '') : null,
            'actor_email' => is_array($authUser) ? (string) ($authUser['email'] ?? '') : null,
            'note' => $note !== '' ? $note : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        foreach (['amount_paid_vnd', 'provider_reference'] as $field) {
            if (! $db->fieldExists($field, 'booking_status_logs')) {
                unset($payload[$field]);
            }
        }

        $db->table('booking_status_logs')->insert($payload);
    }

    private function applyBookingFilters(BookingModel $query, string $status, string $method, string $reconciliation, string $keyword): void
    {
        if ($status !== '') {
            $query->where('payment_status', $status);
        }

        if ($method !== '') {
            $query->where('payment_method', $method);
        }

        if ($reconciliation === 'needs_reconciliation') {
            $query->whereIn('payment_status', ['pending_payment', 'pending_transfer'])
                ->whereIn('payment_method', ['vietqr', 'vnpay']);
        } elseif ($reconciliation === 'online_paid') {
            $query->where('payment_status', 'paid')
                ->whereIn('payment_method', ['vietqr', 'vnpay', 'paypal']);
        } elseif ($reconciliation === 'failed_or_cancelled') {
            $query->whereIn('payment_status', ['failed', 'cancelled'])
                ->whereIn('payment_method', ['vnpay', 'paypal']);
        }

        if ($keyword !== '') {
            $query->groupStart()
                ->like('booking_code', $keyword)
                ->orLike('tour_title', $keyword)
                ->orLike('customer_name', $keyword)
                ->orLike('customer_email', $keyword)
                ->orLike('customer_phone', $keyword)
                ->orLike('provider_reference', $keyword)
                ->groupEnd();
        }
    }

    /**
     * @return array<string, int>
     */
    private function buildReconciliationStats(): array
    {
        return [
            'needs_reconciliation' => (new BookingModel())
                ->whereIn('payment_status', ['pending_payment', 'pending_transfer'])
                ->whereIn('payment_method', ['vietqr', 'vnpay'])
                ->countAllResults(),
            'pending_transfer' => (new BookingModel())
                ->where('payment_status', 'pending_transfer')
                ->countAllResults(),
            'pending_payment' => (new BookingModel())
                ->where('payment_status', 'pending_payment')
                ->countAllResults(),
            'failed_or_cancelled' => (new BookingModel())
                ->whereIn('payment_status', ['failed', 'cancelled'])
                ->countAllResults(),
        ];
    }

    private function normalizeStatus(string $status): string
    {
        $status = trim($status);

        return in_array($status, self::PAYMENT_STATUSES, true) ? $status : '';
    }

    private function normalizePaymentMethod(string $method): string
    {
        $method = trim(strtolower($method));

        return in_array($method, self::PAYMENT_METHODS, true) ? $method : '';
    }

    private function normalizeReconciliationFilter(string $filter): string
    {
        $filter = trim($filter);

        return in_array($filter, self::RECONCILIATION_FILTERS, true) ? $filter : '';
    }

    private function parseVndAmount(string $value): float
    {
        $value = trim($value);

        if ($value === '') {
            return 0.0;
        }

        $normalized = preg_replace('/[^\d.,]/', '', $value) ?? '';

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace([',', '.'], '', $normalized);
        }

        return is_numeric($normalized) ? max(0.0, (float) $normalized) : 0.0;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function incrementCouponUsage(array $booking): void
    {
        $couponId = (int) ($booking['coupon_id'] ?? 0);

        if ($couponId <= 0) {
            return;
        }

        $model = new PromotionCodeModel();

        if (! $model->db->tableExists($model->getTable())) {
            return;
        }

        $model->where('id', $couponId)->set('used_count', 'used_count + 1', false)->update();
    }
}
