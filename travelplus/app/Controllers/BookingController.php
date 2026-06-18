<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\BookingModel;
use App\Models\PromotionCodeModel;
use App\Models\UserModel;
use App\Services\BookingNotificationService;
use App\Services\PayPalSandboxService;
use App\Services\PromotionCodeService;
use App\Services\VietnamPhoneService;
use App\Services\VnpayGatewayService;
use App\Services\VietQrService;

class BookingController extends BaseController
{
    private const DEFAULT_CHILD_PRICE_RATE = 0.85;
    private const DEFAULT_INFANT_PRICE_RATE = 0.25;
    private const DEFAULT_MAX_TRAVELERS = 15;

    public function proceed()
    {
        $rules = [
            'tour_id' => 'required|is_natural_no_zero',
            'adult_quantity' => 'required|is_natural_no_zero',
            'child_quantity' => 'required|is_natural',
            'infant_quantity' => 'required|is_natural',
            'departure_date' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Thông tin booking chưa hợp lệ.',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $adultQty = (int) $this->request->getPost('adult_quantity');
        $childQty = (int) $this->request->getPost('child_quantity');
        $infantQty = (int) $this->request->getPost('infant_quantity');
        $departureDate = trim((string) $this->request->getPost('departure_date'));
        $singleRoomRequested = (string) $this->request->getPost('single_room_requested') === '1';

        if (! $this->isValidTravelerMix($adultQty, $childQty, $infantQty)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $this->travelerMixErrorMessage($adultQty, $childQty, $infantQty),
            ]);
        }

