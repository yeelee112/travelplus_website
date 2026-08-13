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

    public function ktdlB()
    {
        return view('invitation/ktdl-b', [
            'invitationImage' => 'assets/images/invitations/KTDL%20B%20Invitation.png',
        ]);
    }
}
