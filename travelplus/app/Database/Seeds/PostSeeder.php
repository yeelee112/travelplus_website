<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'title_en' => 'Ultimate Travel Planning Guide',
                'title_vi' => 'Hướng dẫn lập kế hoạch du lịch tối ưu',
                'slug' => 'ultimate-travel-planning-guide',
                'content_en' => 'Tips and tricks for smooth travel.',
                'content_vi' => 'Mẹo và thủ thuật cho chuyến đi suôn sẻ.',
                'published_at' => $now,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'Top 10 Travel Hacks',
                'title_vi' => '10 mẹo du lịch hàng đầu',
                'slug' => 'top-10-travel-hacks',
                'content_en' => 'Save money and time.',
                'content_vi' => 'Tiết kiệm tiền và thời gian.',
                'published_at' => $now,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'Discovering Hidden Gems',
                'title_vi' => 'Khám phá những điểm đến bí ẩn',
                'slug' => 'discovering-hidden-gems',
                'content_en' => 'Off-the-beaten-path travel ideas.',
                'content_vi' => 'Ý tưởng đi lại ngoài lộ trình.',
                'published_at' => $now,
                'published' => 1,
                'created_at' => $now,
            ],
        ];

        $this->db->table('posts')->insertBatch($data);
    }
}
