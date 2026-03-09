<?php

namespace App\Controllers;

class Contact extends BaseController
{
    public function index()
    {
        if ($this->request->getMethod() === 'POST') {

            $token = $this->request->getPost('recaptcha_token');
            $secretKey = '6LfgBncsAAAAAKI2vlFIqagVly-ckVVTFcGSe8lG';

            $client = \Config\Services::curlrequest(
    [ 'verify' => false ]
            );

            $response = $client->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'form_params' => [
                        'secret'   => $secretKey,
                        'response' => $token,
                    ]
                ]
            );

            $result = json_decode($response->getBody());

            // 🔥 Kiểm tra
            if (!$result->success || $result->score < 0.5) {
                return redirect()->back()->with('error', 'Spam detected!');
            }

            // ✅ Captcha hợp lệ → xử lý form
            $name = $this->request->getPost('name');

            return redirect()->back()->with('success', 'Form submitted successfully!');
        }

        $data['breadcrumbs'] = [
            [
                'label' => 'Trang chủ',
                'url'   => base_url()
            ],
            [
                'label' => 'Liên hệ'
            ]
        ];

        return view('contact/index', $data);
    }
}