<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MiniGameSeeder extends Seeder
{
    public function run()
    {
        if ($this->db->table('game_questions')->countAllResults() > 0) return;

        // 40 biển số theo địa giới 63 tỉnh/thành trước sáp nhập, đã xáo thứ tự.
        $rows = [
            ['75','Thừa Thiên Huế','Đại Nội\nChùa Thiên Mụ\nLăng Khải Định','Bún bò Huế','Phú Bài','Quần thể Di tích Cố đô Huế'],
            ['47','Đắk Lắk','Buôn Đôn\nHồ Lắk\nBảo tàng Thế giới Cà phê','Cà phê Buôn Ma Thuột','Buôn Ma Thuột',null],
            ['15–16','Hải Phòng','Vịnh Lan Hạ\nĐảo Cát Bà\nBãi biển Đồ Sơn','Bánh đa cua','Cát Bi','Vịnh Hạ Long – Quần đảo Cát Bà'],
            ['68','Kiên Giang','Phú Quốc\nHà Tiên\nQuần đảo Nam Du','Gỏi cá trích','Phú Quốc',null],
            ['29–33, 40','Hà Nội','Hồ Hoàn Kiếm\nVăn Miếu\nHoàng thành Thăng Long','Phở Hà Nội','Nội Bài','Khu trung tâm Hoàng thành Thăng Long'],
            ['79','Khánh Hòa','Vịnh Nha Trang\nTháp Bà Ponagar\nHòn Mun','Bún chả cá','Cam Ranh',null],
            ['11','Cao Bằng','Thác Bản Giốc\nKhu di tích Pác Bó\nĐộng Ngườm Ngao','Bánh cuốn Cao Bằng',null,'Công viên địa chất Non nước Cao Bằng'],
            ['43','Đà Nẵng','Bà Nà Hills\nNgũ Hành Sơn\nBán đảo Sơn Trà','Mì Quảng','Đà Nẵng',null],
            ['65','Cần Thơ','Bến Ninh Kiều\nChợ nổi Cái Răng\nNhà cổ Bình Thủy','Bánh cống','Cần Thơ',null],
            ['14','Quảng Ninh','Vịnh Hạ Long\nYên Tử\nĐảo Cô Tô','Chả mực Hạ Long','Vân Đồn','Vịnh Hạ Long'],
            ['49','Lâm Đồng','Hồ Xuân Hương\nThung lũng Tình Yêu\nNúi Langbiang','Bánh căn Đà Lạt','Liên Khương','Không gian văn hóa Cồng chiêng Tây Nguyên'],
            ['51, 59','TP. Hồ Chí Minh','Dinh Độc Lập\nChợ Bến Thành\nĐịa đạo Củ Chi','Cơm tấm Sài Gòn','Tân Sơn Nhất',null],
            ['37','Nghệ An','Làng Sen quê Bác\nBiển Cửa Lò\nVườn quốc gia Pù Mát','Cháo lươn Vinh','Vinh',null],
            ['92','Quảng Nam','Phố cổ Hội An\nThánh địa Mỹ Sơn\nCù Lao Chàm','Cao lầu Hội An','Chu Lai','Phố cổ Hội An'],
            ['78','Phú Yên','Gành Đá Đĩa\nBãi Xép\nTháp Nhạn','Mắt cá ngừ đại dương','Tuy Hòa',null],
            ['72','Bà Rịa – Vũng Tàu','Bãi Sau Vũng Tàu\nHồ Tràm\nTượng Chúa Kitô Vua','Bánh khọt Vũng Tàu','Côn Đảo',null],
            ['76','Quảng Ngãi','Đảo Lý Sơn\nBiển Mỹ Khê\nNúi Thiên Ấn','Don Quảng Ngãi','Chu Lai',null],
            ['74','Quảng Trị','Thành cổ Quảng Trị\nĐịa đạo Vịnh Mốc\nCầu Hiền Lương','Bún hến Mai Xá',null,null],
            ['73','Quảng Bình','Động Phong Nha\nHang Sơn Đoòng\nBiển Nhật Lệ','Cháo canh Quảng Bình','Đồng Hới','Vườn quốc gia Phong Nha – Kẻ Bàng'],
            ['36','Thanh Hóa','Thành Nhà Hồ\nBiển Sầm Sơn\nPù Luông','Nem chua Thanh Hóa','Thọ Xuân','Thành Nhà Hồ'],
            ['18','Nam Định','Đền Trần\nNhà thờ Phú Nhai\nBiển Thịnh Long','Phở bò Nam Định',null,null],
            ['35','Ninh Bình','Quần thể Tràng An\nTam Cốc – Bích Động\nChùa Bái Đính','Cơm cháy Ninh Bình',null,'Quần thể danh thắng Tràng An'],
            ['17','Thái Bình','Chùa Keo\nBiển Đồng Châu\nCồn Vành','Canh cá Quỳnh Côi',null,null],
            ['89','Hưng Yên','Phố Hiến\nChùa Chuông\nĐền Mẫu','Nhãn lồng Hưng Yên',null,null],
            ['98','Bắc Giang','Chùa Vĩnh Nghiêm\nTây Yên Tử\nHồ Cấm Sơn','Vải thiều Lục Ngạn',null,'Mộc bản chùa Vĩnh Nghiêm'],
            ['99','Bắc Ninh','Chùa Dâu\nĐền Đô\nLàng tranh Đông Hồ','Bánh phu thê',null,'Dân ca Quan họ Bắc Ninh'],
            ['20','Thái Nguyên','Hồ Núi Cốc\nATK Định Hóa\nBảo tàng Văn hóa các dân tộc Việt Nam','Chè Tân Cương',null,null],
            ['97','Bắc Kạn','Hồ Ba Bể\nĐộng Hua Mạ\nThác Đầu Đẳng','Miến dong Bắc Kạn',null,null],
            ['21','Yên Bái','Ruộng bậc thang Mù Cang Chải\nHồ Thác Bà\nSuối Giàng','Cốm Tú Lệ',null,null],
            ['24','Lào Cai','Sa Pa\nĐỉnh Fansipan\nĐèo Ô Quy Hồ','Thắng cố','Sa Pa',null],
            ['22','Tuyên Quang','Khu di tích Tân Trào\nNa Hang\nSuối khoáng Mỹ Lâm','Cam sành Hàm Yên',null,null],
            ['23','Hà Giang','Cột cờ Lũng Cú\nHẻm Tu Sản\nĐèo Mã Pí Lèng','Cháo ấu tẩu',null,'Công viên địa chất Cao nguyên đá Đồng Văn'],
            ['19','Phú Thọ','Đền Hùng\nĐồi chè Long Cốc\nVườn quốc gia Xuân Sơn','Thịt chua Thanh Sơn',null,'Tín ngưỡng thờ cúng Hùng Vương'],
            ['28','Hòa Bình','Hồ Hòa Bình\nMai Châu\nThung Nai','Cơm lam Hòa Bình',null,'Mo Mường Hòa Bình'],
            ['88','Vĩnh Phúc','Tam Đảo\nThiền viện Trúc Lâm Tây Thiên\nHồ Đại Lải','Su su Tam Đảo',null,null],
            ['67','An Giang','Miếu Bà Chúa Xứ Núi Sam\nRừng tràm Trà Sư\nNúi Cấm','Bún cá An Giang',null,null],
            ['94','Bạc Liêu','Nhà Công tử Bạc Liêu\nCánh đồng điện gió\nChùa Xiêm Cán','Bún bò cay Bạc Liêu',null,'Nghệ thuật Đờn ca tài tử Nam Bộ'],
            ['69','Cà Mau','Mũi Cà Mau\nRừng U Minh Hạ\nHòn Đá Bạc','Cua Cà Mau','Cà Mau',null],
            ['83','Sóc Trăng','Chùa Dơi\nChùa Chén Kiểu\nChợ nổi Ngã Năm','Bánh pía Sóc Trăng',null,null],
            ['95','Hậu Giang','Khu bảo tồn Lung Ngọc Hoàng\nChợ nổi Ngã Bảy\nCông viên giải trí Kittyd & Minnied','Khóm Cầu Đúc',null,null],
        ];

        foreach ($rows as $i => $r) {
            $this->db->table('game_questions')->insert([
                'plate_code' => $r[0], 'province' => $r[1], 'places' => $r[2],
                'specialty' => $r[3], 'airport' => $r[4], 'unesco' => $r[5],
                'sort_order' => $i + 1, 'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
