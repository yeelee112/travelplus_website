<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'title_en' => 'Discover Europe: Italy - France - Spain',
                'title_vi' => 'Khám phá Châu Âu: Ý - Pháp - Tây Ban Nha',
                'slug' => 'discover-europe-italy-france-spain',
                'description_en' => 'Explore the best of Europe in this 10-day tour.',
                'description_vi' => 'Khám phá Châu Âu trong chuyến đi 10 ngày.',
                'price' => '5000.00',
                'image' => 'placeholder-360x200.svg',
                'category_id' => 1,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'Tropical Paradise: Thailand Getaway',
                'title_vi' => 'Thiên đường Nhiệt đới: Thái Lan',
                'slug' => 'tropical-paradise-thailand',
                'description_en' => 'Enjoy beaches and culture.',
                'description_vi' => 'Thưởng thức bãi biển và văn hóa.',
                'price' => '1200.00',
                'image' => 'placeholder-360x200.svg',
                'category_id' => 1,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'City Escape: New York Highlights',
                'title_vi' => 'Trốn khỏi thành phố: Điểm nổi bật New York',
                'slug' => 'city-escape-new-york',
                'description_en' => 'See the classic sights of NYC.',
                'description_vi' => 'Xem những địa danh nổi tiếng của NYC.',
                'price' => '1500.00',
                'image' => 'placeholder-360x200.svg',
                'category_id' => 7,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'Mediterranean Cruise',
                'title_vi' => 'Du thuyền Địa Trung Hải',
                'slug' => 'mediterranean-cruise',
                'description_en' => 'Relax on a luxury cruise.',
                'description_vi' => 'Thư giãn trên du thuyền sang trọng.',
                'price' => '2500.00',
                'image' => 'placeholder-360x200.svg',
                'category_id' => 3,
                'published' => 1,
                'created_at' => $now,
            ],
            [
                'title_en' => 'Island Hopper: Philippines',
                'title_vi' => 'Khám phá đảo: Philippines',
                'slug' => 'island-hopper-philippines',
                'description_en' => 'Island hopping and snorkeling adventures.',
                'description_vi' => 'Hành trình khám phá đảo và lặn.',
                'price' => '900.00',
                'image' => 'placeholder-360x200.svg',
                'category_id' => 1,
                'published' => 1,
                'created_at' => $now,
            ],
        ];

        $this->db->table('tours')->insertBatch($data);
    }
}
