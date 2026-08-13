<?php

namespace App\Controllers;

class Invitation extends BaseController
{
    public function index()
    {
        return view('invitation/index', [
            'invitationImage' => 'assets/images/invitations/12E%20Invitation.png',
        ]);
    }
}
