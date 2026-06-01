<?php

namespace App\Data;

final class VisaPageContent
{
    public static function get(string $locale = 'vi'): array
    {
        $content = [
            'vi' => [
                'hero_eyebrow' => 'Dịch vụ visa Travel Plus',
                'hero_title' => 'Dịch vụ visa du lịch, công tác và thăm thân',
                'hero_desc' => 'Travel Plus tư vấn hồ sơ visa theo từng điểm đến, mục đích chuyến đi và tình trạng cá nhân. Đội ngũ hỗ trợ rà giấy tờ, xây checklist, hướng dẫn đặt lịch nộp hồ sơ và theo dõi bổ sung để khách chuẩn bị đúng ngay từ đầu.',
                'hero_cta_primary' => 'Tư vấn hồ sơ visa',
                'hero_cta_secondary' => 'Liên hệ ngay',
                'metrics' => [
                    ['title' => 'Rà hồ sơ theo từng nước', 'text' => 'Định hướng giấy tờ theo visa Mỹ, Canada, Úc, Schengen, Nhật Bản, Hàn Quốc và nhiều điểm đến khác.'],
                    ['title' => 'Checklist dễ chuẩn bị', 'text' => 'Phân nhóm giấy tờ cá nhân, công việc, tài chính, lịch trình và thư mời để khách dễ theo dõi.'],
                    ['title' => 'Theo sát lịch hẹn', 'text' => 'Hỗ trợ các mốc đặt lịch, sinh trắc học, nộp hồ sơ và bổ sung giấy tờ nếu phát sinh.'],
                ],
                'intro_eyebrow' => 'Tư vấn đúng hồ sơ',
                'intro_title' => 'Làm visa hiệu quả bắt đầu từ một bộ hồ sơ có logic.',
                'intro_p1' => 'Mỗi quốc gia và mỗi loại visa có cách đánh giá khác nhau. Hồ sơ xin visa du lịch, visa công tác, visa thăm thân hay visa du học ngắn hạn cần thể hiện rõ mục đích chuyến đi, khả năng tài chính, lịch trình và ràng buộc quay về.',
                'intro_p2' => 'Travel Plus không dùng một mẫu hồ sơ chung cho tất cả khách. Chúng tôi rà từng trường hợp, xác định điểm mạnh, điểm cần giải trình và hướng dẫn chuẩn bị tài liệu phù hợp trước khi nộp.',
                'support_eyebrow' => 'Phạm vi hỗ trợ',
                'support_title' => 'Dịch vụ visa Travel Plus đang hỗ trợ',
                'support_cards' => [
                    ['title' => 'Visa du lịch', 'text' => 'Tư vấn hồ sơ du lịch tự túc hoặc đi tour, bao gồm lịch trình, đặt chỗ, chứng minh công việc và tài chính.'],
                    ['title' => 'Visa công tác, hội nghị, MICE', 'text' => 'Hỗ trợ thư mời, xác nhận công việc, quyết định cử đi công tác và lịch trình làm việc cho cá nhân hoặc đoàn doanh nghiệp.'],
                    ['title' => 'Visa thăm thân, du học ngắn hạn', 'text' => 'Định hướng hồ sơ quan hệ, thư mời, mục đích lưu trú, kế hoạch học tập ngắn hạn hoặc chương trình trao đổi.'],
                    ['title' => 'Lịch hẹn và bổ sung hồ sơ', 'text' => 'Hướng dẫn đặt lịch, sinh trắc học, nộp hồ sơ, theo dõi tiến độ và xử lý yêu cầu bổ sung từ đơn vị tiếp nhận.'],
                ],
                'countries_eyebrow' => 'Điểm đến phổ biến',
                'countries_title' => 'Các nhóm visa khách hàng thường quan tâm',
                'countries_desc' => 'Danh sách dưới đây giúp khách định hướng nhanh. Yêu cầu chính thức, thời gian xử lý và phí lãnh sự có thể thay đổi theo từng quốc gia, từng thời điểm và từng hồ sơ.',
                'regions' => [
                    ['title' => 'Châu Âu', 'items' => ['Pháp', 'Đức', 'Ý', 'Thụy Sĩ', 'Hà Lan', 'Tây Ban Nha', 'Anh']],
                    ['title' => 'Châu Á', 'items' => ['Nhật Bản', 'Hàn Quốc', 'Trung Quốc', 'Đài Loan', 'Singapore', 'Thái Lan']],
                    ['title' => 'Châu Mỹ', 'items' => ['Hoa Kỳ', 'Canada', 'Mexico', 'Brazil']],
                    ['title' => 'Châu Đại Dương', 'items' => ['Úc', 'New Zealand']],
                ],
                'process_eyebrow' => 'Quy trình làm việc',
                'process_title' => 'Quy trình tư vấn và chuẩn bị hồ sơ visa',
                'process' => [
                    ['title' => 'Xác định nhu cầu', 'text' => 'Làm rõ quốc gia, loại visa, mục đích chuyến đi, thời gian dự kiến và tình trạng hộ chiếu/hồ sơ hiện có.'],
                    ['title' => 'Lập checklist hồ sơ', 'text' => 'Gửi danh sách giấy tờ cần chuẩn bị theo từng trường hợp, phân biệt giấy tờ bắt buộc và giấy tờ nên bổ sung.'],
                    ['title' => 'Rà soát trước khi nộp', 'text' => 'Kiểm tra tính nhất quán của thông tin, lịch trình, tài chính, thư mời và các nội dung cần giải trình.'],
                    ['title' => 'Theo dõi sau nộp', 'text' => 'Nhắc lịch hẹn, cập nhật tiến độ và hướng dẫn xử lý khi có yêu cầu bổ sung hoặc phỏng vấn.'],
                ],
                'faq_eyebrow' => 'Câu hỏi thường gặp',
                'faq_title' => 'FAQ về dịch vụ visa Travel Plus',
                'faq_desc' => 'Các câu hỏi phổ biến trước khi khách bắt đầu chuẩn bị hồ sơ xin visa.',
                'faqs' => [
                    ['q' => 'Travel Plus có hỗ trợ làm visa Mỹ, Canada, Úc và Schengen không?', 'a' => 'Có. Travel Plus hỗ trợ tư vấn và chuẩn bị hồ sơ cho nhiều điểm đến phổ biến như Hoa Kỳ, Canada, Úc, New Zealand, các nước Schengen, Nhật Bản, Hàn Quốc, Trung Quốc và một số quốc gia khác tùy nhu cầu thực tế.'],
                    ['q' => 'Làm visa mất bao lâu?', 'a' => 'Thời gian xử lý phụ thuộc vào quốc gia, loại visa, lịch hẹn, mùa cao điểm, độ đầy đủ của hồ sơ và việc có bị yêu cầu bổ sung hay không. Travel Plus sẽ tư vấn mốc chuẩn bị phù hợp sau khi biết điểm đến và ngày dự kiến khởi hành.'],
                    ['q' => 'Chi phí dịch vụ visa được tính như thế nào?', 'a' => 'Chi phí phụ thuộc vào loại visa, phí lãnh sự, phí trung tâm tiếp nhận nếu có, dịch thuật/công chứng và phạm vi hỗ trợ khách chọn. Travel Plus sẽ báo rõ các khoản trước khi khách xác nhận sử dụng dịch vụ.'],
                    ['q' => 'Travel Plus có cam kết đậu visa không?', 'a' => 'Không. Quyết định cấp visa thuộc thẩm quyền của cơ quan lãnh sự hoặc cơ quan quản lý xuất nhập cảnh. Travel Plus tập trung hỗ trợ hồ sơ đầy đủ, logic và đúng quy trình để tăng mức độ sẵn sàng của hồ sơ.'],
                    ['q' => 'Nếu thiếu giấy tờ thì có nộp được không?', 'a' => 'Tùy loại giấy tờ và yêu cầu của từng điểm đến. Đội ngũ sẽ chỉ ra phần còn thiếu, đề xuất giấy tờ thay thế nếu phù hợp và hướng dẫn bổ sung trước khi nộp để hạn chế rủi ro phát sinh.'],
                    ['q' => 'Travel Plus có hỗ trợ visa cho đoàn công ty không?', 'a' => 'Có. Với đoàn công tác, hội nghị, MICE hoặc incentive, Travel Plus có thể hỗ trợ checklist theo nhóm, rà thông tin từng thành viên và phối hợp lịch hẹn để hồ sơ được chuẩn bị đồng bộ hơn.'],
                ],
                'cta_eyebrow' => 'Nhận tư vấn nhanh',
                'cta_title' => 'Cần tư vấn hồ sơ visa cho chuyến đi sắp tới?',
                'cta_text' => 'Gửi quốc gia cần xin visa, mục đích chuyến đi, ngày dự kiến khởi hành và tình trạng hồ sơ hiện có. Travel Plus sẽ tư vấn checklist và hướng chuẩn bị phù hợp.',
                'cta_button' => 'Gửi yêu cầu tư vấn',
            ],
            'en' => [
                'hero_eyebrow' => 'Travel Plus visa service',
                'hero_title' => 'Visa services for travel, business and family visits',
                'hero_desc' => 'Travel Plus advises visa files based on destination, travel purpose and applicant profile. The team supports document review, checklist planning, appointment guidance and follow-up so travelers can prepare correctly from the start.',
                'hero_cta_primary' => 'Get visa advice',
                'hero_cta_secondary' => 'Contact us',
                'metrics' => [
                    ['title' => 'Destination-specific review', 'text' => 'Guidance for the United States, Canada, Australia, Schengen, Japan, South Korea and other popular destinations.'],
                    ['title' => 'Practical checklist', 'text' => 'Documents are grouped by personal, employment, financial, itinerary and invitation requirements.'],
                    ['title' => 'Appointment follow-up', 'text' => 'Support for appointment milestones, biometrics, submission and additional-document requests.'],
                ],
                'intro_eyebrow' => 'Case-based guidance',
                'intro_title' => 'A strong visa file starts with clear document logic.',
                'intro_p1' => 'Each destination and visa type is assessed differently. Leisure, business, family-visit and short-term study files need to show a clear travel purpose, financial readiness, itinerary and ties to return.',
                'intro_p2' => 'Travel Plus does not rely on one generic template. We review each case, identify strengths and weak points, and guide the documents that should be prepared before submission.',
                'support_eyebrow' => 'Support scope',
                'support_title' => 'Visa support handled by Travel Plus',
                'support_cards' => [
                    ['title' => 'Tourist visa', 'text' => 'Document guidance for independent trips or packaged tours, including itinerary, reservations, employment proof and financial documents.'],
                    ['title' => 'Business, conference and MICE visa', 'text' => 'Support for invitation letters, employment confirmation, assignment letters and business schedules for individuals or corporate groups.'],
                    ['title' => 'Family visit and short-term study visa', 'text' => 'Guidance on relationship documents, invitations, stay purpose, short-term study plans or exchange programs.'],
                    ['title' => 'Appointments and follow-up', 'text' => 'Guidance for appointments, biometrics, submission, progress tracking and additional-document requests from the receiving authority.'],
                ],
                'countries_eyebrow' => 'Popular destinations',
                'countries_title' => 'Visa groups clients often ask about',
                'countries_desc' => 'This list helps clients orient their request. Official requirements, processing time and consular fees may change by destination, season and individual file.',
                'regions' => [
                    ['title' => 'Europe', 'items' => ['France', 'Germany', 'Italy', 'Switzerland', 'Netherlands', 'Spain', 'United Kingdom']],
                    ['title' => 'Asia', 'items' => ['Japan', 'South Korea', 'China', 'Taiwan', 'Singapore', 'Thailand']],
                    ['title' => 'Americas', 'items' => ['United States', 'Canada', 'Mexico', 'Brazil']],
                    ['title' => 'Oceania', 'items' => ['Australia', 'New Zealand']],
                ],
                'process_eyebrow' => 'Workflow',
                'process_title' => 'Visa consultation and document preparation process',
                'process' => [
                    ['title' => 'Clarify the request', 'text' => 'Confirm destination, visa type, travel purpose, expected timing and current passport/document status.'],
                    ['title' => 'Build the checklist', 'text' => 'Send a case-based document list and separate required documents from recommended supporting documents.'],
                    ['title' => 'Review before submission', 'text' => 'Check consistency across information, itinerary, finances, invitation letters and explanations.'],
                    ['title' => 'Follow after submission', 'text' => 'Track appointment milestones, progress updates and additional-document or interview requests.'],
                ],
                'faq_eyebrow' => 'Frequently asked questions',
                'faq_title' => 'FAQ about Travel Plus visa services',
                'faq_desc' => 'Common questions before clients start preparing a visa application file.',
                'faqs' => [
                    ['q' => 'Can Travel Plus support U.S., Canada, Australia and Schengen visas?', 'a' => 'Yes. Travel Plus supports consultation and document preparation for popular destinations such as the United States, Canada, Australia, New Zealand, Schengen countries, Japan, South Korea, China and other destinations depending on the request.'],
                    ['q' => 'How long does visa processing take?', 'a' => 'Processing time depends on the destination, visa type, appointment availability, peak season, document completeness and whether additional documents are requested. Travel Plus will advise a suitable preparation timeline after reviewing the destination and expected departure date.'],
                    ['q' => 'How is the visa service cost calculated?', 'a' => 'Cost depends on visa type, consular fees, visa center fees if applicable, translation/notarization needs and the selected support scope. Travel Plus will clarify the cost items before the client confirms the service.'],
                    ['q' => 'Does Travel Plus guarantee visa approval?', 'a' => 'No. Visa approval is decided by the relevant consular or immigration authority. Travel Plus focuses on preparing a complete, logical and properly submitted file to improve readiness.'],
                    ['q' => 'Can I apply if some documents are missing?', 'a' => 'That depends on the document and destination requirements. The team will identify gaps, suggest suitable alternatives where possible and guide completion before submission to reduce avoidable risk.'],
                    ['q' => 'Can Travel Plus support company groups?', 'a' => 'Yes. For business, conference, MICE or incentive groups, Travel Plus can support group checklists, member information review and appointment coordination.'],
                ],
                'cta_eyebrow' => 'Quick consultation',
                'cta_title' => 'Need visa advice for an upcoming trip?',
                'cta_text' => 'Send the destination, travel purpose, expected departure date and current document status. Travel Plus will advise the checklist and preparation direction.',
                'cta_button' => 'Send consultation request',
            ],
        ];

        return $content[$locale] ?? $content['vi'];
    }
}
