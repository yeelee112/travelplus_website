<?php

namespace App\Controllers;

class Invitation extends BaseController
{
    public function index()
    {
        return view('invitation/index', [
            // Dùng đường dẫn tương đối để hoạt động đúng với cả domain Laragon
            // và trường hợp dự án được chạy trong một thư mục con.
            'invitationImage' => 'assets/images/invitations/an-cuu-residence.png',
        ]);
    }
}
