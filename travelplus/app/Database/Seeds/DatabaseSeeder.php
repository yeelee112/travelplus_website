<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('App\\Database\\Seeds\\CategorySeeder');
        $this->call('App\\Database\\Seeds\\TourSeeder');
        $this->call('App\\Database\\Seeds\\PostSeeder');
    }
}
