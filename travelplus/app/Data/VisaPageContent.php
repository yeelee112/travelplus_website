<?php

namespace App\Data;

final class VisaPageContent
{
    public static function get(string $locale = 'vi'): array
    {
        $content = [
            'vi' => [
                'hero_eyebrow' => 'Dịch vụ visa',
                'hero_title' => 'Tư vấn hồ sơ visa rõ quy trình, đúng mục đích và dễ triển khai.',
                'hero_desc' => 'Travel Plus hỗ trợ khách du lịch, công tác, thăm thân và đoàn doanh nghiệp chuẩn bị hồ sơ visa theo đúng nhu cầu thực tế, tránh thiếu giấy tờ và giảm thời gian xử lý phát sinh.',
                'hero_cta_primary' => 'Đặt lịch tư vấn',
                'hero_cta_secondary' => 'Liên hệ ngay',
                'metrics' => [
                    ['title' => 'Checklist rõ ràng', 'text' => 'Rà đúng giấy tờ theo loại visa và mục đích chuyến đi.'],
                    ['title' => 'Theo sát tiến độ', 'text' => 'Cập nhật các mốc quan trọng và phần cần bổ sung nếu có.'],
                    ['title' => 'Phù hợp nhiều nhu cầu', 'text' => 'Du lịch, công tác, thăm thân và hồ sơ cho đoàn doanh nghiệp.'],
                ],
                'intro_eyebrow' => 'Cách Travel Plus hỗ trợ',
                'intro_title' => 'Hồ sơ visa cần đúng ngay từ đầu, không nên sửa sai ở phút cuối.',
                'intro_p1' => 'Một bộ hồ sơ tốt không chỉ là đủ giấy tờ mà còn phải đúng logic chuyến đi, đúng mục đích xin visa và nhất quán về thông tin.',
                'intro_p2' => 'Travel Plus tập trung vào việc làm rõ checklist, rà tài liệu và hướng dẫn khách chuẩn bị theo từng trường hợp cụ thể thay vì dùng một mẫu chung cho tất cả.',
                'support_eyebrow' => 'Phạm vi hỗ trợ',
                'support_title' => 'Các hạng mục Travel Plus đang triển khai',
                'support_cards' => [
                    ['title' => 'Tư vấn hồ sơ visa', 'text' => 'Rà soát giấy tờ cá nhân, công việc, tài chính và mục đích chuyến đi.'],
                    ['title' => 'Hướng dẫn lịch hẹn', 'text' => 'Hỗ trợ quy trình đặt lịch, sinh trắc học và nộp hồ sơ.'],
                    ['title' => 'Hồ sơ công tác', 'text' => 'Phù hợp khách đi hội nghị, gặp đối tác hoặc đoàn doanh nghiệp.'],
                    ['title' => 'Theo dõi bổ sung', 'text' => 'Hướng dẫn xử lý khi cơ quan tiếp nhận yêu cầu thêm giấy tờ.'],
                ],
                'countries_eyebrow' => 'Điểm đến phổ biến',
                'countries_title' => 'Các nhóm điểm đến thường được khách hàng quan tâm',
                'countries_desc' => 'Danh sách dưới đây có tính định hướng, không thay thế yêu cầu chính thức từ cơ quan tiếp nhận hồ sơ.',
                'regions' => [
                    ['title' => 'Châu Âu', 'items' => ['Pháp', 'Đức', 'Ý', 'Thụy Sĩ', 'Hà Lan', 'Tây Ban Nha']],
                    ['title' => 'Châu Á', 'items' => ['Nhật Bản', 'Hàn Quốc', 'Trung Quốc', 'Singapore', 'Thái Lan', 'Malaysia']],
                    ['title' => 'Châu Mỹ', 'items' => ['Hoa Kỳ', 'Canada', 'Mexico', 'Brazil']],
                    ['title' => 'Châu Đại Dương', 'items' => ['Úc', 'New Zealand']],
                ],
                'process_eyebrow' => 'Quy trình làm việc',
                'process_title' => '4 bước hỗ trợ visa tại Travel Plus',
                'process' => [
                    ['title' => 'Tiếp nhận nhu cầu', 'text' => 'Làm rõ điểm đến, mục đích, thời gian dự kiến và tình trạng hồ sơ hiện có.'],
                    ['title' => 'Xây checklist', 'text' => 'Xác định phần giấy tờ cần chuẩn bị và thứ tự ưu tiên.'],
                    ['title' => 'Rà soát và hoàn thiện', 'text' => 'Kiểm tra độ đầy đủ, tính nhất quán và các điểm cần giải trình.'],
                    ['title' => 'Theo sát xử lý', 'text' => 'Cập nhật tiến độ và hỗ trợ khi phát sinh yêu cầu bổ sung.'],
                ],
                'faq_eyebrow' => 'Câu hỏi thường gặp',
                'faq_title' => 'Những câu hỏi khách thường hỏi trước khi làm visa',
                'faq_desc' => 'Một số câu hỏi phổ biến để hình dung rõ hơn cách Travel Plus hỗ trợ.',
                'faqs' => [
                    ['q' => 'Travel Plus có cam kết đậu visa không?', 'a' => 'Không đơn vị nào có thể cam kết đậu visa. Travel Plus hỗ trợ phần hồ sơ, cách chuẩn bị và theo sát tiến độ để tăng tính sẵn sàng của hồ sơ.'],
                    ['q' => 'Có hỗ trợ hồ sơ công tác hoặc hội nghị không?', 'a' => 'Có. Đây là nhóm hồ sơ Travel Plus xử lý thường xuyên, bao gồm thư mời, xác nhận công việc, lịch trình và giấy tờ liên quan.'],
                    ['q' => 'Nếu thiếu giấy tờ hoặc cần bổ sung thì sao?', 'a' => 'Đội ngũ sẽ hướng dẫn khách chuẩn bị đúng phần cần bổ sung và điều chỉnh hồ sơ theo yêu cầu thực tế.'],
                    ['q' => 'Nên chuẩn bị hồ sơ trước bao lâu?', 'a' => 'Tùy điểm đến và mùa cao điểm. Với lịch công tác hoặc tour cố định ngày, nên chuẩn bị sớm để có dư thời gian xử lý phát sinh.'],
                ],
                'cta_eyebrow' => 'Nhận tư vấn nhanh',
                'cta_title' => 'Cần tư vấn hồ sơ visa cho chuyến đi sắp tới?',
                'cta_text' => 'Gửi điểm đến, mục đích chuyến đi và thời gian dự kiến. Travel Plus sẽ tư vấn hướng chuẩn bị hồ sơ phù hợp.',
                'cta_button' => 'Gửi yêu cầu ngay',
            ],
            'en' => [
                'hero_eyebrow' => 'Visa support',
                'hero_title' => 'Visa support with clearer steps, stronger document readiness and practical guidance.',
                'hero_desc' => 'Travel Plus supports leisure, business, family-visit and corporate-group travelers in preparing visa files that match the actual travel purpose and reduce avoidable delays.',
                'hero_cta_primary' => 'Book a consultation',
                'hero_cta_secondary' => 'Contact us',
                'metrics' => [
                    ['title' => 'Clear checklist', 'text' => 'Prepare the right documents for the visa type and travel purpose.'],
                    ['title' => 'Progress follow-up', 'text' => 'Track key milestones and additional requests if they appear.'],
                    ['title' => 'Suitable for many needs', 'text' => 'Leisure, business, family visits and corporate delegations.'],
                ],
                'intro_eyebrow' => 'How Travel Plus supports',
                'intro_title' => 'Visa files need to be correct from the start, not patched at the last minute.',
                'intro_p1' => 'A good visa file is not only complete. It also needs to match the trip purpose and keep the information consistent across the application.',
                'intro_p2' => 'Travel Plus focuses on clarifying the checklist, reviewing the file and guiding each traveler based on the actual case instead of one generic template.',
                'support_eyebrow' => 'Support scope',
                'support_title' => 'What Travel Plus is handling now',
                'support_cards' => [
                    ['title' => 'Visa file consultation', 'text' => 'Review personal, work, financial and travel-purpose documents.'],
                    ['title' => 'Appointment guidance', 'text' => 'Support with booking, biometrics and submission flow.'],
                    ['title' => 'Business files', 'text' => 'Suitable for meetings, conferences and corporate groups.'],
                    ['title' => 'Additional-document support', 'text' => 'Guidance when the receiving authority asks for more documents.'],
                ],
                'countries_eyebrow' => 'Popular destinations',
                'countries_title' => 'Destination groups commonly requested by clients',
                'countries_desc' => 'This list is for orientation only and does not replace official requirements from the receiving authority.',
                'regions' => [
                    ['title' => 'Europe', 'items' => ['France', 'Germany', 'Italy', 'Switzerland', 'Netherlands', 'Spain']],
                    ['title' => 'Asia', 'items' => ['Japan', 'South Korea', 'China', 'Singapore', 'Thailand', 'Malaysia']],
                    ['title' => 'Americas', 'items' => ['United States', 'Canada', 'Mexico', 'Brazil']],
                    ['title' => 'Oceania', 'items' => ['Australia', 'New Zealand']],
                ],
                'process_eyebrow' => 'Workflow',
                'process_title' => '4 steps of visa support at Travel Plus',
                'process' => [
                    ['title' => 'Receive the request', 'text' => 'Clarify destination, purpose, timing and current document status.'],
                    ['title' => 'Build the checklist', 'text' => 'Identify what must be prepared and in which order.'],
                    ['title' => 'Review and complete', 'text' => 'Check completeness, consistency and weak points that may need explanation.'],
                    ['title' => 'Follow the process', 'text' => 'Track progress and support additional-document requests if needed.'],
                ],
                'faq_eyebrow' => 'Frequently asked questions',
                'faq_title' => 'Questions clients usually ask before starting a visa file',
                'faq_desc' => 'A few common questions to make the support scope easier to understand.',
                'faqs' => [
                    ['q' => 'Does Travel Plus guarantee visa approval?', 'a' => 'No agency can guarantee approval. Travel Plus supports preparation, file logic and process follow-up to improve readiness.'],
                    ['q' => 'Can Travel Plus support business or conference files?', 'a' => 'Yes. This is one of the regular file types we support, including invitation letters, employment confirmations, schedules and related paperwork.'],
                    ['q' => 'What if additional documents are requested?', 'a' => 'The team will guide you on exactly what needs to be added and how to adjust the file.'],
                    ['q' => 'How early should a visa file be prepared?', 'a' => 'That depends on the destination and travel season. For fixed-date trips or business travel, earlier preparation gives more room for unexpected requests.'],
                ],
                'cta_eyebrow' => 'Quick consultation',
                'cta_title' => 'Need visa advice for an upcoming trip?',
                'cta_text' => 'Send the destination, purpose and expected timing. Travel Plus will advise the right preparation direction.',
                'cta_button' => 'Send your request',
            ],
        ];

        return $content[$locale] ?? $content['vi'];
    }
}
