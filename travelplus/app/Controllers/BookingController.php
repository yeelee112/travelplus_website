<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\BookingModel;
use App\Services\BookingNotificationService;
use App\Services\PayPalSandboxService;
use App\Services\VietQrService;

class BookingController extends BaseController
{
    public function proceed()
    {
        $rules = [
            'tour_id' => 'required|is_natural_no_zero',
            'tour_title' => 'required|max_length[255]',
            'adult_quantity' => 'required|is_natural_no_zero',
            'child_quantity' => 'required|is_natural',
            'infant_quantity' => 'required|is_natural',
            'adult_price' => 'required|decimal',
            'child_price' => 'required|decimal',
            'infant_price' => 'required|decimal',
            'grand_total' => 'required|decimal',
            'max_travelers' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Thong tin booking chua hop le.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $adultQty = (int) $this->request->getPost('adult_quantity');
        $childQty = (int) $this->request->getPost('child_quantity');
        $infantQty = (int) $this->request->getPost('infant_quantity');
        $maxTravelers = (int) $this->request->getPost('max_travelers');
        $totalTravelers = $adultQty + $childQty + $infantQty;

        if ($totalTravelers <= 0 || $totalTravelers > $maxTravelers) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'So luong khach khong hop le.',
            ]);
        }

        session()->set('pending_booking', [
            'tour_id' => (int) $this->request->getPost('tour_id'),
            'tour_title' => trim((string) $this->request->getPost('tour_title')),
            'tour_image' => trim((string) $this->request->getPost('tour_image')),
            'tour_link' => trim((string) $this->request->getPost('tour_link')),
            'departure_label' => trim((string) $this->request->getPost('departure_label')),
            'duration_label' => trim((string) $this->request->getPost('duration_label')),
            'adult_quantity' => $adultQty,
            'child_quantity' => $childQty,
            'infant_quantity' => $infantQty,
            'adult_price' => (float) $this->request->getPost('adult_price'),
            'child_price' => (float) $this->request->getPost('child_price'),
            'infant_price' => (float) $this->request->getPost('infant_price'),
            'grand_total' => (float) $this->request->getPost('grand_total'),
            'currency' => 'VND',
            'max_travelers' => $maxTravelers,
            'saved_at' => date('Y-m-d H:i:s'),
        ]);

        session()->remove('current_booking_code');

        if (session()->has('auth_user')) {
            session()->set('checkout_mode', 'member');

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Booking saved.',
                'redirect' => LocalizedPathCatalog::url('booking.checkout'),
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Booking saved temporarily.',
        ]);
    }

    public function continueGuest()
    {
        if (! session()->has('pending_booking')) {
            return redirect()->to(localized_url('/'));
        }

        session()->set('checkout_mode', 'guest');

        return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
    }

    public function checkout()
    {
        $pendingBooking = session()->get('pending_booking');

        if (! is_array($pendingBooking) || $pendingBooking === []) {
            return redirect()->to(localized_url('/'));
        }

        return view('booking/checkout', [
            'pendingBooking' => $pendingBooking,
            'authUser' => session()->get('auth_user'),
            'checkoutMode' => session()->get('checkout_mode') ?: (session()->has('auth_user') ? 'member' : 'guest'),
            'checkoutNotice' => session()->getFlashdata('checkout_notice'),
            'checkoutError' => session()->getFlashdata('checkout_error'),
        ]);
    }

    public function success(string $bookingCode)
    {
        $booking = (new BookingModel())->where('booking_code', $bookingCode)->first();

        if (! is_array($booking)) {
            return redirect()->to(localized_url('/'));
        }

        return view('booking/success', [
            'booking' => $booking,
        ]);
    }

    public function createPayPalOrder()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Khong tim thay thong tin booking de thanh toan.',
            ]);
        }

        $paymentPlan = (string) $this->request->getPost('payment_plan');
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($paymentMethod !== 'paypal') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Cong thanh toan nay chua duoc cau hinh that.',
            ]);
        }

        $customer = $this->extractCustomerPayload();

        if ($customer['error'] !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $customer['error'],
            ]);
        }

        $grandTotal = (float) ($pendingBooking['grand_total'] ?? 0);
        $amountVnd = $paymentPlan === 'full' ? $grandTotal : ($grandTotal * 0.10);
        $amountUsd = $this->convertVndToUsd($amountVnd);

        $paypal = new PayPalSandboxService();

        if (! $paypal->isConfigured()) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'PayPal sandbox credentials are missing.',
            ]);
        }

        try {
            $booking = $this->createOrUpdateBooking(
                $pendingBooking,
                $customer['data'],
                $paymentMethod,
                $paymentPlan,
                $amountVnd,
                'pending_payment'
            );

            $requestLocale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
            $returnUrl = LocalizedPathCatalog::url('booking.paypalReturn', $requestLocale);
            $cancelUrl = LocalizedPathCatalog::url('booking.paypalCancel', $requestLocale);
            $order = $paypal->createOrder($pendingBooking, $amountUsd, $returnUrl, $cancelUrl);
            $approveLink = $this->extractApproveLink($order);

            if ($approveLink === '') {
                throw new \RuntimeException('PayPal approve URL was not returned.');
            }

            $bookingModel = new BookingModel();
            $bookingModel->update((int) $booking['id'], [
                'paypal_order_id' => (string) ($order['id'] ?? ''),
                'paypal_status' => (string) ($order['status'] ?? 'CREATED'),
                'provider_payload' => json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            session()->set('paypal_checkout', [
                'booking_code' => (string) $booking['booking_code'],
                'payment_plan' => $paymentPlan,
                'payment_method' => $paymentMethod,
                'amount_vnd' => $amountVnd,
                'amount_usd' => $amountUsd,
                'order_id' => (string) ($order['id'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            session()->set('current_booking_code', (string) $booking['booking_code']);

            return $this->response->setJSON([
                'ok' => true,
                'redirect' => $approveLink,
            ]);
        } catch (\Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function generateVietQr()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Khong tim thay thong tin booking de thanh toan.',
            ]);
        }

        $paymentPlan = (string) $this->request->getPost('payment_plan');
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($paymentMethod !== 'vietqr') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Phuong thuc thanh toan khong hop le.',
            ]);
        }

        $customer = $this->extractCustomerPayload();

        if ($customer['error'] !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $customer['error'],
            ]);
        }

        $grandTotal = (float) ($pendingBooking['grand_total'] ?? 0);
        $amountVnd = (int) round($paymentPlan === 'full' ? $grandTotal : ($grandTotal * 0.10));

        $vietQr = new VietQrService();

        if (! $vietQr->isConfigured()) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'VietQR chua duoc cau hinh day du.',
            ]);
        }

        try {
            $booking = $this->createOrUpdateBooking(
                $pendingBooking,
                $customer['data'],
                $paymentMethod,
                $paymentPlan,
                $amountVnd,
                'pending_transfer'
            );

            $reference = (string) $booking['booking_code'];
            $qr = $vietQr->generateQr($amountVnd, $reference);
            $receiver = $vietQr->getReceiverMeta();

            (new BookingModel())->update((int) $booking['id'], [
                'provider_reference' => $reference,
                'provider_payload' => json_encode($qr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            session()->set('vietqr_checkout', [
                'booking_code' => (string) $booking['booking_code'],
                'payment_plan' => $paymentPlan,
                'payment_method' => $paymentMethod,
                'amount_vnd' => $amountVnd,
                'reference' => $reference,
                'generated_at' => date('Y-m-d H:i:s'),
            ]);

            session()->set('current_booking_code', (string) $booking['booking_code']);

            return $this->response->setJSON([
                'ok' => true,
                'qr' => [
                    'image' => (string) ($qr['qrDataURL'] ?? ''),
                    'code' => (string) ($qr['qrCode'] ?? ''),
                    'account_name' => (string) ($qr['accountName'] ?? $receiver['accountName']),
                    'account_no' => (string) $receiver['accountNo'],
                    'acq_id' => (string) $receiver['acqId'],
                    'amount' => $amountVnd,
                    'add_info' => $reference,
                    'booking_code' => (string) $booking['booking_code'],
                ],
            ]);
        } catch (\Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function completeVietQr()
    {
        $vietQrCheckout = session()->get('vietqr_checkout');

        if (! is_array($vietQrCheckout) || empty($vietQrCheckout['booking_code'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Khong tim thay giao dich VietQR de hoan tat.',
            ]);
        }

        $bookingCode = (string) $vietQrCheckout['booking_code'];
        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('booking_code', $bookingCode)->first();

        if (! is_array($booking)) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Khong tim thay booking de cap nhat.',
            ]);
        }

        $bookingModel->update((int) $booking['id'], [
            'payment_method' => 'vietqr',
            'payment_plan' => (string) ($vietQrCheckout['payment_plan'] ?? 'deposit'),
            'payment_status' => 'pending_transfer',
            'amount_due_vnd' => (float) ($vietQrCheckout['amount_vnd'] ?? ($booking['amount_due_vnd'] ?? 0)),
            'provider_reference' => (string) ($vietQrCheckout['reference'] ?? $bookingCode),
        ]);

        $updatedBooking = $bookingModel->find((int) $booking['id']);

        if (is_array($updatedBooking)) {
            (new BookingNotificationService())->sendBookingEmails($updatedBooking);
        }

        session()->remove('vietqr_checkout');
        session()->remove('pending_booking');
        session()->remove('current_booking_code');

        return $this->response->setJSON([
            'ok' => true,
            'redirect' => LocalizedPathCatalog::url('booking.successPrefix', $this->request->getLocale() === 'en' ? 'en' : 'vi') . '/' . $bookingCode,
        ]);
    }

    public function paypalReturn()
    {
        $orderId = (string) $this->request->getGet('token');
        $paypalCheckout = session()->get('paypal_checkout');

        if ($orderId === '' || ! is_array($paypalCheckout)) {
            session()->setFlashdata('checkout_error', 'Khong tim thay giao dich PayPal de xac nhan.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        $bookingCode = (string) ($paypalCheckout['booking_code'] ?? '');
        $bookingModel = new BookingModel();
        $booking = $bookingCode !== '' ? $bookingModel->where('booking_code', $bookingCode)->first() : null;

        if (! is_array($booking)) {
            session()->setFlashdata('checkout_error', 'Khong tim thay booking de cap nhat thanh toan.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        $paypal = new PayPalSandboxService();

        try {
            $capture = $paypal->captureOrder($orderId);
            $status = strtoupper((string) ($capture['status'] ?? ''));

            if ($status !== 'COMPLETED') {
                throw new \RuntimeException('Giao dich PayPal chua hoan tat.');
            }

            $captureId = '';
            $purchaseUnits = $capture['purchase_units'] ?? [];

            if (is_array($purchaseUnits)) {
                foreach ($purchaseUnits as $unit) {
                    $payments = $unit['payments']['captures'] ?? [];

                    if (is_array($payments) && isset($payments[0]['id'])) {
                        $captureId = (string) $payments[0]['id'];
                        break;
                    }
                }
            }

            $bookingModel->update((int) $booking['id'], [
                'payment_status' => 'paid',
                'amount_paid_vnd' => (float) ($booking['amount_due_vnd'] ?? 0),
                'paypal_capture_id' => $captureId,
                'paypal_status' => $status,
                'provider_payload' => json_encode($capture, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            $updatedBooking = $bookingModel->find((int) $booking['id']);

            if (is_array($updatedBooking)) {
                (new BookingNotificationService())->sendBookingEmails($updatedBooking);
            }

            session()->remove('paypal_checkout');
            session()->remove('pending_booking');
            session()->remove('current_booking_code');

            return redirect()->to(LocalizedPathCatalog::url('booking.successPrefix', $this->request->getLocale() === 'en' ? 'en' : 'vi') . '/' . $bookingCode);
        } catch (\Throwable $exception) {
            $bookingModel->update((int) $booking['id'], [
                'payment_status' => 'failed',
                'paypal_status' => 'FAILED',
            ]);

            session()->setFlashdata('checkout_error', $exception->getMessage());
        }

        return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
    }

    public function paypalCancel()
    {
        $paypalCheckout = session()->get('paypal_checkout');

        if (is_array($paypalCheckout) && ! empty($paypalCheckout['booking_code'])) {
            (new BookingModel())
                ->where('booking_code', (string) $paypalCheckout['booking_code'])
                ->set([
                    'payment_status' => 'cancelled',
                    'cancelled_at' => date('Y-m-d H:i:s'),
                ])
                ->update();
        }

        session()->remove('paypal_checkout');
        session()->remove('current_booking_code');
        session()->setFlashdata('checkout_error', 'Ban da huy giao dich PayPal.');

        return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
    }

    private function getPendingBooking(): ?array
    {
        $booking = session()->get('pending_booking');

        return is_array($booking) && $booking !== [] ? $booking : null;
    }

    /**
     * @return array{data: array<string, string>, error: string|null}
     */
    private function extractCustomerPayload(): array
    {
        $locale = $this->request->getLocale();
        $fullName = trim((string) $this->request->getPost('full_name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $phone = trim((string) $this->request->getPost('phone'));
        $note = trim((string) $this->request->getPost('note'));

        if ($fullName === '' || $email === '' || $phone === '') {
            return [
                'data' => [],
                'error' => lang('Frontend.checkout.customerInfoRequired', [], $locale),
            ];
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'data' => [],
                'error' => lang('Frontend.checkout.invalidEmail', [], $locale),
            ];
        }

        return [
            'data' => [
                'customer_name' => $fullName,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'customer_note' => $note,
            ],
            'error' => null,
        ];
    }

    /**
     * @param array<string, mixed> $pendingBooking
     * @param array<string, string> $customer
     * @return array<string, mixed>
     */
    private function createOrUpdateBooking(
        array $pendingBooking,
        array $customer,
        string $paymentMethod,
        string $paymentPlan,
        float $amountDueVnd,
        string $paymentStatus
    ): array {
        $bookingModel = new BookingModel();
        $currentCode = (string) session()->get('current_booking_code');
        $existing = $currentCode !== '' ? $bookingModel->where('booking_code', $currentCode)->first() : null;

        $payload = [
            'user_id' => (int) (session()->get('auth_user')['id'] ?? 0) ?: null,
            'tour_id' => (int) ($pendingBooking['tour_id'] ?? 0) ?: null,
            'tour_title' => (string) ($pendingBooking['tour_title'] ?? ''),
            'tour_link' => (string) ($pendingBooking['tour_link'] ?? ''),
            'tour_image' => (string) ($pendingBooking['tour_image'] ?? ''),
            'departure_label' => (string) ($pendingBooking['departure_label'] ?? ''),
            'duration_label' => (string) ($pendingBooking['duration_label'] ?? ''),
            'customer_name' => $customer['customer_name'],
            'customer_email' => $customer['customer_email'],
            'customer_phone' => $customer['customer_phone'],
            'customer_note' => $customer['customer_note'] !== '' ? $customer['customer_note'] : null,
            'adult_quantity' => (int) ($pendingBooking['adult_quantity'] ?? 1),
            'child_quantity' => (int) ($pendingBooking['child_quantity'] ?? 0),
            'infant_quantity' => (int) ($pendingBooking['infant_quantity'] ?? 0),
            'adult_price' => (float) ($pendingBooking['adult_price'] ?? 0),
            'child_price' => (float) ($pendingBooking['child_price'] ?? 0),
            'infant_price' => (float) ($pendingBooking['infant_price'] ?? 0),
            'grand_total' => (float) ($pendingBooking['grand_total'] ?? 0),
            'currency' => (string) ($pendingBooking['currency'] ?? 'VND'),
            'payment_method' => $paymentMethod,
            'payment_plan' => $paymentPlan === 'full' ? 'full' : 'deposit',
            'payment_status' => $paymentStatus,
            'amount_due_vnd' => $amountDueVnd,
        ];

        if (is_array($existing)) {
            $bookingModel->update((int) $existing['id'], $payload);
            $booking = $bookingModel->find((int) $existing['id']);
        } else {
            $payload['booking_code'] = $this->generateBookingCode();
            $bookingModel->insert($payload);
            $booking = $bookingModel->find((int) $bookingModel->getInsertID());
        }

        if (! is_array($booking)) {
            throw new \RuntimeException('Khong the tao booking.');
        }

        session()->set('current_booking_code', (string) $booking['booking_code']);

        return $booking;
    }

    private function generateBookingCode(): string
    {
        $prefix = 'BK' . date('ymd');
        $model = new BookingModel();

        do {
            $code = $prefix . strtoupper(bin2hex(random_bytes(3)));
        } while ($model->where('booking_code', $code)->first() !== null);

        return $code;
    }

    private function convertVndToUsd(float $amountVnd): string
    {
        $rate = (float) env('paypal.vndToUsdRate', 25000);
        $rate = $rate > 0 ? $rate : 25000;

        return number_format($amountVnd / $rate, 2, '.', '');
    }

    /**
     * @param array<string, mixed> $order
     */
    private function extractApproveLink(array $order): string
    {
        $links = $order['links'] ?? [];

        if (! is_array($links)) {
            return '';
        }

        foreach ($links as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                return (string) ($link['href'] ?? '');
            }
        }

        return '';
    }
}
