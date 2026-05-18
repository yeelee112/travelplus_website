<?php

namespace App\Data;

class ServicePageCatalog
{
    public static function getAll(): array
    {
        return [
            'airline_tickets' => [
                'paths' => [
                    'vi' => 'dich-vu-ve-may-bay',
                    'en' => 'airline-ticket-service',
                ],
                'nav_label' => [
                    'vi' => 'Vé máy bay',
                    'en' => 'Airline Tickets',
                ],
                'meta_title' => [
                    'vi' => 'Dịch vụ vé máy bay | Travel Plus',
                    'en' => 'Airline Ticket Service | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Travel Plus hỗ trợ đặt vé máy bay lẻ, đoàn, công tác doanh nghiệp và xử lý thay đổi hành trình khi cần.',
                    'en' => 'Travel Plus supports airline tickets for individual, group and corporate travel with routing and schedule-change support.',
                ],
                'hero' => [
                    'eyebrow' => ['vi' => 'Dịch vụ hàng không', 'en' => 'Airline service'],
                    'title' => [
                        'vi' => 'Đặt vé máy bay nhanh, đúng hành trình và có người theo sát khi lịch thay đổi.',
                        'en' => 'Book airline tickets with faster support, better routing and follow-up when schedules change.',
                    ],
                    'description' => [
                        'vi' => 'Travel Plus hỗ trợ khách lẻ, gia đình, đoàn công tác và chương trình doanh nghiệp với tư vấn giờ bay, chặng nối chuyến, hành lý và chi phí tổng thể.',
                        'en' => 'Travel Plus supports individual travelers, families, business trips and group programs with flight timing, baggage and routing advice.',
                    ],
                    'image' => 'assets/images/air-ticket.jpg',
                ],
                'metrics' => [
                    ['icon' => 'bi bi-clock-history', 'title' => ['vi' => 'Phản hồi nhanh', 'en' => 'Fast response'], 'text' => ['vi' => 'Báo giá, giữ chỗ và xác nhận phương án bay theo timeline rõ ràng.', 'en' => 'Fare, hold and routing support with a clear response timeline.']],
                    ['icon' => 'bi bi-people', 'title' => ['vi' => 'Xử lý đoàn', 'en' => 'Group booking'], 'text' => ['vi' => 'Phù hợp tour đoàn, công tác doanh nghiệp hoặc khách mời sự kiện.', 'en' => 'Suitable for tour groups, corporate trips and invited guests.']],
                    ['icon' => 'bi bi-arrow-repeat', 'title' => ['vi' => 'Hỗ trợ thay đổi', 'en' => 'Change support'], 'text' => ['vi' => 'Theo sát đổi giờ bay, đổi tên, hoàn vé hoặc các thay đổi sát ngày.', 'en' => 'Supports date, schedule and ticket change requests when possible.']],
                ],
                'intro' => [
                    'title' => ['vi' => 'Không chỉ xuất vé, mà quản lý cả hành trình bay.', 'en' => 'More than ticketing, it is route management.'],
                    'body' => [
                        'vi' => 'Một vé máy bay phù hợp không chỉ nằm ở giá thấp nhất. Thời gian nối chuyến, hành lý, giờ đến và khả năng xử lý phát sinh mới là phần ảnh hưởng trực tiếp đến trải nghiệm cả chuyến đi.',
                        'en' => 'The best option is not always the cheapest fare. Timing, baggage, transit and flexibility matter just as much as the ticket price.',
                    ],
                    'points' => [
                        ['vi' => 'Đặt vé nội địa và quốc tế', 'en' => 'Domestic and international tickets'],
                        ['vi' => 'Giữ chỗ và báo giá cho đoàn', 'en' => 'Group fare and seat hold support'],
                        ['vi' => 'Hỗ trợ hồ sơ công tác và hóa đơn', 'en' => 'Invoice and travel paperwork support'],
                    ],
                ],
                'capabilities_title' => ['vi' => 'Những hạng mục đang triển khai', 'en' => 'Core ticketing support'],
                'capabilities' => [
                    ['icon' => 'bi bi-airplane', 'title' => ['vi' => 'Vé lẻ và gia đình', 'en' => 'Individual bookings'], 'text' => ['vi' => 'Tư vấn giờ bay, hành lý và phương án tối ưu tổng chi phí.', 'en' => 'Schedule, baggage and routing support for individual travelers.'], 'bullets' => [['vi' => 'Nội địa và quốc tế', 'en' => 'Domestic and international'], ['vi' => 'Tối ưu giờ đi và giờ đến', 'en' => 'Better departure and arrival timing']]],
                    ['icon' => 'bi bi-building', 'title' => ['vi' => 'Vé công tác doanh nghiệp', 'en' => 'Corporate trips'], 'text' => ['vi' => 'Phù hợp công tác, hội nghị, khách mời và lịch trình nhiều chặng.', 'en' => 'Designed for meetings, corporate trips and multi-leg itineraries.'], 'bullets' => [['vi' => 'Xuất hóa đơn', 'en' => 'Invoice support'], ['vi' => 'Theo sát thay đổi lịch', 'en' => 'Schedule change handling']]],
                    ['icon' => 'bi bi-diagram-3', 'title' => ['vi' => 'Đoàn và series booking', 'en' => 'Group travel'], 'text' => ['vi' => 'Nhận brief đoàn, xử lý số lượng lớn và sắp xếp khung giờ phù hợp.', 'en' => 'Handles group travel needs and aligned schedules for larger teams.'], 'bullets' => [['vi' => 'Điều phối ghế theo đoàn', 'en' => 'Group seat coordination'], ['vi' => 'Hỗ trợ danh sách khách', 'en' => 'Passenger list coordination']]],
                    ['icon' => 'bi bi-headset', 'title' => ['vi' => 'Hỗ trợ phát sinh', 'en' => 'After-booking support'], 'text' => ['vi' => 'Theo sát đổi vé, hoàn chuyến hoặc xử lý chậm chuyến khi cần.', 'en' => 'Supports change, delay and route issues after booking.'], 'bullets' => [['vi' => 'Đổi và hoàn vé theo điều kiện hãng', 'en' => 'Change and refund guidance'], ['vi' => 'Hỗ trợ trong quá trình di chuyển', 'en' => 'Support while traveling']]],
                ],
                'use_cases_title' => ['vi' => 'Phù hợp cho các nhu cầu nào', 'en' => 'Typical use cases'],
                'use_cases' => [
                    ['vi' => 'Chuyến công tác cần tối ưu thời gian thay vì chỉ chọn giá rẻ.', 'en' => 'Business trips that prioritize timing, not just fare.'],
                    ['vi' => 'Đoàn khách, incentive hoặc hội nghị cần cùng hành trình bay.', 'en' => 'Conference, incentive and group travelers needing aligned schedules.'],
                    ['vi' => 'Khách cần phản hồi nhanh khi lịch thay đổi sát ngày.', 'en' => 'Travelers needing quick support when schedules change.'],
                ],
                'why_title' => ['vi' => 'Vì sao khách chọn Travel Plus', 'en' => 'Why clients choose Travel Plus'],
                'why' => [
                    ['vi' => 'Theo sát cả hành trình thay vì chỉ giao mã đặt chỗ.', 'en' => 'We follow the trip, not just issue the ticket.'],
                    ['vi' => 'Hiểu bài toán nối chuyến, hành lý và thời gian di chuyển.', 'en' => 'We optimize timing, baggage and transit decisions.'],
                    ['vi' => 'Phù hợp cả nhu cầu lẻ lẫn booking số lượng lớn.', 'en' => 'Suitable for both individual and group travel.'],
                ],
                'process' => [
                    ['vi' => 'Nhận yêu cầu hành trình và thời gian đi', 'en' => 'Receive routing and travel timing'],
                    ['vi' => 'Đề xuất phương án bay và chi phí', 'en' => 'Propose fare and routing options'],
                    ['vi' => 'Chốt booking và xuất vé', 'en' => 'Confirm and issue tickets'],
                    ['vi' => 'Hỗ trợ thay đổi nếu phát sinh', 'en' => 'Support changes if needed'],
                ],
                'cta' => [
                    'title' => ['vi' => 'Cần báo giá vé máy bay cho cá nhân hoặc đoàn?', 'en' => 'Need an airline ticket quote?'],
                    'text' => ['vi' => 'Gửi hành trình dự kiến, số lượng khách và thời gian đi. Travel Plus sẽ đề xuất phương án phù hợp với mục tiêu chuyến đi.', 'en' => 'Send the route, passenger count and preferred dates. We will propose suitable options.'],
                ],
            ],
            'transport' => [
                'paths' => [
                    'vi' => 'dich-vu-van-chuyen',
                    'en' => 'transport-service',
                ],
                'nav_label' => [
                    'vi' => 'Vận chuyển',
                    'en' => 'Transport',
                ],
                'meta_title' => [
                    'vi' => 'Dịch vụ vận chuyển | Travel Plus',
                    'en' => 'Transport Service | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Travel Plus cung cấp vận chuyển sân bay, xe riêng, xe đoàn và điều phối di chuyển cho tour, sự kiện, hội nghị.',
                    'en' => 'Travel Plus provides airport transfer, private car, group transport and event mobility support.',
                ],
                'hero' => [
                    'eyebrow' => ['vi' => 'Điều phối di chuyển', 'en' => 'Ground transport'],
                    'title' => [
                        'vi' => 'Giải pháp vận chuyển đúng giờ, đúng tải và phù hợp với từng hành trình.',
                        'en' => 'Ground transport planned around timing, capacity and actual program needs.',
                    ],
                    'description' => [
                        'vi' => 'Travel Plus hỗ trợ đón tiễn sân bay, xe riêng, xe đoàn, shuttle cho sự kiện và điều phối vận chuyển theo lịch trình tour hoặc công tác doanh nghiệp.',
                        'en' => 'Travel Plus provides airport transfer, private car, group transport and event shuttle coordination with a focus on timing and execution.',
                    ],
                    'image' => 'assets/images/transport.jpg',
                ],
                'metrics' => [
                    ['icon' => 'bi bi-sign-turn-right', 'title' => ['vi' => 'Điều phối tuyến', 'en' => 'Route coordination'], 'text' => ['vi' => 'Tính theo điểm đón, điểm trả và lịch trình thực tế.', 'en' => 'Planned around pickup points, drop-offs and real schedules.']],
                    ['icon' => 'bi bi-bus-front', 'title' => ['vi' => 'Đủ loại phương tiện', 'en' => 'Flexible vehicle types'], 'text' => ['vi' => 'Phù hợp nhóm nhỏ, gia đình, đoàn doanh nghiệp và sự kiện.', 'en' => 'Fits small groups, families, corporate teams and events.']],
                    ['icon' => 'bi bi-shield-check', 'title' => ['vi' => 'Có phương án dự phòng', 'en' => 'Backup planning'], 'text' => ['vi' => 'Luôn tính trước các tình huống trễ chuyến hoặc đổi lịch.', 'en' => 'Built with backup plans for delays and schedule changes.']],
                ],
                'intro' => [
                    'title' => ['vi' => 'Vận chuyển tốt là phần không được phép trục trặc.', 'en' => 'Transport is the part that cannot fail.'],
                    'body' => [
                        'vi' => 'Một chương trình có thể mất nhịp chỉ vì xe đến trễ, thiếu chỗ hoặc điều phối không khớp lịch. Travel Plus làm rõ timeline, đầu mối liên hệ và phương án dự phòng trước khi triển khai.',
                        'en' => 'A program can fail because of late pick-up, wrong capacity or poor on-site coordination. We plan these variables early.',
                    ],
                    'points' => [
                        ['vi' => 'Đón tiễn sân bay', 'en' => 'Airport transfer'],
                        ['vi' => 'Xe riêng và xe đoàn', 'en' => 'Private and group transport'],
                        ['vi' => 'Shuttle cho hội nghị và sự kiện', 'en' => 'Shuttle for meetings and events'],
                    ],
                ],
                'capabilities_title' => ['vi' => 'Các hạng mục vận chuyển', 'en' => 'Transport scope'],
                'capabilities' => [
                    ['icon' => 'bi bi-airplane-engines', 'title' => ['vi' => 'Đón tiễn sân bay', 'en' => 'Airport transfer'], 'text' => ['vi' => 'Đón khách đúng giờ, đúng nhà ga và theo sát thay đổi giờ đáp hoặc cất cánh.', 'en' => 'Pickup and drop-off tied to actual arrival and departure timing.'], 'bullets' => [['vi' => 'Nội địa và quốc tế', 'en' => 'Domestic and international airports'], ['vi' => 'Theo dõi giờ bay', 'en' => 'Flight timing follow-up']]],
                    ['icon' => 'bi bi-car-front', 'title' => ['vi' => 'Xe riêng theo hành trình', 'en' => 'Private transport'], 'text' => ['vi' => 'Phù hợp khách gia đình, khách VIP hoặc lịch công tác cần linh hoạt.', 'en' => 'For families, VIP travelers and flexible business schedules.'], 'bullets' => [['vi' => 'Linh hoạt tuyến điểm', 'en' => 'Flexible routing'], ['vi' => 'Theo khung giờ riêng', 'en' => 'Custom time windows']]],
                    ['icon' => 'bi bi-bus-front', 'title' => ['vi' => 'Xe đoàn và shuttle', 'en' => 'Group and shuttle'], 'text' => ['vi' => 'Phục vụ đoàn tour, hội nghị, team building hoặc chương trình nhiều điểm đón.', 'en' => 'Supports conferences, team programs and multiple pickup points.'], 'bullets' => [['vi' => 'Điều phối nhiều xe', 'en' => 'Multi-vehicle coordination'], ['vi' => 'Tối ưu tải và lịch', 'en' => 'Capacity and timing planning']]],
                    ['icon' => 'bi bi-map', 'title' => ['vi' => 'Vận hành theo chương trình', 'en' => 'Program-based dispatch'], 'text' => ['vi' => 'Bám theo agenda thực tế, có đầu mối onsite và phương án đổi tuyến khi cần.', 'en' => 'Aligned with the actual agenda and managed on-site.'], 'bullets' => [['vi' => 'Có đầu mối điều phối', 'en' => 'Dedicated coordinator'], ['vi' => 'Có phương án dự phòng', 'en' => 'Backup route planning']]],
                ],
                'use_cases_title' => ['vi' => 'Nhu cầu thường gặp', 'en' => 'Common needs'],
                'use_cases' => [
                    ['vi' => 'Đưa đón sân bay cho khách lẻ, gia đình hoặc khách VIP.', 'en' => 'Airport transfer for individual, family or VIP travelers.'],
                    ['vi' => 'Vận chuyển đoàn hội nghị, sales incentive hoặc team building.', 'en' => 'Transport for conferences, incentive groups or team building events.'],
                    ['vi' => 'Điều phối xe cho lịch trình nhiều điểm đón trả trong ngày.', 'en' => 'Mobility planning for multiple pickup and drop-off points.'],
                ],
                'why_title' => ['vi' => 'Điểm Travel Plus tập trung', 'en' => 'Execution priorities'],
                'why' => [
                    ['vi' => 'Ưu tiên đúng giờ và đúng tải trước mọi thứ khác.', 'en' => 'Timing and capacity come first.'],
                    ['vi' => 'Rõ đầu mối liên hệ trước, trong và sau chương trình.', 'en' => 'Clear coordination contacts before, during and after the program.'],
                    ['vi' => 'Giảm rủi ro phát sinh nhờ kịch bản dự phòng cụ thể.', 'en' => 'Operational risk is reduced through backup planning.'],
                ],
                'process' => [
                    ['vi' => 'Nhận thông tin số lượng khách và tuyến điểm', 'en' => 'Receive passenger count and route details'],
                    ['vi' => 'Đề xuất phương tiện và timeline', 'en' => 'Propose vehicles and timing'],
                    ['vi' => 'Chốt điều phối và danh sách liên hệ', 'en' => 'Confirm dispatch and contact points'],
                    ['vi' => 'Theo sát onsite và xử lý phát sinh', 'en' => 'Support onsite and handle issues'],
                ],
                'cta' => [
                    'title' => ['vi' => 'Cần phương án vận chuyển cho tour, đoàn hoặc sự kiện?', 'en' => 'Need a transport plan for your group or event?'],
                    'text' => ['vi' => 'Gửi số lượng khách, thời gian, tuyến điểm và loại chương trình. Travel Plus sẽ đề xuất phương án phù hợp để dễ triển khai.', 'en' => 'Send the passenger count, timeline and route. We will propose a practical transport setup.'],
                ],
            ],
            'translation' => [
                'paths' => [
                    'vi' => 'dich-vu-dich-thuat',
                    'en' => 'translation-service',
                ],
                'nav_label' => [
                    'vi' => 'Dịch thuật',
                    'en' => 'Translation',
                ],
                'meta_title' => [
                    'vi' => 'Dịch vụ dịch thuật | Travel Plus',
                    'en' => 'Translation Service | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Travel Plus hỗ trợ dịch thuật hồ sơ visa, tài liệu công tác, booking và các nội dung song ngữ phục vụ vận hành du lịch.',
                    'en' => 'Travel Plus provides translation support for visa files, business documents and travel operation content.',
                ],
                'hero' => [
                    'eyebrow' => ['vi' => 'Hồ sơ và tài liệu', 'en' => 'Documents and files'],
                    'title' => [
                        'vi' => 'Dịch đúng ngữ cảnh để hồ sơ, tài liệu và nội dung vận hành sử dụng được ngay.',
                        'en' => 'Translation support built for actual use, not just literal wording.',
                    ],
                    'description' => [
                        'vi' => 'Travel Plus hỗ trợ dịch hồ sơ visa, tài liệu công tác, xác nhận booking, thư mời và nội dung song ngữ liên quan đến vận hành du lịch hoặc sự kiện.',
                        'en' => 'Travel Plus supports visa files, work documents, booking confirmations, invitation letters and bilingual materials for travel or events.',
                    ],
                    'image' => 'assets/images/translation.jpg',
                ],
                'metrics' => [
                    ['icon' => 'bi bi-file-earmark-text', 'title' => ['vi' => 'Theo loại hồ sơ', 'en' => 'File-based support'], 'text' => ['vi' => 'Không dịch đại trà, mà căn theo mục đích nộp hồ sơ hoặc sử dụng.', 'en' => 'Handled according to the intended submission or business use.']],
                    ['icon' => 'bi bi-translate', 'title' => ['vi' => 'Giữ thống nhất thuật ngữ', 'en' => 'Consistent terminology'], 'text' => ['vi' => 'Đặc biệt quan trọng với hồ sơ visa, doanh nghiệp và biểu mẫu.', 'en' => 'Especially important for visa and business documentation.']],
                    ['icon' => 'bi bi-check2-square', 'title' => ['vi' => 'Dễ kiểm tra và bàn giao', 'en' => 'Easy to review'], 'text' => ['vi' => 'Bố cục rõ, nội dung dễ đối chiếu với bản gốc.', 'en' => 'Clear output that is easy to compare with the source file.']],
                ],
                'intro' => [
                    'title' => ['vi' => 'Dịch đúng ngữ cảnh quan trọng hơn dịch từng chữ.', 'en' => 'Context matters more than literal wording.'],
                    'body' => [
                        'vi' => 'Tài liệu dùng cho visa, công tác hoặc đối tác cần sự rõ ràng, thống nhất và đúng mục đích sử dụng. Travel Plus tập trung vào bản dịch có thể dùng ngay cho xử lý hồ sơ hoặc trao đổi công việc.',
                        'en' => 'Travel and business documents often need clarity and consistency more than literal translation. We focus on usable output.',
                    ],
                    'points' => [
                        ['vi' => 'Hồ sơ visa và du lịch', 'en' => 'Visa and travel files'],
                        ['vi' => 'Biểu mẫu, xác nhận, thư mời', 'en' => 'Forms, confirmations and invitation letters'],
                        ['vi' => 'Tài liệu giới thiệu doanh nghiệp', 'en' => 'Company profiles and supporting documents'],
                    ],
                ],
                'capabilities_title' => ['vi' => 'Các nhóm tài liệu hỗ trợ', 'en' => 'Supported document types'],
                'capabilities' => [
                    ['icon' => 'bi bi-passport', 'title' => ['vi' => 'Hồ sơ visa', 'en' => 'Visa-related files'], 'text' => ['vi' => 'Hỗ trợ tài liệu đi kèm hồ sơ visa, xác nhận công việc, tài chính hoặc lịch trình.', 'en' => 'Support for visa files, work confirmations, finance and itinerary documents.'], 'bullets' => [['vi' => 'Theo checklist hồ sơ', 'en' => 'Checklist-based support'], ['vi' => 'Giữ thống nhất thuật ngữ', 'en' => 'Consistent terminology']]],
                    ['icon' => 'bi bi-file-earmark-richtext', 'title' => ['vi' => 'Tài liệu du lịch và booking', 'en' => 'Travel and booking documents'], 'text' => ['vi' => 'Thông tin hành trình, xác nhận dịch vụ và nội dung làm việc với khách hàng.', 'en' => 'Travel information, service confirmations and client-facing support material.'], 'bullets' => [['vi' => 'Rõ thông tin sử dụng', 'en' => 'Clear operational wording'], ['vi' => 'Dễ đối chiếu bản gốc', 'en' => 'Easy source comparison']]],
                    ['icon' => 'bi bi-briefcase', 'title' => ['vi' => 'Tài liệu doanh nghiệp', 'en' => 'Business materials'], 'text' => ['vi' => 'Phục vụ công tác, hội nghị, làm việc với đối tác hoặc chương trình MICE.', 'en' => 'Useful for corporate travel, meetings and MICE support.'], 'bullets' => [['vi' => 'Tài liệu giới thiệu', 'en' => 'Company support docs'], ['vi' => 'Biểu mẫu và thư từ', 'en' => 'Forms and correspondence']]],
                    ['icon' => 'bi bi-chat-square-text', 'title' => ['vi' => 'Nội dung song ngữ', 'en' => 'Bilingual content'], 'text' => ['vi' => 'Phù hợp nội dung ngắn phục vụ vận hành, hướng dẫn khách hoặc thông báo sự kiện.', 'en' => 'Bilingual content for travel operations and event communication.'], 'bullets' => [['vi' => 'Gọn, dễ đọc', 'en' => 'Concise and readable'], ['vi' => 'Phù hợp tình huống sử dụng', 'en' => 'Tailored to the use case']]],
                ],
                'use_cases_title' => ['vi' => 'Tình huống thường dùng', 'en' => 'Typical use cases'],
                'use_cases' => [
                    ['vi' => 'Chuẩn bị hồ sơ visa hoặc bộ giấy tờ nộp cơ quan tiếp nhận.', 'en' => 'Preparing visa files or submission documents.'],
                    ['vi' => 'Dịch nội dung xác nhận cho đối tác, khách mời hoặc nhân sự đi công tác.', 'en' => 'Translating confirmations for partners or business travelers.'],
                    ['vi' => 'Làm bộ nội dung song ngữ phục vụ tour, sự kiện hoặc MICE.', 'en' => 'Creating bilingual materials for tours, events or MICE programs.'],
                ],
                'why_title' => ['vi' => 'Điểm làm việc ưu tiên', 'en' => 'Service priorities'],
                'why' => [
                    ['vi' => 'Dịch theo mục đích sử dụng thực tế.', 'en' => 'Translated for the actual use case.'],
                    ['vi' => 'Giữ nhất quán tên riêng, chức danh và thuật ngữ.', 'en' => 'Keeps names, titles and terms consistent.'],
                    ['vi' => 'Bản giao dễ rà soát và tiếp tục xử lý hồ sơ.', 'en' => 'Easy to review and continue processing.'],
                ],
                'process' => [
                    ['vi' => 'Nhận tài liệu và mục đích sử dụng', 'en' => 'Receive files and intended use'],
                    ['vi' => 'Đánh giá khối lượng và thời gian', 'en' => 'Review scope and timeline'],
                    ['vi' => 'Thực hiện và rà soát nội dung', 'en' => 'Translate and review'],
                    ['vi' => 'Bàn giao bản hoàn chỉnh', 'en' => 'Deliver final files'],
                ],
                'cta' => [
                    'title' => ['vi' => 'Cần hỗ trợ dịch thuật cho hồ sơ hoặc tài liệu đi công tác?', 'en' => 'Need translation support for documents?'],
                    'text' => ['vi' => 'Gửi loại tài liệu, ngôn ngữ cần xử lý và deadline. Travel Plus sẽ phản hồi cách triển khai phù hợp.', 'en' => 'Send the document type, required language and deadline. We will advise the right approach.'],
                ],
            ],
            'hotels' => [
                'paths' => [
                    'vi' => 'dich-vu-khach-san',
                    'en' => 'hotel-service',
                ],
                'nav_label' => [
                    'vi' => 'Khách sạn',
                    'en' => 'Hotels',
                ],
                'meta_title' => [
                    'vi' => 'Dịch vụ khách sạn | Travel Plus',
                    'en' => 'Hotel Service | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Travel Plus hỗ trợ đặt khách sạn cho du lịch tự túc, công tác doanh nghiệp, hội nghị và đoàn MICE.',
                    'en' => 'Travel Plus supports hotel booking for leisure, business trips, conferences and group rooming needs.',
                ],
                'hero' => [
                    'eyebrow' => ['vi' => 'Lưu trú theo nhu cầu', 'en' => 'Stay planning'],
                    'title' => [
                        'vi' => 'Đặt khách sạn đúng vị trí, đúng tiêu chuẩn và phù hợp với mục đích chuyến đi.',
                        'en' => 'Hotel booking planned around location, standard and trip purpose.',
                    ],
                    'description' => [
                        'vi' => 'Travel Plus hỗ trợ khách lẻ, gia đình, công tác doanh nghiệp và đoàn MICE với ưu tiên rõ ràng về vị trí, thời gian di chuyển và tiêu chuẩn dịch vụ.',
                        'en' => 'Travel Plus supports leisure, business, conference and group stays with location and operational fit in mind.',
                    ],
                    'image' => 'assets/images/hotel.jpg',
                ],
                'metrics' => [
                    ['icon' => 'bi bi-geo-alt', 'title' => ['vi' => 'Ưu tiên vị trí', 'en' => 'Location first'], 'text' => ['vi' => 'Chọn khách sạn theo điểm làm việc, tham quan hoặc venue sự kiện.', 'en' => 'Hotels chosen around meetings, attractions or venues.']],
                    ['icon' => 'bi bi-door-open', 'title' => ['vi' => 'Theo tiêu chuẩn lưu trú', 'en' => 'Room standard fit'], 'text' => ['vi' => 'Phù hợp khách lẻ, gia đình, khách VIP hoặc đoàn doanh nghiệp.', 'en' => 'Suitable for individual guests, families, VIPs and corporate groups.']],
                    ['icon' => 'bi bi-grid-1x2', 'title' => ['vi' => 'Xử lý rooming list', 'en' => 'Rooming support'], 'text' => ['vi' => 'Hữu ích với đoàn lớn, hội nghị hoặc chương trình nhiều đợt check-in.', 'en' => 'Useful for larger groups and event rooming lists.']],
                ],
                'intro' => [
                    'title' => ['vi' => 'Khách sạn phù hợp giúp cả lịch trình vận hành nhẹ hơn.', 'en' => 'The right hotel makes the whole program easier.'],
                    'body' => [
                        'vi' => 'Một khách sạn giá tốt nhưng xa venue hoặc không hợp cấu trúc đoàn có thể làm tăng chi phí ở phần khác. Travel Plus tư vấn lưu trú theo bối cảnh sử dụng thực tế.',
                        'en' => 'A low rate is not enough if the property is far from the venue or unsuitable for the group setup. We optimize for the actual program.',
                    ],
                    'points' => [
                        ['vi' => 'Khách sạn du lịch và nghỉ dưỡng', 'en' => 'Leisure and resort stays'],
                        ['vi' => 'Lưu trú công tác và hội nghị', 'en' => 'Business and conference stays'],
                        ['vi' => 'Rooming list cho đoàn', 'en' => 'Group rooming list support'],
                    ],
                ],
                'capabilities_title' => ['vi' => 'Phạm vi hỗ trợ lưu trú', 'en' => 'Accommodation support'],
                'capabilities' => [
                    ['icon' => 'bi bi-house-door', 'title' => ['vi' => 'Đặt phòng khách lẻ', 'en' => 'Individual stays'], 'text' => ['vi' => 'Phù hợp gia đình, cặp đôi hoặc khách tự túc cần chỗ ở thuận tiện.', 'en' => 'For families, couples and individual travelers.'], 'bullets' => [['vi' => 'Theo khu vực mong muốn', 'en' => 'Based on preferred area'], ['vi' => 'Theo chuẩn dịch vụ', 'en' => 'Based on hotel standard']]],
                    ['icon' => 'bi bi-building-check', 'title' => ['vi' => 'Lưu trú công tác', 'en' => 'Business accommodation'], 'text' => ['vi' => 'Ưu tiên gần nơi làm việc, thuận tiện di chuyển và có dịch vụ phù hợp.', 'en' => 'Prioritizes location, convenience and business-friendly amenities.'], 'bullets' => [['vi' => 'Gần venue hoặc văn phòng', 'en' => 'Close to venue or office'], ['vi' => 'Thuận tiện check-in, check-out', 'en' => 'Convenient check-in/out']]],
                    ['icon' => 'bi bi-people-fill', 'title' => ['vi' => 'Đoàn hội nghị và MICE', 'en' => 'MICE and group stays'], 'text' => ['vi' => 'Xử lý rooming list, phân bổ phòng và phối hợp với timeline chương trình.', 'en' => 'Handles rooming lists and aligns with the event schedule.'], 'bullets' => [['vi' => 'Nhiều loại phòng', 'en' => 'Mixed room types'], ['vi' => 'Phối hợp cùng lịch chương trình', 'en' => 'Coordinated with agenda']]],
                    ['icon' => 'bi bi-stars', 'title' => ['vi' => 'Nâng hạng trải nghiệm', 'en' => 'Experience fit'], 'text' => ['vi' => 'Đề xuất khách sạn tạo trải nghiệm tốt hơn cho incentive, khách VIP hoặc khách mời quan trọng.', 'en' => 'Better-fit stays for incentive, VIP or hosted guests.'], 'bullets' => [['vi' => 'Theo mục tiêu chuyến đi', 'en' => 'Matched to trip purpose'], ['vi' => 'Kiểm soát trải nghiệm tổng thể', 'en' => 'Aligned with the overall experience']]],
                ],
                'use_cases_title' => ['vi' => 'Nhu cầu phù hợp', 'en' => 'Use cases'],
                'use_cases' => [
                    ['vi' => 'Đoàn công tác hoặc hội nghị cần ở gần venue.', 'en' => 'Corporate groups staying close to the venue.'],
                    ['vi' => 'Khách du lịch muốn ở khu vực tiện đi lại và đúng ngân sách.', 'en' => 'Leisure guests balancing budget and location.'],
                    ['vi' => 'Chương trình MICE cần quản lý rooming list và timeline check-in.', 'en' => 'MICE programs needing rooming and check-in coordination.'],
                ],
                'why_title' => ['vi' => 'Cách Travel Plus chọn phương án', 'en' => 'How we choose options'],
                'why' => [
                    ['vi' => 'Dựa trên vị trí và lịch trình thực tế.', 'en' => 'Based on actual location and program timing.'],
                    ['vi' => 'Cân đối ngân sách và trải nghiệm lưu trú.', 'en' => 'Balances budget and stay experience.'],
                    ['vi' => 'Hữu ích cho cả khách lẻ lẫn đoàn doanh nghiệp.', 'en' => 'Works for both individuals and corporate groups.'],
                ],
                'process' => [
                    ['vi' => 'Nhận yêu cầu về khu vực, tiêu chuẩn và số phòng', 'en' => 'Receive area, standard and room needs'],
                    ['vi' => 'Đề xuất khách sạn phù hợp', 'en' => 'Propose suitable options'],
                    ['vi' => 'Chốt phương án và rooming list', 'en' => 'Confirm option and rooming list'],
                    ['vi' => 'Theo sát check-in và phát sinh', 'en' => 'Support check-in and stay issues'],
                ],
                'cta' => [
                    'title' => ['vi' => 'Cần phương án khách sạn cho chuyến đi hoặc đoàn doanh nghiệp?', 'en' => 'Need hotel options for your trip or group?'],
                    'text' => ['vi' => 'Gửi khu vực mong muốn, số lượng phòng, thời gian lưu trú và mục tiêu chuyến đi. Travel Plus sẽ tư vấn phương án phù hợp.', 'en' => 'Send the area, room count, stay dates and trip purpose. We will propose suitable options.'],
                ],
            ],
        ];
    }
}
