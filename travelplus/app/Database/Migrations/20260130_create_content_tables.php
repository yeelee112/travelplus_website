<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentTables extends Migration
{
    public function up()
    {
        // categories
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name_en' => ['type' => 'VARCHAR', 'constraint' => 255],
            'name_vi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('categories', true);

        // tours
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title_en' => ['type' => 'VARCHAR', 'constraint' => 255],
            'title_vi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description_en' => ['type' => 'TEXT', 'null' => true],
            'description_vi' => ['type' => 'TEXT', 'null' => true],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'category_id' => ['type' => 'INT', 'null' => true],
            'published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tours', true);

        // posts
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title_en' => ['type' => 'VARCHAR', 'constraint' => 255],
            'title_vi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'content_en' => ['type' => 'TEXT', 'null' => true],
            'content_vi' => ['type' => 'TEXT', 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('posts', true);
    }

    public function down()
    {
        $this->forge->dropTable('posts', true);
        $this->forge->dropTable('tours', true);
        $this->forge->dropTable('categories', true);
    }
}