        try {
            $pendingBooking = $this->buildPendingBookingSnapshot(
                (int) $this->request->getPost('tour_id'),
                $departureDate,
                $adultQty,
                $childQty,
                $infantQty,
                $singleRoomRequested,
                trim((string) $this->request->getPost('tour_link'))
            );
        } catch (\RuntimeException $exception) {
            log_message('warning', 'Booking snapshot failed: {message}', ['message' => $exception->getMessage()]);

            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $this->bookingRequestErrorMessage(),
            ]);
        }

        session()->set('pending_booking', $pendingBooking);

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
            'checkoutRetry' => (bool) session()->getFlashdata('checkout_retry'),
        ]);
    }

    public function applyCoupon()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy thông tin booking để áp dụng mã.',
            ]);
        }

        $code = trim((string) $this->request->getPost('coupon_code'));

        if ($code === '') {
            $pendingBooking = $this->clearPendingBookingCoupon($pendingBooking);
            session()->set('pending_booking', $pendingBooking);

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Đã bỏ mã khuyến mãi.',
                'coupon' => null,
                'subtotal' => (float) ($pendingBooking['subtotal_vnd'] ?? 0),
                'discount_amount' => 0.0,
                'grand_total' => (float) ($pendingBooking['grand_total'] ?? 0),
                'deposit_amount' => round(((float) ($pendingBooking['grand_total'] ?? 0)) * 0.10, 0),
            ]);
        }

        $result = (new PromotionCodeService())->applyCode($code, $pendingBooking);

        if (! $result['ok']) {
            return $this->response->setStatusCode(422)->setJSON($result);
        }

        $promotion = is_array($result['code'] ?? null) ? $result['code'] : [];
        $pendingBooking = $this->clearPendingBookingCoupon($pendingBooking);
        $pendingBooking['coupon_id'] = (int) ($promotion['id'] ?? 0) ?: null;
        $pendingBooking['coupon_code'] = strtoupper(trim((string) ($promotion['code'] ?? $code)));
        $pendingBooking['coupon_name'] = trim((string) ($promotion['name'] ?? ''));
        $pendingBooking['discount_amount_vnd'] = (float) ($result['discount_amount'] ?? 0);
        $pendingBooking['grand_total'] = (float) ($result['grand_total'] ?? 0);
        $pendingBooking['coupon_snapshot'] = [
            'id' => (int) ($promotion['id'] ?? 0),
            'code' => $pendingBooking['coupon_code'],
            'name' => $pendingBooking['coupon_name'],
            'discount_type' => (string) ($promotion['discount_type'] ?? ''),
            'discount_value' => (float) ($promotion['discount_value'] ?? 0),
            'discount_amount_vnd' => (float) ($result['discount_amount'] ?? 0),
        ];
        session()->set('pending_booking', $pendingBooking);

        return $this->response->setJSON([
            'ok' => true,
            'message' => $result['message'],
            'coupon' => [
                'code' => (string) ($pendingBooking['coupon_code'] ?? ''),
                'name' => (string) ($pendingBooking['coupon_name'] ?? ''),
            ],
            'subtotal' => (float) ($pendingBooking['subtotal_vnd'] ?? 0),
            'discount_amount' => (float) ($pendingBooking['discount_amount_vnd'] ?? 0),
            'grand_total' => (float) ($pendingBooking['grand_total'] ?? 0),
            'deposit_amount' => round(((float) ($pendingBooking['grand_total'] ?? 0)) * 0.10, 0),
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
            'departureFrom' => $this->resolveBookingDepartureFrom((int) ($booking['tour_id'] ?? 0), $this->request->getLocale()),
            'bookingTourType' => $this->resolveBookingTourType((int) ($booking['tour_id'] ?? 0)),
        ]);
    }

    private function resolveBookingTourType(int $tourId): string
    {
        if ($tourId <= 0) {
            return '';
        }

        $db = db_connect();

        if (! $db->tableExists('tours')) {
            return '';
        }

        $row = $db->table('tours')
            ->select('tour_type')
            ->where('id', $tourId)
            ->get(1)
            ->getRowArray();

        return trim((string) ($row['tour_type'] ?? ''));
    }

    private function resolveBookingDepartureFrom(int $tourId, string $locale): string
    {
        if ($tourId <= 0) {
            return '';
        }

        $db = db_connect();

        if (! $db->tableExists('tours') || ! $db->tableExists('locations') || ! $db->tableExists('location_translations')) {
            return '';
        }

        $row = $db->table('tours t')
            ->select('COALESCE(ltn.name, ltvi.name, l.code, "") AS departure_from', false)
            ->join('locations l', 'l.id = t.departure_location_id', 'left')
            ->join('location_translations ltn', 'ltn.location_id = l.id AND ltn.locale = ' . $db->escape($locale), 'left')
            ->join('location_translations ltvi', 'ltvi.location_id = l.id AND ltvi.locale = ' . $db->escape('vi'), 'left')
            ->where('t.id', $tourId)
            ->get(1)
            ->getRowArray();

        return trim((string) ($row['departure_from'] ?? ''));
    }

    public function createPayPalOrder()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy thông tin booking để thanh toán.',
            ]);
        }

        $paymentPlan = $this->normalizePaymentPlan((string) $this->request->getPost('payment_plan'));
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($paymentPlan === null || $paymentMethod !== 'paypal') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Thông tin thanh toán không hợp lệ.',
            ]);
        }

        $customer = $this->extractCustomerPayload();

        if ($customer['error'] !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $customer['error'],
            ]);
        }

        $this->syncAuthenticatedCustomerProfile($customer['data']);

        $grandTotal = (float) ($pendingBooking['grand_total'] ?? 0);
        $amountVnd = $paymentPlan === 'full' ? $grandTotal : ($grandTotal * 0.10);
        $amountUsd = $this->convertVndToUsd($amountVnd);

        $paypal = new PayPalSandboxService();

        if (! $paypal->isConfigured()) {
            log_message('error', 'PayPal payment requested but gateway credentials are missing.');

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
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
            log_message('error', 'PayPal order creation failed: {message}', ['message' => $exception->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
            ]);
        }
    }

    public function generateVietQr()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy thông tin booking để thanh toán.',
            ]);
        }

        $paymentPlan = $this->normalizePaymentPlan((string) $this->request->getPost('payment_plan'));
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($paymentPlan === null || $paymentMethod !== 'vietqr') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Phương thức thanh toán không hợp lệ.',
            ]);
        }

        $customer = $this->extractCustomerPayload();

        if ($customer['error'] !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $customer['error'],
            ]);
        }

        $this->syncAuthenticatedCustomerProfile($customer['data']);

        $grandTotal = (float) ($pendingBooking['grand_total'] ?? 0);
        $amountVnd = (int) round($paymentPlan === 'full' ? $grandTotal : ($grandTotal * 0.10));

        $vietQr = new VietQrService();

        if (! $vietQr->isConfigured()) {
            log_message('error', 'VietQR payment requested but gateway configuration is incomplete.');

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
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
            log_message('error', 'VietQR generation failed: {message}', ['message' => $exception->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
            ]);
        }
    }

    public function createVnpayPayment()
    {
        $pendingBooking = $this->getPendingBooking();

        if ($pendingBooking === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy thông tin booking để thanh toán.',
            ]);
        }

        $paymentPlan = $this->normalizePaymentPlan((string) $this->request->getPost('payment_plan'));
        $paymentMethod = (string) $this->request->getPost('payment_method');

        if ($paymentPlan === null || $paymentMethod !== 'vnpay') {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Phương thức thanh toán không hợp lệ.',
            ]);
        }

        $customer = $this->extractCustomerPayload();

        if ($customer['error'] !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $customer['error'],
            ]);
        }

        $this->syncAuthenticatedCustomerProfile($customer['data']);

        $grandTotal = (float) ($pendingBooking['grand_total'] ?? 0);
        $amountVnd = (int) round($paymentPlan === 'full' ? $grandTotal : ($grandTotal * 0.10));
        $vnpay = new VnpayGatewayService();

        if (! $vnpay->isConfigured()) {
            log_message('error', 'VNPAY payment requested but gateway configuration is incomplete.');

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
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
            $returnUrl = LocalizedPathCatalog::url('booking.vnpayReturn', $requestLocale);
            $paymentUrl = $vnpay->createCardPaymentUrl(
                $pendingBooking,
                $amountVnd,
                $returnUrl,
                (string) $this->request->getIPAddress(),
                $requestLocale,
                (string) $booking['booking_code']
            );

            $bookingModel = new BookingModel();
            $bookingModel->update((int) $booking['id'], [
                'provider_reference' => (string) $booking['booking_code'],
                'provider_payload' => json_encode([
                    'gateway' => 'vnpay',
                    'payment_url' => $paymentUrl,
                    'amount_vnd' => $amountVnd,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            session()->set('vnpay_checkout', [
                'booking_code' => (string) $booking['booking_code'],
                'payment_plan' => $paymentPlan,
                'payment_method' => $paymentMethod,
                'amount_vnd' => $amountVnd,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            session()->set('current_booking_code', (string) $booking['booking_code']);

            return $this->response->setJSON([
                'ok' => true,
                'redirect' => $paymentUrl,
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'VNPAY payment creation failed: {message}', ['message' => $exception->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => $this->paymentRequestErrorMessage(),
            ]);
        }
    }

    public function completeVietQr()
    {
        $vietQrCheckout = session()->get('vietqr_checkout');

        if (! is_array($vietQrCheckout) || empty($vietQrCheckout['booking_code'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy giao dịch VietQR để hoàn tất.',
            ]);
        }

        $bookingCode = (string) $vietQrCheckout['booking_code'];
        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('booking_code', $bookingCode)->first();

        if (! is_array($booking)) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Không tìm thấy booking để cập nhật.',
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
            $this->incrementCouponUsage($booking, $updatedBooking);
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
            session()->setFlashdata('checkout_error', 'Không tìm thấy giao dịch PayPal để xác nhận.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        $bookingCode = (string) ($paypalCheckout['booking_code'] ?? '');
        $bookingModel = new BookingModel();
        $booking = $bookingCode !== '' ? $bookingModel->where('booking_code', $bookingCode)->first() : null;

        if (! is_array($booking)) {
            session()->setFlashdata('checkout_error', 'Không tìm thấy booking để cập nhật thanh toán.');
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
                $this->incrementCouponUsage($booking, $updatedBooking);
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

            log_message('error', 'PayPal capture failed: {message}', ['message' => $exception->getMessage()]);
            session()->setFlashdata('checkout_error', $this->paymentRequestErrorMessage());
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
        session()->setFlashdata('checkout_error', 'Bạn đã hủy giao dịch PayPal.');

        return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
    }

    public function vnpayReturn()
    {
        $vnpay = new VnpayGatewayService();
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $result = $vnpay->validateReturnData($this->request->getGet(), $locale);

        if (! $result['is_valid']) {
            session()->setFlashdata('checkout_error', 'Chữ ký xác thực VNPAY không hợp lệ.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('booking_code', $result['txn_ref'])->first();

        if (! is_array($booking)) {
            session()->setFlashdata('checkout_error', 'Không tìm thấy booking để cập nhật thanh toán.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        if ((string) ($booking['payment_status'] ?? '') === 'paid') {
            session()->remove('vnpay_checkout');
            session()->remove('pending_booking');
            session()->remove('current_booking_code');

            return redirect()->to(
                LocalizedPathCatalog::url('booking.successPrefix', $locale)
                . '/' . (string) $booking['booking_code']
            );
        }

        if ((float) ($booking['amount_due_vnd'] ?? 0) !== (float) $result['amount_vnd']) {
            session()->setFlashdata('checkout_error', 'Số tiền giao dịch VNPAY không khớp với booking.');
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        if (! $result['is_success']) {
            $bookingModel->update((int) $booking['id'], [
                'payment_status' => $result['response_code'] === '24' ? 'cancelled' : 'failed',
                'provider_payload' => json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'cancelled_at' => $result['response_code'] === '24' ? date('Y-m-d H:i:s') : null,
            ]);

            if ($result['response_code'] === '11') {
                session()->setFlashdata('checkout_error', lang('Frontend.checkout.vnpayExpired', [], $locale));
                session()->setFlashdata('checkout_retry', true);
                return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
            }

            if ($result['response_code'] === '24') {
                session()->setFlashdata('checkout_error', lang('Frontend.checkout.vnpayCancelled', [], $locale));
                session()->setFlashdata('checkout_retry', true);
                return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
            }

            session()->setFlashdata('checkout_error', $result['message']);
            return redirect()->to(LocalizedPathCatalog::url('booking.checkout'));
        }

        $bookingModel->update((int) $booking['id'], [
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'amount_paid_vnd' => (float) ($booking['amount_due_vnd'] ?? $result['amount_vnd']),
            'provider_reference' => $result['transaction_no'] !== '' ? $result['transaction_no'] : $result['txn_ref'],
            'provider_payload' => json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $updatedBooking = $bookingModel->find((int) $booking['id']);

        if (is_array($updatedBooking)) {
            $this->incrementCouponUsage($booking, $updatedBooking);
            (new BookingNotificationService())->sendBookingEmails($updatedBooking);
        }

        session()->remove('vnpay_checkout');
        session()->remove('pending_booking');
        session()->remove('current_booking_code');

        return redirect()->to(
            LocalizedPathCatalog::url('booking.successPrefix', $locale)
            . '/' . (string) $booking['booking_code']
        );
    }

    public function vnpayIpn()
    {
        $vnpay = new VnpayGatewayService();
        $result = $vnpay->validateReturnData($this->request->getGet(), 'vi');

        if (! $result['is_valid']) {
            return $this->response->setJSON(['RspCode' => '97', 'Message' => 'Invalid checksum']);
        }

        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('booking_code', $result['txn_ref'])->first();

        if (! is_array($booking)) {
            return $this->response->setJSON(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if ((float) ($booking['amount_due_vnd'] ?? 0) !== (float) $result['amount_vnd']) {
            return $this->response->setJSON(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if ((string) ($booking['payment_status'] ?? '') === 'paid') {
            return $this->response->setJSON(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        if (! $result['is_success']) {
            $bookingModel->update((int) $booking['id'], [
                'payment_status' => $result['response_code'] === '24' ? 'cancelled' : 'failed',
                'provider_payload' => json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'cancelled_at' => $result['response_code'] === '24' ? date('Y-m-d H:i:s') : null,
            ]);

            return $this->response->setJSON(['RspCode' => '00', 'Message' => 'Confirm failed']);
        }

        $bookingModel->update((int) $booking['id'], [
            'payment_method' => 'vnpay',
            'payment_status' => 'paid',
            'amount_paid_vnd' => (float) ($booking['amount_due_vnd'] ?? $result['amount_vnd']),
            'provider_reference' => $result['transaction_no'] !== '' ? $result['transaction_no'] : $result['txn_ref'],
            'provider_payload' => json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $updatedBooking = $bookingModel->find((int) $booking['id']);

        if (is_array($updatedBooking)) {
            $this->incrementCouponUsage($booking, $updatedBooking);
            (new BookingNotificationService())->sendBookingEmails($updatedBooking);
        }

        return $this->response->setJSON(['RspCode' => '00', 'Message' => 'Confirm success']);
    }

    private function bookingRequestErrorMessage(): string
    {
        return $this->request->getLocale() === 'en'
            ? 'The booking request could not be processed. Please check your selection or contact Travel Plus.'
            : 'Không thể xử lý yêu cầu booking lúc này. Vui lòng kiểm tra lại lựa chọn hoặc liên hệ Travel Plus.';
    }

    private function travelerMixErrorMessage(int $adultQty, int $childQty, int $infantQty): string
    {
        if ($this->request->getLocale() === 'en') {
            if ($adultQty < 1) {
                return 'At least 1 adult is required.';
            }

            if ($infantQty > $adultQty) {
                return 'Infants cannot exceed the number of adults.';
            }

            return 'Child and infant travelers cannot exceed 2 times the number of adults.';
        }

        if ($adultQty < 1) {
            return 'Cần ít nhất 1 người lớn.';
        }

        if ($infantQty > $adultQty) {
            return 'Số lượng em bé không được vượt quá số lượng người lớn.';
        }

        return 'Tổng số trẻ em và em bé không được vượt quá 2 lần số lượng người lớn.';
    }

    private function paymentRequestErrorMessage(): string
    {
        return $this->request->getLocale() === 'en'
            ? 'The payment request could not be processed right now. Please try again or contact Travel Plus.'
            : 'Không thể xử lý thanh toán lúc này. Vui lòng thử lại hoặc liên hệ Travel Plus.';
    }

    private function getPendingBooking(): ?array
    {
        $booking = session()->get('pending_booking');

        return is_array($booking) && $booking !== [] ? $booking : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPendingBookingSnapshot(
        int $tourId,
        string $departureDate,
        int $adultQty,
        int $childQty,
        int $infantQty,
        bool $singleRoomRequested = false,
        string $postedTourLink = ''
    ): array {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $db = db_connect();

        foreach (['tours', 'tour_translations'] as $table) {
            if (! $db->tableExists($table)) {
                throw new \RuntimeException('Dữ liệu tour chưa sẵn sàng để đặt chỗ.');
            }
        }

        $totalTravelers = $adultQty + $childQty + $infantQty;
        if ($totalTravelers <= 0) {
            throw new \RuntimeException('Số lượng khách không hợp lệ.');
        }

        if (! $this->isValidTravelerMix($adultQty, $childQty, $infantQty)) {
            throw new \RuntimeException('Số lượng trẻ em/em bé vượt quá giới hạn cho phép.');
        }

        $tourFields = $db->getFieldNames('tours');
        $has = static fn(string $field): bool => in_array($field, $tourFields, true);
        $select = [
            't.id',
            't.tour_type',
            't.duration_days',
            't.duration_nights',
            't.thumbnail',
            'COALESCE(tt.name, tt_vi.name, CONCAT("Tour #", t.id)) AS tour_title',
            'COALESCE(tt.slug, tt_vi.slug, CONCAT("tour-", t.id)) AS tour_slug',
        ];

        foreach (['max_travelers', 'base_price', 'sale_price', 'currency', 'child_price_rate', 'infant_price_rate', 'single_room_supplement'] as $field) {
            if ($has($field)) {
                $select[] = 't.' . $field;
            }
        }

        $tour = $db->table('tours t')
            ->select(implode(', ', $select), false)
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $db->escape($locale), 'left')
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
            ->where('t.id', $tourId)
            ->where('t.status', 'published')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($tour)) {
            throw new \RuntimeException('Tour không tồn tại hoặc chưa được công bố.');
        }

        $departure = $this->resolveDepartureRow($db, $tourId, $departureDate);
        $basePrice = (float) ($tour['sale_price'] ?? 0);
        if ($basePrice <= 0) {
            $basePrice = (float) ($tour['base_price'] ?? 0);
        }

        $adultPrice = (float) ($departure['price'] ?? 0);
        if ($adultPrice <= 0) {
            $adultPrice = $basePrice;
        }

        if ($adultPrice <= 0) {
            throw new \RuntimeException('Tour chưa có giá hợp lệ để đặt chỗ.');
        }

        $maxTravelers = (int) ($tour['max_travelers'] ?? self::DEFAULT_MAX_TRAVELERS);
        $maxTravelers = $maxTravelers > 0 ? $maxTravelers : self::DEFAULT_MAX_TRAVELERS;
        $availableSlots = (int) ($departure['available_slots'] ?? 0);

        if ($availableSlots > 0) {
            $maxTravelers = min($maxTravelers, $availableSlots);
        }

        if ($totalTravelers > $maxTravelers) {
            throw new \RuntimeException('Số lượng khách vượt quá số chỗ còn nhận.');
        }

        $childRate = $this->normalizeTravelerPriceRate($tour['child_price_rate'] ?? null, self::DEFAULT_CHILD_PRICE_RATE);
        $infantRate = $this->normalizeTravelerPriceRate($tour['infant_price_rate'] ?? null, self::DEFAULT_INFANT_PRICE_RATE);
        $childPrice = round($adultPrice * $childRate, 0);
        $infantPrice = round($adultPrice * $infantRate, 0);
        $eligibleSubtotal = ($adultQty * $adultPrice) + ($childQty * $childPrice) + ($infantQty * $infantPrice);
        $singleRoomSupplement = $singleRoomRequested ? max(0, (float) ($tour['single_room_supplement'] ?? 0)) : 0.0;
        $subtotal = $eligibleSubtotal + $singleRoomSupplement;
        $departureValue = (string) ($departure['departure_date'] ?? '');

        return [
            'tour_id' => $tourId,
            'tour_title' => trim((string) ($tour['tour_title'] ?? ('Tour #' . $tourId))),
            'tour_image' => $this->resolveTourImage($tourId, (string) ($tour['thumbnail'] ?? '')),
            'tour_link' => $this->sanitizeInternalUrl($postedTourLink) ?? '',
            'departure_date' => $departureValue,
            'departure_label' => $this->formatDisplayDate($departureValue),
            'duration_label' => $this->formatDurationLabel(
                (int) ($tour['duration_days'] ?? 0),
                (int) ($tour['duration_nights'] ?? 0),
                $locale
            ),
            'adult_quantity' => $adultQty,
            'child_quantity' => $childQty,
            'infant_quantity' => $infantQty,
            'adult_price' => $adultPrice,
            'child_price' => $childPrice,
            'infant_price' => $infantPrice,
            'single_room_requested' => $singleRoomRequested,
            'single_room_supplement_vnd' => $singleRoomSupplement,
            'coupon_eligible_subtotal_vnd' => $eligibleSubtotal,
            'subtotal_vnd' => $subtotal,
            'discount_amount_vnd' => 0.0,
            'coupon_id' => null,
            'coupon_code' => '',
            'coupon_name' => '',
            'coupon_snapshot' => null,
            'grand_total' => $subtotal,
            'currency' => (string) ($tour['currency'] ?? 'VND'),
            'max_travelers' => $maxTravelers,
            'saved_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function normalizeTravelerPriceRate($value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $rate = (float) $value;

        if ($rate < 0 || $rate > 1) {
            return $default;
        }

        return $rate;
    }

    private function isValidTravelerMix(int $adultQty, int $childQty, int $infantQty): bool
    {
        if ($adultQty < 1 || $childQty < 0 || $infantQty < 0) {
            return false;
        }

        if ($infantQty > $adultQty) {
            return false;
        }

        return ($childQty + $infantQty) <= ($adultQty * 2);
    }

    private function resolveDepartureRow($db, int $tourId, string $departureDate): array
    {
        if (! $db->tableExists('tour_departures')) {
            return [];
        }

        if ($departureDate === '') {
            throw new \RuntimeException('Vui lòng chọn ngày khởi hành.');
        }

        $builder = $db->table('tour_departures')
            ->where('tour_id', $tourId)
            ->where('status', 'open')
            ->where('departure_date >=', date('Y-m-d'))
            ->where('DATE(departure_date)', $departureDate);

        $row = $builder
            ->orderBy('departure_date', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            throw new \RuntimeException('Ngày khởi hành không hợp lệ hoặc đã hết chỗ.');
        }

        return $row;
    }

    private function resolveTourImage(int $tourId, string $fallback): string
    {
        $path = trim($fallback);
        $db = db_connect();

        if ($tourId > 0 && $db->tableExists('tour_media')) {
            $row = $db->table('tour_media')
                ->select('file_path')
                ->where('tour_id', $tourId)
                ->whereIn('type', ['banner', 'cover'])
                ->orderBy('FIELD(type, "banner", "cover")', '', false)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (is_array($row) && trim((string) ($row['file_path'] ?? '')) !== '') {
                $path = trim((string) $row['file_path']);
            }
        }

        if ($path === '') {
            return base_url('assets/images/avt-tour-01.jpg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return base_url($path);
        }

        return base_url('assets/images/' . ltrim($path, '/'));
    }

    private function sanitizeInternalUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $baseUrl = rtrim((string) base_url('/'), '/');

        if (str_starts_with($url, '/')) {
            $candidate = base_url(ltrim($url, '/'));
            return str_starts_with($candidate, $baseUrl) ? $candidate : null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return str_starts_with($url, $baseUrl) ? $url : null;
    }

    private function formatDisplayDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $timestamp = strtotime($date);

        return $timestamp ? date('d/m/Y', $timestamp) : $date;
    }

    private function formatDurationLabel(int $days, int $nights, string $locale): string
    {
        if ($days <= 0 && $nights <= 0) {
            return '';
        }

        return $locale === 'en'
            ? sprintf('%02d Days / %02d Nights', max(0, $days), max(0, $nights))
            : sprintf('%02d Ngay / %02d Dem', max(0, $days), max(0, $nights));
    }

    private function normalizePaymentPlan(string $paymentPlan): ?string
    {
        $paymentPlan = trim($paymentPlan);

        return in_array($paymentPlan, ['full', 'deposit'], true) ? $paymentPlan : null;
    }

    /**
     * @return array{data: array<string, string>, error: string|null}
     */
    private function extractCustomerPayload(): array
    {
        $locale = $this->request->getLocale();
        $fullName = trim((string) $this->request->getPost('full_name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $phone = VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
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

        if (! VietnamPhoneService::isValid($phone)) {
            return [
                'data' => [],
                'error' => $locale === 'en'
                    ? 'Please enter a valid Vietnamese phone number.'
                    : 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.',
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
            'single_room_requested' => ! empty($pendingBooking['single_room_requested']) ? 1 : 0,
            'single_room_supplement_vnd' => (float) ($pendingBooking['single_room_supplement_vnd'] ?? 0),
            'subtotal_vnd' => (float) ($pendingBooking['subtotal_vnd'] ?? $pendingBooking['grand_total'] ?? 0),
            'discount_amount_vnd' => (float) ($pendingBooking['discount_amount_vnd'] ?? 0),
            'coupon_id' => (int) ($pendingBooking['coupon_id'] ?? 0) ?: null,
            'coupon_code' => $this->nullableString((string) ($pendingBooking['coupon_code'] ?? '')),
            'coupon_snapshot' => $this->encodeCouponSnapshot($pendingBooking['coupon_snapshot'] ?? null),
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
            throw new \RuntimeException('Không thể tạo booking.');
        }

        session()->set('current_booking_code', (string) $booking['booking_code']);

        return $booking;
    }

    /**
     * @param array<string, mixed> $pendingBooking
     * @return array<string, mixed>
     */
    private function clearPendingBookingCoupon(array $pendingBooking): array
    {
        $pendingBooking['coupon_id'] = null;
        $pendingBooking['coupon_code'] = '';
        $pendingBooking['coupon_name'] = '';
        $pendingBooking['coupon_snapshot'] = null;
        $pendingBooking['discount_amount_vnd'] = 0.0;
        $pendingBooking['grand_total'] = (float) ($pendingBooking['subtotal_vnd'] ?? $pendingBooking['grand_total'] ?? 0);

        return $pendingBooking;
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param mixed $snapshot
     */
    private function encodeCouponSnapshot($snapshot): ?string
    {
        if (! is_array($snapshot) || $snapshot === []) {
            return null;
        }

        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    private function incrementCouponUsage(array $before, array $after): void
    {
        if ((string) ($before['payment_status'] ?? '') === 'paid' || (string) ($after['payment_status'] ?? '') !== 'paid') {
            return;
        }

        $couponId = (int) ($after['coupon_id'] ?? 0);

        if ($couponId <= 0) {
            return;
        }

        $model = new PromotionCodeModel();

        if (! $model->db->tableExists($model->getTable())) {
            return;
        }

        $model->where('id', $couponId)->set('used_count', 'used_count + 1', false)->update();
    }

    /**
     * @param array<string, string> $customer
     */
    private function syncAuthenticatedCustomerProfile(array $customer): void
    {
        $authUser = session()->get('auth_user');

        if (! is_array($authUser) || empty($authUser['id'])) {
            return;
        }

        $userId = (int) $authUser['id'];
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! is_array($user)) {
            return;
        }

        $payload = [];
        $newFullName = trim((string) ($customer['customer_name'] ?? ''));
        $newPhone = trim((string) ($customer['customer_phone'] ?? ''));

        if ($newFullName !== '' && $newFullName !== (string) ($user['full_name'] ?? '')) {
            $payload['full_name'] = $newFullName;
        }

        if ($newPhone !== '' && $newPhone !== (string) ($user['phone'] ?? '')) {
            $payload['phone'] = $newPhone;
        }

        if ($payload === []) {
            return;
        }

        $payload['updated_at'] = date('Y-m-d H:i:s');
        $userModel->update($userId, $payload);

        $freshUser = $userModel->find($userId);
        if (is_array($freshUser)) {
            session()->set('auth_user', $this->buildAuthSessionUser($freshUser));
        }
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
