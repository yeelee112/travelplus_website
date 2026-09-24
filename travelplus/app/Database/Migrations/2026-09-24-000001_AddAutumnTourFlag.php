<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAutumnTourFlag extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_autumn', 'tours')) {
            $this->forge->addColumn('tours', [
                'is_autumn' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            ]);
        }
        $this->db->resetDataCache();
        cache()->delete('db_field_exists_tours_is_autumn');
    }

    public function down()
    {
        if ($this->db->fieldExists('is_autumn', 'tours')) {
            $this->forge->dropColumn('tours', 'is_autumn');
        }
        $this->db->resetDataCache();
        cache()->delete('db_field_exists_tours_is_autumn');
    }
}
