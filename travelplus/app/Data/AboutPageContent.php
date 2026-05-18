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
                'journey_title' => 'Dấu ấn thời gian',
                'journey_desc' => 'Hành trình phát triển của Travel Plus được xây dựng từ sự tận tâm, tính ổn định trong vận hành và cam kết mang lại giá trị dài hạn cho khách hàng.',
                'timeline' => [
                    ['year' => '2008', 'title' => 'Giai đoạn khởi đầu và thành lập', 'body' => 'Travel Plus hình thành nền tảng hoạt động đầu tiên, xây dựng bộ máy vận hành và định hướng phát triển dịch vụ du lịch bài bản.'],
                    ['year' => '2011 – 2012', 'title' => 'Mở rộng chi nhánh', 'body' => 'Mạng lưới hoạt động được mở rộng để tiếp cận khách hàng ở nhiều khu vực hơn và tăng khả năng phục vụ trực tiếp.'],
                    ['year' => '2013 – 2015', 'title' => 'Phát triển và khẳng định', 'body' => 'Travel Plus củng cố năng lực vận hành, mở rộng nhóm sản phẩm và từng bước khẳng định vị trí trên thị trường.'],
                    ['year' => '2016 – 2017', 'title' => 'Mở rộng khu vực', 'body' => 'Tiếp tục hoàn thiện mạng lưới hiện diện và tăng khả năng hỗ trợ khách hàng tại các đầu mối quan trọng.'],
                    ['year' => '2018 – 2019', 'title' => 'Kỷ niệm 10 năm', 'body' => 'Đây là giai đoạn đánh dấu bước trưởng thành về quy mô, quy trình và chất lượng dịch vụ.'],
                    ['year' => '2020 – 2024', 'title' => 'Thích nghi và phục hồi', 'body' => 'Travel Plus điều chỉnh mô hình vận hành, tái cấu trúc nguồn lực và duy trì chất lượng phục vụ trong bối cảnh biến động.'],
                    ['year' => '2025 – nay', 'title' => 'Bứt phá và phát triển bền vững', 'body' => 'Tập trung chuyển đổi số, nâng cao trải nghiệm khách hàng và mở rộng các nhóm dịch vụ có giá trị cao hơn cho doanh nghiệp lẫn khách lẻ.'],
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
                'journey_title' => 'Milestones over time',
                'journey_desc' => 'Travel Plus has grown through operational discipline, client focus and a long-term commitment to service value.',
                'timeline' => [
                    ['year' => '2008', 'title' => 'Foundation stage', 'body' => 'Travel Plus established its initial operating base and built the first service direction for the business.'],
                    ['year' => '2011 – 2012', 'title' => 'Branch expansion', 'body' => 'The network expanded to reach more travelers and improve direct support across regions.'],
                    ['year' => '2013 – 2015', 'title' => 'Growth and positioning', 'body' => 'The company strengthened execution capability, broadened product scope and improved its market position.'],
                    ['year' => '2016 – 2017', 'title' => 'Regional expansion', 'body' => 'Travel Plus continued to improve geographic coverage and service capacity at key locations.'],
                    ['year' => '2018 – 2019', 'title' => '10-year milestone', 'body' => 'This period marked stronger scale, more stable processes and more mature service quality.'],
                    ['year' => '2020 – 2024', 'title' => 'Adaptation and recovery', 'body' => 'Travel Plus adjusted its operating model, restructured resources and maintained service continuity through disruption.'],
                    ['year' => '2025 – present', 'title' => 'Acceleration and sustainable growth', 'body' => 'The focus has shifted to digital transformation, stronger customer experience and higher-value services for both corporate and retail clients.'],
                ],
            ],
        ];

        return $content[$locale] ?? $content['vi'];
    }
}
