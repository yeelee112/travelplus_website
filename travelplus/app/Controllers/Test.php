<?php

namespace App\Controllers;

class Test extends BaseController
{
    public function db()
    {
        $db = \Config\Database::connect();

        return [
            'username' => $db->getUsername(),
            'database' => $db->getDatabase(),
            'driver'   => $db->getPlatform(),
        ];
    }
}