<?php

namespace App\Data;

final class AboutPageContent
{
    public static function get(string $locale = 'vi'): array
    {
        $content = [
            'vi' => [
                'welcome' => 'Chào mừng bạn đến với Travel Plus',
                'title' => 'Vì sao Travel Plus là lựa chọn đáng tin cậy?',
                'intro_1' => 'Travel Plus tập trung vào tư vấn hành trình rõ ràng, dịch vụ đồng bộ và trải nghiệm phù hợp với từng nhu cầu cụ thể.',
                'intro_2' => 'Chúng tôi xem du lịch không chỉ là di chuyển, mà là quá trình xây dựng một hành trình đáng nhớ, có tổ chức tốt và đủ linh hoạt để khách hàng an tâm trong suốt chuyến đi.',
                'founder' => 'Nguyễn Bá Thanh Tùng',
                'position' => 'Giám đốc Travel Plus',
                'service_title' => 'Travel Plus mang đến dịch vụ phù hợp hơn cho từng hành trình',
                'services' => [
                    ['title' => 'Tận tâm', 'text' => 'Đội ngũ giàu kinh nghiệm luôn theo sát nhu cầu và hỗ trợ khách trong suốt hành trình.'],
                    ['title' => 'Ưu đãi phù hợp', 'text' => 'Đề xuất giải pháp tour, vé máy bay và dịch vụ đi kèm theo đúng ngân sách thực tế.'],
                    ['title' => 'Tối ưu chi phí', 'text' => 'Giảm các chi phí phát sinh không cần thiết và giữ phương án triển khai rõ ràng.'],
                ],
                'cta' => 'Khám phá ưu đãi ngay',
                'journey_title' => 'Hành trình phát triển Travel Plus',
                'journey_desc' => 'Từ ngày thành lập năm 2008 đến nay, Travel Plus từng bước xây dựng năng lực lữ hành quốc tế, mở rộng mạng lưới chi nhánh và thích nghi với những thay đổi lớn của thị trường du lịch.',
                'timeline' => [
                    ['year' => '2008', 'date' => '2008', 'title' => 'Thành lập và hoàn thiện nền tảng pháp lý', 'events' => [
                        ['date' => '01/2008', 'text' => 'Thành lập công ty với mã số doanh nghiệp 0305475784 theo giấy phép đăng ký kinh doanh do Sở Kế hoạch và Đầu tư cấp.'],
                        ['date' => '02/2008', 'text' => 'Khai trương công ty tại 90-92 Lê Thị Riêng, Phường Bến Nghé, Quận 1, TP Hồ Chí Minh.'],
                        ['date' => '05/2008', 'text' => 'Đăng ký sở hữu trí tuệ cho thương hiệu TRAVEL PLUS gồm nghĩa từ và logo.'],
                        ['date' => '07/2008', 'text' => 'Tổng cục Du lịch cấp giấy phép lữ hành quốc tế chính thức cho công ty.'],
                    ]],
                    ['year' => '2011 – 2012', 'date' => '2011 – 2012', 'title' => 'Mở rộng mạng lưới chi nhánh', 'events' => [
                        ['date' => '06/2011', 'text' => 'Thành lập Chi nhánh Hà Nội tại 47 Lê Văn Hưu, Phường Phạm Đình Hổ, Quận Hai Bà Trưng.'],
                        ['date' => '02/2012', 'text' => 'Chuyển văn phòng TP Hồ Chí Minh về Tòa nhà WVC 102 A-B-C Cống Quỳnh, Phường Phạm Ngũ Lão, Quận 1.'],
                        ['date' => '04/2012', 'text' => 'Thành lập Chi nhánh Huế tại 78 Bến Nghé, Phường Phú Hội, TP Huế.'],
                    ]],
                    ['year' => '2013 – 2014', 'date' => '2013 – 2014', 'title' => 'Củng cố vận hành sau 5 năm', 'events' => [
                        ['date' => '01/2013', 'text' => 'Kỷ niệm 5 năm thành lập công ty tại TP Hồ Chí Minh.'],
                        ['date' => '12/2014', 'text' => 'Chuyển văn phòng TP Hồ Chí Minh về 3/30A Thích Quảng Đức, Phường 3, Quận Phú Nhuận.'],
                        ['date' => '12/2014', 'text' => 'Giấy phép kinh doanh lữ hành quốc tế được Tổng cục Du lịch cấp lần 2.'],
                    ]],
                    ['year' => '2016 – 2017', 'date' => '2016 – 2017', 'title' => 'Phát triển tại Cần Thơ', 'events' => [
                        ['date' => '05/2016', 'text' => 'Thành lập Chi nhánh Cần Thơ tại 14-16B Hòa Bình, Phường An Cư, Quận Ninh Kiều.'],
                        ['date' => '04/2017', 'text' => 'Chi nhánh Cần Thơ chuyển về 83C Quang Trung, Phường Xuân Khánh, Quận Ninh Kiều.'],
                    ]],
                    ['year' => '2018 – 2019', 'date' => '2018 – 2019', 'title' => 'Dấu mốc 10 năm và điều chỉnh mạng lưới', 'events' => [
                        ['date' => '01/2018', 'text' => 'Kỷ niệm 10 năm thành lập công ty tại TP Hồ Chí Minh.'],
                        ['date' => '06/2018', 'text' => 'Chi nhánh Huế chuyển về 05A Nguyễn Tri Phương, Phường Phú Hội, TP Huế.'],
                        ['date' => '06/2019', 'text' => 'Chi nhánh Cần Thơ chuyển về Lầu 5 Tòa nhà STS, 11B Hòa Bình, Phường Tân An, Quận Ninh Kiều.'],
                    ]],
                    ['year' => '2020 – 2021', 'date' => '2020 – 2021', 'title' => 'Thích nghi trong giai đoạn Covid-19', 'events' => [
                        ['date' => '03/2020', 'text' => 'Chi nhánh Cần Thơ ngừng hoạt động do ảnh hưởng của dịch Covid-19.'],
                        ['date' => '2021', 'text' => 'Chi nhánh Huế tạm ngừng hoạt động trong bối cảnh ngành du lịch chịu nhiều biến động.'],
                    ]],
                    ['year' => '2022', 'date' => '2022', 'title' => 'Tái khởi động và kỷ niệm 15 năm', 'events' => [
                        ['date' => '01/2022', 'text' => 'Thành lập Chi nhánh Đà Nẵng tại 24 An Cư 5, Phường An Hải Bắc, Quận Sơn Trà.'],
                        ['date' => '12/2022', 'text' => 'Kỷ niệm 15 năm thành lập công ty tại Phú Quốc.'],
                    ]],
                    ['year' => '2024 – nay', 'date' => '2024 – nay', 'title' => 'Mở lại Huế và tập trung chuyển đổi số', 'events' => [
                        ['date' => '04/2024', 'text' => 'Mở lại Chi nhánh Huế tại 9/130 Đặng Thái Thân, Phường Thuận Hòa, Huế.'],
                        ['date' => '2024 – nay', 'text' => 'Tập trung chuyển đổi số, nâng cao trải nghiệm khách hàng và mở rộng các nhóm dịch vụ có giá trị cao hơn cho doanh nghiệp lẫn khách lẻ.'],
                    ]],
                ],
            ],
            'en' => [
                'welcome' => 'Welcome to Travel Plus',
                'title' => 'Why is Travel Plus a trusted choice?',
                'intro_1' => 'Travel Plus focuses on clear trip planning, consistent service delivery and travel experiences matched to each client\'s actual needs.',
                'intro_2' => 'We see travel as more than moving from one place to another. It is a well-organized journey that should feel memorable, practical and reliable from start to finish.',
                'founder' => 'Nguyen Ba Thanh Tung',
                'position' => 'Managing Director of Travel Plus',
                'service_title' => 'Travel Plus delivers services that fit each journey better',
                'services' => [
                    ['title' => 'Dedicated support', 'text' => 'An experienced team follows the brief closely and supports travelers throughout the trip.'],
                    ['title' => 'Relevant offers', 'text' => 'Tours, flights and add-on services are proposed based on the actual budget and need.'],
                    ['title' => 'Cost efficiency', 'text' => 'We reduce unnecessary extra costs and keep the operating plan clear.'],
                ],
                'cta' => 'Explore current offers',
                'journey_title' => 'Travel Plus development journey',
                'journey_desc' => 'Since its establishment in 2008, Travel Plus has built international travel capabilities, expanded its branch network and adapted to major changes in Vietnam’s travel market.',
                'timeline' => [
                    ['year' => '2008', 'date' => '2008', 'title' => 'Establishment and legal foundation', 'events' => [
                        ['date' => '01/2008', 'text' => 'Travel Plus was established with business registration number 0305475784 under a business license issued by the Department of Planning and Investment.'],
                        ['date' => '02/2008', 'text' => 'The company opened at 90-92 Le Thi Rieng, Ben Nghe Ward, District 1, Ho Chi Minh City.'],
                        ['date' => '05/2008', 'text' => 'Travel Plus registered intellectual property protection for the TRAVEL PLUS brand name and logo.'],
                        ['date' => '07/2008', 'text' => 'The Vietnam National Administration of Tourism officially issued the international tour operator license.'],
                    ]],
                    ['year' => '2011 – 2012', 'date' => '2011 – 2012', 'title' => 'Branch network expansion', 'events' => [
                        ['date' => '06/2011', 'text' => 'Travel Plus established its Hanoi Branch at 47 Le Van Huu, Pham Dinh Ho Ward, Hai Ba Trung District.'],
                        ['date' => '02/2012', 'text' => 'The Ho Chi Minh City office moved to WVC Building, 102 A-B-C Cong Quynh, Pham Ngu Lao Ward, District 1.'],
                        ['date' => '04/2012', 'text' => 'The Hue Branch was established at 78 Ben Nghe, Phu Hoi Ward, Hue City.'],
                    ]],
                    ['year' => '2013 – 2014', 'date' => '2013 – 2014', 'title' => 'Consolidation after five years', 'events' => [
                        ['date' => '01/2013', 'text' => 'Travel Plus celebrated its 5th anniversary in Ho Chi Minh City.'],
                        ['date' => '12/2014', 'text' => 'The Ho Chi Minh City office moved to 3/30A Thich Quang Duc, Ward 3, Phu Nhuan District.'],
                        ['date' => '12/2014', 'text' => 'The international travel business license was issued for the second time by the Vietnam National Administration of Tourism.'],
                    ]],
                    ['year' => '2016 – 2017', 'date' => '2016 – 2017', 'title' => 'Development in Can Tho', 'events' => [
                        ['date' => '05/2016', 'text' => 'Travel Plus established its Can Tho Branch at 14-16B Hoa Binh, An Cu Ward, Ninh Kieu District.'],
                        ['date' => '04/2017', 'text' => 'The Can Tho Branch moved to 83C Quang Trung, Xuan Khanh Ward, Ninh Kieu District.'],
                    ]],
                    ['year' => '2018 – 2019', 'date' => '2018 – 2019', 'title' => '10-year milestone and network adjustment', 'events' => [
                        ['date' => '01/2018', 'text' => 'Travel Plus celebrated its 10th anniversary in Ho Chi Minh City.'],
                        ['date' => '06/2018', 'text' => 'The Hue Branch moved to 05A Nguyen Tri Phuong, Phu Hoi Ward, Hue City.'],
                        ['date' => '06/2019', 'text' => 'The Can Tho Branch moved to the 5th floor of STS Building, 11B Hoa Binh, Tan An Ward, Ninh Kieu District.'],
                    ]],
                    ['year' => '2020 – 2021', 'date' => '2020 – 2021', 'title' => 'Adapting through Covid-19', 'events' => [
                        ['date' => '03/2020', 'text' => 'The Can Tho Branch suspended operations due to the impact of Covid-19.'],
                        ['date' => '2021', 'text' => 'The Hue Branch paused operations as the travel industry faced significant disruption.'],
                    ]],
                    ['year' => '2022', 'date' => '2022', 'title' => 'Restart and 15-year anniversary', 'events' => [
                        ['date' => '01/2022', 'text' => 'Travel Plus established its Da Nang Branch at 24 An Cu 5, An Hai Bac Ward, Son Tra District.'],
                        ['date' => '12/2022', 'text' => 'The company celebrated its 15th anniversary in Phu Quoc.'],
                    ]],
                    ['year' => '2024 – present', 'date' => '2024 – present', 'title' => 'Hue reopening and digital transformation', 'events' => [
                        ['date' => '04/2024', 'text' => 'Travel Plus reopened the Hue Branch at 9/130 Dang Thai Than, Thuan Hoa Ward, Hue.'],
                        ['date' => '2024 – present', 'text' => 'The company has focused on digital transformation, improved customer experience and higher-value service groups for both corporate clients and retail travelers.'],
                    ]],
                ],
            ],
        ];

        return $content[$locale] ?? $content['vi'];
    }
}
