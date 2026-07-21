<?php

namespace App\Controllers;

class Invitation extends BaseController
{
    public function index()
    {
        return view('invitation/index', [
            'invitationImage' => base_url('assets/images/invitations/an-cuu-residence.png'),
        ]);
    }
}
