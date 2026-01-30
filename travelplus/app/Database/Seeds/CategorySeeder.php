<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name_en' => 'Outbound', 'name_vi' => 'Outbound', 'slug' => 'outbound', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Inbound', 'name_vi' => 'Inbound', 'slug' => 'inbound', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Cruise', 'name_vi' => 'Du thuyền', 'slug' => 'cruise', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'VIP', 'name_vi' => 'VIP', 'slug' => 'vip', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Senior', 'name_vi' => 'Người cao tuổi', 'slug' => 'senior', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Solo Traveler', 'name_vi' => 'Du lịch đơn lẻ', 'slug' => 'solo-traveler', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Tour', 'name_vi' => 'Tour', 'slug' => 'tour', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
            ['name_en' => 'Free & Easy', 'name_vi' => 'Free & Easy', 'slug' => 'free-easy', 'image' => 'placeholder-360x200.svg', 'created_at' => date('Y-m-d H:i:s')],
        ];

        $this->db->table('categories')->insertBatch($data);
    }
}
