<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTourTravelerPriceRates extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('tours')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('child_price_rate', 'tours')) {
            $fields['child_price_rate'] = [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'null' => false,
                'default' => '0.8500',
                'after' => 'sale_price',
            ];
        }

        if (! $this->db->fieldExists('infant_price_rate', 'tours')) {
            $fields['infant_price_rate'] = [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'null' => false,
                'default' => '0.2500',
                'after' => 'child_price_rate',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('tours', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('tours')) {
            return;
        }

        foreach (['infant_price_rate', 'child_price_rate'] as $field) {
            if ($this->db->fieldExists($field, 'tours')) {
                $this->forge->dropColumn('tours', $field);
            }
        }
    }
}
