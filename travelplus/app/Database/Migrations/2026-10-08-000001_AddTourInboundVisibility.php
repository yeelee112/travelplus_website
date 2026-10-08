<?php

namespace App\Database\Migrations;

use App\Services\PublicContentCacheService;
use CodeIgniter\Database\Migration;

class AddTourInboundVisibility extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('show_on_inbound', 'tours')) {
            $this->forge->addColumn('tours', [
                'show_on_inbound' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            ]);
        }
        $this->refreshCache();
    }

    public function down()
    {
        if ($this->db->fieldExists('show_on_inbound', 'tours')) {
            $this->forge->dropColumn('tours', 'show_on_inbound');
        }
        $this->refreshCache();
    }

    private function refreshCache(): void
    {
        $this->db->resetDataCache();
        cache()->delete('db_field_exists_tours_show_on_inbound');
        (new PublicContentCacheService())->invalidate();
    }
}
