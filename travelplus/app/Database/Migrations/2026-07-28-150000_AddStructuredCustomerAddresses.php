<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStructuredCustomerAddresses extends Migration
{
    public function up()
    {
        $this->addColumns('users', [
            'address_line' => 'VARCHAR(255) NULL AFTER `address`',
            'province_code' => 'VARCHAR(2) NULL AFTER `address_line`',
            'ward_code' => 'VARCHAR(5) NULL AFTER `province_code`',
        ]);
        $this->addColumns('bookings', [
            'customer_address_line' => 'VARCHAR(255) NULL AFTER `customer_address`',
            'customer_province_code' => 'VARCHAR(2) NULL AFTER `customer_address_line`',
            'customer_ward_code' => 'VARCHAR(5) NULL AFTER `customer_province_code`',
        ]);
    }

    public function down()
    {
        $this->dropColumns('bookings', [
            'customer_address_line',
            'customer_province_code',
            'customer_ward_code',
        ]);
        $this->dropColumns('users', ['address_line', 'province_code', 'ward_code']);
    }

    /**
     * @param array<string, string> $columns
     */
    private function addColumns(string $table, array $columns): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $statements = [];
        foreach ($columns as $name => $definition) {
            if (! $this->db->fieldExists($name, $table)) {
                $statements[] = 'ADD COLUMN `' . $name . '` ' . $definition;
            }
        }

        if ($statements !== []) {
            $this->db->query('ALTER TABLE `' . $table . '` ' . implode(', ', $statements));
        }
    }

    /**
     * @param list<string> $columns
     */
    private function dropColumns(string $table, array $columns): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $statements = [];
        foreach ($columns as $name) {
            if ($this->db->fieldExists($name, $table)) {
                $statements[] = 'DROP COLUMN `' . $name . '`';
            }
        }

        if ($statements !== []) {
            $this->db->query('ALTER TABLE `' . $table . '` ' . implode(', ', $statements));
        }
    }
}
