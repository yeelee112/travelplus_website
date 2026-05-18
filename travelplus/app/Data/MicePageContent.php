<?php

namespace App\Data;

final class MicePageContent
{
    public static function get(string $locale = 'vi'): array
    {
        $content = [
            'vi' => [
                'hero_eyebrow' => 'MICE cho doanh nghiệp',
                'hero_title' => 'Thiết kế chương trình MICE có mục tiêu, có trải nghiệm và có hiệu quả vận hành.',
                'hero_desc' => 'Travel Plus triển khai trọn gói hội nghị, hội thảo, incentive, team building và sự kiện doanh nghiệp trong nước lẫn quốc tế. Mỗi chương trình được xây theo mục tiêu thực tế thay vì một mẫu tour cố định.',
                'hero_cta_primary' => 'Liên hệ tư vấn',
                'hero_cta_secondary' => 'Nhận đề xuất chương trình',
                'metrics' => [
                    ['title' => 'Trọn gói', 'text' => 'Từ ý tưởng, báo giá, vận hành onsite đến tổng kết sau chương trình.'],
                    ['title' => 'Linh hoạt quy mô', 'text' => 'Phù hợp đoàn nhỏ, incentive, hội nghị nội bộ hoặc sự kiện khách mời lớn.'],
                    ['title' => 'Một đầu mối phụ trách', 'text' => 'Doanh nghiệp làm việc trực tiếp với một team vận hành xuyên suốt.'],
                ],
                'intro_eyebrow' => 'Giải pháp MICE',
                'intro_title' => 'MICE không chỉ là chuyến đi, mà là công cụ để doanh nghiệp đạt mục tiêu.',
                'intro_p1' => 'Một chương trình MICE hiệu quả cần cân bằng giữa nội dung chuyên môn, trải nghiệm của người tham dự, ngân sách và khả năng vận hành thực tế.',
                'intro_p2' => 'Travel Plus đề xuất concept, lịch trình, format hoạt động và mức đầu tư dựa trên mục tiêu thật của từng doanh nghiệp.',
                'services_eyebrow' => 'Dịch vụ cốt lõi',
                'services_title' => 'Các nhóm chương trình Travel Plus đang triển khai',
                'services_desc' => 'Thiết kế theo ngành hàng, quy mô đoàn, thời điểm tổ chức và mục tiêu kinh doanh.',
                'service_cards' => [
                    ['title' => 'Hội nghị và hội thảo', 'text' => 'Meeting, conference, workshop, kick-off và đào tạo nội bộ với flow rõ ràng.', 'bullets' => ['Venue phù hợp quy mô', 'Âm thanh, ánh sáng, sân khấu', 'Điều phối check-in và hậu cần']],
                    ['title' => 'Incentive và tri ân', 'text' => 'Chương trình du lịch khen thưởng cho đội ngũ bán hàng, đối tác hoặc khách hàng chiến lược.', 'bullets' => ['Thiết kế trải nghiệm có điểm nhấn', 'Kết hợp nghỉ dưỡng, tham quan, gala', 'Kiểm soát nhận diện thương hiệu']],
                    ['title' => 'Team building', 'text' => 'Chương trình gắn kết đội ngũ phù hợp văn hóa doanh nghiệp và đặc điểm nhân sự.', 'bullets' => ['Concept theo mục tiêu', 'Indoor, outdoor hoặc hybrid', 'Kịch bản MC và tổng kết']],
                    ['title' => 'Sự kiện doanh nghiệp', 'text' => 'Gala dinner, lễ kỷ niệm, ra mắt sản phẩm hoặc activation thương hiệu.', 'bullets' => ['Ý tưởng chủ đề', 'Kịch bản sân khấu', 'Điều phối nhà cung cấp và onsite']],
                ],
                'solution_eyebrow' => 'Giải pháp theo điểm đến',
                'solution_title' => 'MICE trong nước và quốc tế đều có kịch bản vận hành riêng.',
                'solution_text' => 'Với chương trình trong nước, ưu tiên thường là chi phí, tốc độ triển khai và trải nghiệm gắn kết. Với chương trình quốc tế, trọng tâm chuyển sang lịch trình, visa, hãng bay, điều phối đoàn và quản trị rủi ro.',
                'solution_links' => ['domestic' => 'Xem tour trong nước', 'outbound' => 'Xem tour nước ngoài'],
                'solution_items' => [
                    ['title' => 'MICE nội địa', 'text' => 'Phù hợp hội nghị khách hàng, team building, retreat quản lý và incentive ngắn ngày.'],
                    ['title' => 'MICE outbound', 'text' => 'Dành cho đoàn khen thưởng, đối tác, đại lý cấp cao hoặc chương trình kết hợp networking.'],
                    ['title' => 'Hội nghị kết hợp trải nghiệm', 'text' => 'Kết hợp lịch trình chuyên môn với city tour, gala dinner hoặc hoạt động gắn kết.'],
                    ['title' => 'Hỗ trợ theo từng hạng mục', 'text' => 'Doanh nghiệp đã có venue hoặc khung chương trình vẫn có thể thuê Travel Plus vận hành từng phần.'],
                ],
                'why_eyebrow' => 'Vì sao chọn Travel Plus',
                'why_title' => 'Ưu tiên hiệu quả vận hành thay vì chỉ làm đẹp proposal.',
                'why_items' => [
                    ['title' => 'Hiểu brief kinh doanh', 'text' => 'Làm rõ mục tiêu trước khi đề xuất điểm đến, format hoạt động và mức đầu tư.'],
                    ['title' => 'Minh bạch ngân sách', 'text' => 'Báo giá chia theo nhóm chi phí để doanh nghiệp dễ đối chiếu và ra quyết định.'],
                    ['title' => 'Vận hành trọn gói', 'text' => 'Một đầu mối điều phối xuyên suốt từ khảo sát, booking đến ngày chạy chương trình.'],
                    ['title' => 'Linh hoạt theo quy mô', 'text' => 'Phù hợp đoàn nhỏ, incentive, hội nghị khách hàng hoặc sự kiện nội bộ lớn.'],
                    ['title' => 'Kiểm soát trải nghiệm', 'text' => 'Theo sát timeline, nhịp hoạt động và chất lượng dịch vụ trong suốt chương trình.'],
                    ['title' => 'Hỗ trợ sau chương trình', 'text' => 'Bàn giao hình ảnh, tổng kết chi phí và đề xuất cải tiến cho lần tổ chức sau.'],
                ],
                'process_eyebrow' => 'Quy trình làm việc',
                'process_title' => '5 bước triển khai một chương trình MICE',
                'process' => [
                    ['title' => 'Tiếp nhận brief', 'text' => 'Làm rõ mục tiêu, số lượng khách, thời gian dự kiến, ngân sách và yêu cầu bắt buộc.'],
                    ['title' => 'Đề xuất concept', 'text' => 'Xây dựng điểm đến, format, timeline, lưu trú, vận chuyển và chi phí sơ bộ.'],
                    ['title' => 'Chốt phương án', 'text' => 'Khóa lịch trình, danh mục dịch vụ, checklist nhân sự và phương án dự phòng.'],
                    ['title' => 'Triển khai onsite', 'text' => 'Điều phối thực địa, xử lý phát sinh và kiểm soát tiến độ chương trình.'],
                    ['title' => 'Tổng kết và bàn giao', 'text' => 'Đối soát chi phí, bàn giao hình ảnh và rút kinh nghiệm cho các mùa tiếp theo.'],
                ],
                'brief_eyebrow' => 'Nhận tư vấn nhanh',
                'brief_title' => 'Gửi brief MICE để Travel Plus đề xuất chương trình phù hợp.',
                'brief_text' => 'Doanh nghiệp chỉ cần cung cấp số lượng khách, thời gian dự kiến, mục tiêu chương trình và mức ngân sách mong muốn.',
                'brief_submit' => 'Gửi yêu cầu ngay',
            ],
            'en' => [
                'hero_eyebrow' => 'MICE for businesses',
                'hero_title' => 'MICE programs designed around objectives, experiences and execution quality.',
                'hero_desc' => 'Travel Plus delivers end-to-end meetings, conferences, incentive travel, team building and corporate events across Vietnam and overseas. Each program is built around a real business objective.',
                'hero_cta_primary' => 'Request consultation',
                'hero_cta_secondary' => 'Get a proposal',
                'metrics' => [
                    ['title' => 'End-to-end delivery', 'text' => 'From concept and quotation to onsite execution and wrap-up.'],
                    ['title' => 'Flexible scale', 'text' => 'Suitable for small groups, incentives, internal conferences or large hosted events.'],
                    ['title' => 'Single point of contact', 'text' => 'Your team works with one operating team throughout the project.'],
                ],
                'intro_eyebrow' => 'MICE solution',
                'intro_title' => 'MICE is not just a trip. It is a business tool.',
                'intro_p1' => 'An effective MICE program needs to balance business content, attendee experience, budget and operational feasibility.',
                'intro_p2' => 'Travel Plus shapes the concept, itinerary, activity format and budget around the actual business need instead of pushing a fixed package.',
                'services_eyebrow' => 'Core services',
                'services_title' => 'Program formats Travel Plus is delivering',
                'services_desc' => 'Built around industry context, group size, timing and business objectives.',
                'service_cards' => [
                    ['title' => 'Meetings and conferences', 'text' => 'Meeting, conference, workshop, kick-off and training programs with a clear delivery flow.', 'bullets' => ['Venue suited to scale', 'AV, lighting and stage', 'Check-in and logistics coordination']],
                    ['title' => 'Incentive and appreciation', 'text' => 'Reward travel for sales teams, partners or strategic clients.', 'bullets' => ['Experience-led itinerary design', 'Integrated leisure and gala', 'Consistent brand presentation']],
                    ['title' => 'Team building', 'text' => 'Programs designed around company culture and participant profile.', 'bullets' => ['Goal-based concept', 'Indoor, outdoor or hybrid', 'MC flow and wrap-up']],
                    ['title' => 'Corporate events', 'text' => 'Gala dinners, anniversaries, launches or hosted brand experiences.', 'bullets' => ['Theme and event identity', 'Stage flow', 'Vendor and onsite coordination']],
                ],
                'solution_eyebrow' => 'Destination approach',
                'solution_title' => 'Domestic and outbound MICE require different operating logic.',
                'solution_text' => 'Domestic programs usually prioritize cost, speed and team engagement. Outbound programs place more weight on routing, visas, airlines, delegation flow and risk control.',
                'solution_links' => ['domestic' => 'View domestic tours', 'outbound' => 'View outbound tours'],
                'solution_items' => [
                    ['title' => 'Domestic MICE', 'text' => 'Ideal for client conferences, team building, leadership retreats and short incentive programs.'],
                    ['title' => 'Outbound MICE', 'text' => 'For incentive groups, partners, senior distributors or hosted networking programs.'],
                    ['title' => 'Conference plus experience', 'text' => 'Combines business sessions with city experiences, gala dinner or engagement activities.'],
                    ['title' => 'Partial scope support', 'text' => 'If your team already has a venue or baseline plan, Travel Plus can still operate selected scopes only.'],
                ],
                'why_eyebrow' => 'Why Travel Plus',
                'why_title' => 'We prioritize execution quality, not just good-looking proposals.',
                'why_items' => [
                    ['title' => 'Business-aware brief handling', 'text' => 'We clarify objectives before recommending destinations, formats or investment levels.'],
                    ['title' => 'Transparent budgeting', 'text' => 'Quotations are structured by cost groups for easier internal review.'],
                    ['title' => 'End-to-end operations', 'text' => 'One team coordinates the project from survey and booking to onsite execution.'],
                    ['title' => 'Flexible by scale', 'text' => 'Suitable for small groups, incentives, client conferences or large internal events.'],
                    ['title' => 'Experience control', 'text' => 'We track timing, service quality and attendee flow throughout the program.'],
                    ['title' => 'Post-event support', 'text' => 'Photo handover, budget wrap-up and improvement suggestions for future runs.'],
                ],
                'process_eyebrow' => 'Workflow',
                'process_title' => '5 steps to deliver a MICE program',
                'process' => [
                    ['title' => 'Receive the brief', 'text' => 'Clarify objectives, guest count, timing, budget and non-negotiable requirements.'],
                    ['title' => 'Propose the concept', 'text' => 'Build destination, format, timeline, stay plan, transport and indicative cost.'],
                    ['title' => 'Confirm the solution', 'text' => 'Lock the itinerary, service scope, staffing checklist and backup plan.'],
                    ['title' => 'Execute onsite', 'text' => 'Coordinate the field team, handle issues and maintain delivery flow.'],
                    ['title' => 'Wrap up and hand over', 'text' => 'Close costs, hand over visuals and document improvements for future runs.'],
                ],
                'brief_eyebrow' => 'Quick consultation',
                'brief_title' => 'Send your MICE brief and let Travel Plus propose the right direction.',
                'brief_text' => 'Share guest count, expected timing, program objective and target budget. Travel Plus will advise a practical implementation direction.',
                'brief_submit' => 'Send your request',
            ],
        ];

        return $content[$locale] ?? $content['vi'];
    }
}
