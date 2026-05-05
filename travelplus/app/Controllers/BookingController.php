<?php

namespace App\Controllers;

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

        if (session()->has('auth_user')) {
            session()->set('checkout_mode', 'member');

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Booking saved.',
                'redirect' => localized_url('booking/checkout'),
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

        return redirect()->to(localized_url('booking/checkout'));
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
        ]);
    }
}
