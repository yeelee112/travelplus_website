<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerAddresses extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && ! $this->db->fieldExists('address', 'users')) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `address` VARCHAR(500) NULL AFTER `phone`');
        }

        if ($this->db->tableExists('bookings') && ! $this->db->fieldExists('customer_address', 'bookings')) {
            $this->db->query('ALTER TABLE `bookings` ADD COLUMN `customer_address` VARCHAR(500) NULL AFTER `customer_phone`');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('bookings') && $this->db->fieldExists('customer_address', 'bookings')) {
            $this->db->query('ALTER TABLE `bookings` DROP COLUMN `customer_address`');
        }

        if ($this->db->tableExists('users') && $this->db->fieldExists('address', 'users')) {
            $this->db->query('ALTER TABLE `users` DROP COLUMN `address`');
        }
    }
}
