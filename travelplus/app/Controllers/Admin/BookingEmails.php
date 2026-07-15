<?php

namespace App\Controllers\Admin;

use App\Services\BookingReminderDispatchService;

class BookingEmails extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $type = 'payment';
        $limit = $this->normalizeLimit((string) $this->request->getGet('limit'));
        $preview = (new BookingReminderDispatchService())->dispatch($type, $limit, true);

        return view('admin/booking_emails/index', [
            'adminSection' => 'booking_emails',
            'type' => $type,
            'limit' => $limit,
            'preview' => $preview,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function send()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $type = 'payment';
        $limit = $this->normalizeLimit((string) $this->request->getPost('limit'));
        $confirmed = (string) $this->request->getPost('confirm_send') === '1';
        $bookingIds = $this->normalizeBookingIds($this->request->getPost('booking_ids'));

        if (! $confirmed) {
            return redirect()->to(site_url('admin/booking-emails?' . http_build_query(['type' => $type, 'limit' => $limit])))
                ->with('error', 'Vui lòng tick xác nhận trước khi gửi email reminder.');
        }

        if ($bookingIds === []) {
            return redirect()->to(site_url('admin/booking-emails?' . http_build_query(['type' => $type, 'limit' => $limit])))
                ->with('error', 'Vui lòng chọn ít nhất một booking để gửi email.');
        }

        $result = (new BookingReminderDispatchService())->dispatch($type, count($bookingIds), false, $bookingIds);

        if ($result['errors'] !== []) {
            return redirect()->to(site_url('admin/booking-emails?' . http_build_query(['type' => $type, 'limit' => $limit])))
                ->with('error', implode(' ', $result['errors']));
        }

        return redirect()->to(site_url('admin/booking-emails?' . http_build_query(['type' => $type, 'limit' => $limit])))
            ->with('success', sprintf('Đã gửi %d email. Bỏ qua %d email.', $result['sent'], $result['skipped']));
    }

    private function normalizeLimit(string $limit): int
    {
        return max(1, min(200, (int) ($limit !== '' ? $limit : 50)));
    }

    /**
     * @return list<int>
     */
    private function normalizeBookingIds($value): array
    {
        $values = is_array($value) ? $value : [];

        return array_values(array_unique(array_filter(array_map(
            static fn($id): int => (int) $id,
            $values
        ), static fn(int $id): bool => $id > 0)));
    }
}
