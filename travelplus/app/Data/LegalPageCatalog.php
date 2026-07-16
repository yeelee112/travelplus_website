<?php

namespace App\Data;

use App\Services\WebsiteSettingsService;

final class LegalPageCatalog
{
    /**
     * @return array<string, mixed>
     */
    public static function get(string $type, string $locale = 'vi'): array
    {
        $locale = $locale === 'en' ? 'en' : 'vi';
        $pages = self::pages();
        $page = $pages[$type] ?? null;

        if ($page === null) {
            return [];
        }

        $result = [
            'title' => self::localized($page['title'], $locale),
            'meta_title' => self::localized($page['meta_title'], $locale),
            'meta_desc' => self::localized($page['meta_desc'], $locale),
            'subtitle' => self::localized($page['subtitle'], $locale),
            'version' => $page['version'],
            'effective_date' => $page['effective_date'],
            'updated_at' => $page['updated_at'],
            'updated_label' => self::localized($page['updated_label'], $locale),
            'effective_label' => self::localized($page['effective_label'], $locale),
            'version_label' => self::localized($page['version_label'], $locale),
            'toc_label' => self::localized($page['toc_label'], $locale),
            'related_title' => self::localized($page['related_title'], $locale),
            'print_label' => self::localized($page['print_label'], $locale),
            'search_label' => self::localized($page['search_label'], $locale),
            'search_placeholder' => self::localized($page['search_placeholder'], $locale),
            'notice_title' => self::localized($page['notice_title'], $locale),
            'notice' => self::localized($page['notice'], $locale),
            'cta' => self::localized($page['cta'], $locale),
            'faq_title' => self::localized($page['faq_title'], $locale),
            'faqs' => self::localized($page['faqs'], $locale),
            'sections' => self::localized($page['sections'], $locale),
        ];

        $settings = new WebsiteSettingsService();

        return self::replaceContactDetails($result, [
            '+84 79 568 1 568' => $settings->phoneDisplay($locale),
            'info@travelplusvn.com' => $settings->get('email'),
        ]);
    }

    /**
     * @param mixed $value
     * @param array<string, string> $replacements
     * @return mixed
     */
    private static function replaceContactDetails($value, array $replacements)
    {
        if (is_string($value)) {
            return strtr($value, $replacements);
        }
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = self::replaceContactDetails($item, $replacements);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $value
     * @return mixed
     */
    private static function localized(array $value, string $locale)
    {
        return $value[$locale] ?? $value['vi'] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function pages(): array
    {
        return [
            'terms' => [
                'title' => [
                    'vi' => 'Điều khoản sử dụng',
                    'en' => 'Terms of Service',
                ],
                'meta_title' => [
                    'vi' => 'Điều khoản sử dụng dịch vụ Travel Plus | Tour, Visa, MICE',
                    'en' => 'Travel Plus Terms of Service | Tours, Visa, MICE',
                ],
                'meta_desc' => [
                    'vi' => 'Điều khoản sử dụng website và dịch vụ Travel Plus: tour du lịch, visa, vé máy bay, khách sạn, MICE, vận chuyển, thanh toán, hủy đổi và trách nhiệm các bên.',
                    'en' => 'Terms governing Travel Plus website and services, including tours, visa support, flights, hotels, MICE, transport, payment, changes, cancellations and responsibilities.',
                ],
                'subtitle' => [
                    'vi' => 'Áp dụng cho website travelplusvn.com và các dịch vụ do Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus cung cấp.',
                    'en' => 'Applies to travelplusvn.com and services provided by Uu The Du Lich One Member Co., Ltd. - Travel Plus.',
                ],
                'version' => '2.0',
                'effective_date' => '2026-04-10',
                'updated_at' => '2026-04-10',
                'updated_label' => [
                    'vi' => 'Cập nhật',
                    'en' => 'Updated',
                ],
                'effective_label' => [
                    'vi' => 'Hiệu lực từ',
                    'en' => 'Effective date',
                ],
                'version_label' => [
                    'vi' => 'Phiên bản',
                    'en' => 'Version',
                ],
                'toc_label' => [
                    'vi' => 'Mục lục',
                    'en' => 'Table of contents',
                ],
                'related_title' => [
                    'vi' => 'Chính sách liên quan',
                    'en' => 'Related policies',
                ],
                'print_label' => [
                    'vi' => 'In trang',
                    'en' => 'Print page',
                ],
                'search_label' => [
                    'vi' => 'Tìm trong điều khoản',
                    'en' => 'Search within terms',
                ],
                'search_placeholder' => [
                    'vi' => 'Nhập từ khóa như visa, hoàn tiền, MICE...',
                    'en' => 'Search keywords like visa, refund, MICE...',
                ],
                'notice_title' => [
                    'vi' => 'Lưu ý quan trọng',
                    'en' => 'Important note',
                ],
                'notice' => [
                    'vi' => 'Giá, lịch trình, điều kiện hủy đổi và thời hạn thanh toán có thể khác nhau theo từng dịch vụ. Điều kiện cuối cùng sẽ được Travel Plus xác nhận bằng văn bản trước khi khách hàng thanh toán hoặc sử dụng dịch vụ.',
                    'en' => 'Prices, itineraries, change or cancellation rules and payment deadlines may vary by service. Final conditions will be confirmed by Travel Plus in writing before payment or service use.',
                ],
                'cta' => [
                    'vi' => [
                        'eyebrow' => 'Cần kiểm tra điều khoản cho booking cụ thể?',
                        'title' => 'Gửi yêu cầu để Travel Plus tư vấn rõ điều kiện dịch vụ trước khi đặt.',
                        'description' => 'Đội ngũ tư vấn sẽ hỗ trợ kiểm tra lịch trình, thanh toán, điều kiện hủy đổi, visa hoặc yêu cầu MICE theo từng trường hợp.',
                        'primary_label' => 'Liên hệ tư vấn',
                        'secondary_label' => 'Xem chính sách bảo mật',
                    ],
                    'en' => [
                        'eyebrow' => 'Need to review terms for a specific booking?',
                        'title' => 'Send a request so Travel Plus can clarify service conditions before booking.',
                        'description' => 'Our team can help review itinerary, payment, change rules, visa requirements or MICE conditions case by case.',
                        'primary_label' => 'Contact Travel Plus',
                        'secondary_label' => 'View Privacy Statement',
                    ],
                ],
                'faq_title' => [
                    'vi' => 'Câu hỏi thường gặp về điều khoản',
                    'en' => 'Terms FAQ',
                ],
                'faqs' => [
                    'vi' => [
                        [
                            'question' => 'Điều khoản này áp dụng cho những dịch vụ nào?',
                            'answer' => 'Điều khoản áp dụng cho website Travel Plus và các dịch vụ như tour trong nước, tour nước ngoài, visa, vé máy bay, khách sạn, MICE, vận chuyển, dịch thuật và các dịch vụ liên quan.',
                        ],
                        [
                            'question' => 'Travel Plus có cam kết đậu visa không?',
                            'answer' => 'Travel Plus hỗ trợ tư vấn và chuẩn bị hồ sơ visa, nhưng quyết định cấp visa thuộc thẩm quyền của cơ quan lãnh sự hoặc cơ quan quản lý xuất nhập cảnh.',
                        ],
                        [
                            'question' => 'Khi nào booking được xem là xác nhận?',
                            'answer' => 'Booking được xem là xác nhận khi Travel Plus thông báo tình trạng chỗ, điều kiện dịch vụ và ghi nhận thanh toán hoặc đặt cọc theo yêu cầu của từng dịch vụ.',
                        ],
                        [
                            'question' => 'Travel Plus có quyền cập nhật điều khoản không?',
                            'answer' => 'Có. Travel Plus có thể cập nhật điều khoản để phù hợp với quy định pháp luật, chính sách nhà cung cấp hoặc thay đổi vận hành. Phiên bản mới có hiệu lực từ thời điểm được đăng trên website, trừ khi có thông báo khác.',
                        ],
                    ],
                    'en' => [
                        [
                            'question' => 'Which services do these Terms apply to?',
                            'answer' => 'These Terms apply to the Travel Plus website and services including domestic tours, outbound tours, visa support, flights, hotels, MICE, transportation, translation and related services.',
                        ],
                        [
                            'question' => 'Does Travel Plus guarantee visa approval?',
                            'answer' => 'Travel Plus supports visa consultation and document preparation, but approval decisions are made solely by consular or immigration authorities.',
                        ],
                        [
                            'question' => 'When is a booking considered confirmed?',
                            'answer' => 'A booking is considered confirmed when Travel Plus confirms availability, service conditions and records the required payment or deposit for the relevant service.',
                        ],
                        [
                            'question' => 'Can Travel Plus update these Terms?',
                            'answer' => 'Yes. Travel Plus may update these Terms to reflect legal requirements, supplier policies or operational changes. The latest version is effective when published unless otherwise stated.',
                        ],
                    ],
                ],
                'sections' => [
                    'vi' => [
                        [
                            'id' => 'pham-vi-ap-dung',
                            'heading' => 'Phạm vi áp dụng',
                            'paragraphs' => [
                                'Điều khoản này áp dụng cho toàn bộ việc truy cập website travelplusvn.com và việc sử dụng các dịch vụ do Travel Plus cung cấp.',
                                'Khi truy cập website, gửi form tư vấn, đặt dịch vụ hoặc thanh toán, khách hàng được xem là đã đọc, hiểu và đồng ý với Điều khoản sử dụng này.',
                            ],
                            'bullets' => [
                                'Tour du lịch trong nước, tour nước ngoài và tour thiết kế riêng.',
                                'Dịch vụ visa, vé máy bay, khách sạn, vận chuyển, dịch thuật.',
                                'Dịch vụ MICE, corporate travel, hội nghị, hội thảo, incentive, team building, gala dinner và các chương trình doanh nghiệp.',
                            ],
                        ],
                        [
                            'id' => 'su-dung-website',
                            'heading' => 'Điều kiện sử dụng website',
                            'bullets' => [
                                'Chỉ sử dụng website cho mục đích hợp pháp và phù hợp với quy định hiện hành.',
                                'Không can thiệp trái phép vào dữ liệu, mã nguồn, hệ thống bảo mật hoặc hạ tầng kỹ thuật của website.',
                                'Không sao chép, khai thác hoặc sử dụng nội dung trên website cho mục đích thương mại khi chưa có chấp thuận bằng văn bản từ Travel Plus.',
                                'Travel Plus có quyền hạn chế hoặc chấm dứt quyền truy cập nếu phát hiện hành vi vi phạm.',
                            ],
                        ],
                        [
                            'id' => 'thong-tin-khach-hang',
                            'heading' => 'Thông tin khách hàng và tài khoản',
                            'bullets' => [
                                'Khách hàng cần cung cấp thông tin chính xác, đầy đủ và cập nhật khi gửi yêu cầu tư vấn, đặt dịch vụ hoặc đăng ký tài khoản.',
                                'Khách hàng tự chịu trách nhiệm bảo mật tài khoản, mật khẩu và các hoạt động phát sinh từ tài khoản của mình.',
                                'Travel Plus có quyền tạm khóa hoặc từ chối xử lý yêu cầu nếu thông tin sai lệch, giả mạo hoặc có dấu hiệu gian lận.',
                            ],
                        ],
                        [
                            'id' => 'thong-tin-dich-vu',
                            'heading' => 'Thông tin dịch vụ trên website',
                            'paragraphs' => [
                                'Thông tin về lịch trình, giá, tình trạng chỗ, hình ảnh minh họa, điều kiện khuyến mãi và chính sách áp dụng có thể thay đổi theo thời điểm, theo chính sách nhà cung cấp hoặc theo tình hình vận hành thực tế.',
                                'Travel Plus nỗ lực cập nhật nội dung chính xác, nhưng thông tin cuối cùng sẽ được xác nhận lại trước khi booking được hoàn tất.',
                            ],
                        ],
                        [
                            'id' => 'dat-dich-vu-thanh-toan',
                            'heading' => 'Đặt dịch vụ, giá và thanh toán',
                            'bullets' => [
                                'Khách hàng gửi yêu cầu đặt chỗ hoặc yêu cầu tư vấn qua website, hotline, email, Zalo, Messenger hoặc kênh liên hệ chính thức của Travel Plus.',
                                'Travel Plus xác nhận tình trạng dịch vụ, tổng chi phí, điều kiện áp dụng, thời hạn thanh toán và hướng dẫn thanh toán.',
                                'Một số dịch vụ yêu cầu đặt cọc hoặc thanh toán toàn bộ trong thời hạn cụ thể.',
                                'Khách hàng chịu các chi phí phát sinh từ ngân hàng, cổng thanh toán hoặc chuyển đổi ngoại tệ nếu có.',
                            ],
                            'note' => 'Booking chỉ được xem là hoàn tất khi Travel Plus xác nhận bằng văn bản và/hoặc ghi nhận thanh toán theo điều kiện của từng dịch vụ.',
                        ],
                        [
                            'id' => 'huy-doi-hoan-tien',
                            'heading' => 'Thay đổi, hủy dịch vụ và hoàn tiền',
                            'paragraphs' => [
                                'Điều kiện thay đổi, hủy dịch vụ hoặc hoàn tiền phụ thuộc vào loại dịch vụ, thời điểm yêu cầu, tình trạng booking và chính sách của nhà cung cấp.',
                                'Trong trường hợp cần thiết để đảm bảo an toàn, tuân thủ quy định điểm đến hoặc xử lý yếu tố khách quan, Travel Plus có thể điều chỉnh lịch trình, sắp xếp dịch vụ tương đương hoặc thay đổi một số hạng mục hợp lý và sẽ thông báo cho khách hàng trong phạm vi có thể.',
                            ],
                        ],
                        [
                            'id' => 'dieu-khoan-theo-dich-vu',
                            'heading' => 'Điều khoản riêng theo nhóm dịch vụ',
                            'paragraphs' => [
                                'Ngoài các điều khoản chung, từng nhóm dịch vụ có thể có điều kiện riêng. Điều kiện cụ thể sẽ được thể hiện trong chương trình tour, báo giá, proposal, email xác nhận hoặc hợp đồng dịch vụ.',
                            ],
                            'subsections' => [
                                [
                                    'heading' => 'Tour du lịch',
                                    'bullets' => [
                                        'Lịch trình có thể điều chỉnh theo thời tiết, an toàn, quy định điểm đến hoặc điều kiện vận hành thực tế.',
                                        'Giá tour có thể phụ thuộc vào ngày khởi hành, số lượng khách, hạng dịch vụ, phụ thu lễ tết và tỷ giá.',
                                        'Khách hàng cần chuẩn bị giấy tờ cá nhân hợp lệ và tuân thủ quy định của điểm đến.',
                                    ],
                                ],
                                [
                                    'heading' => 'Dịch vụ visa',
                                    'bullets' => [
                                        'Travel Plus hỗ trợ tư vấn, checklist và chuẩn bị hồ sơ theo thông tin khách hàng cung cấp.',
                                        'Quyết định cấp hoặc từ chối visa thuộc thẩm quyền của cơ quan lãnh sự hoặc cơ quan quản lý xuất nhập cảnh.',
                                        'Khách hàng chịu trách nhiệm về tính trung thực, chính xác và đầy đủ của hồ sơ.',
                                    ],
                                ],
                                [
                                    'heading' => 'Vé máy bay, khách sạn và vận chuyển',
                                    'bullets' => [
                                        'Giá, tình trạng chỗ, điều kiện đổi tên, đổi ngày, hoàn hủy phụ thuộc vào hãng hàng không, khách sạn hoặc nhà cung cấp vận chuyển.',
                                        'Khách hàng cần kiểm tra kỹ thông tin hành khách, ngày giờ, điểm đón trả và điều kiện hành lý trước khi xác nhận.',
                                    ],
                                ],
                                [
                                    'heading' => 'MICE và dịch vụ doanh nghiệp',
                                    'bullets' => [
                                        'Proposal, báo giá và timeline có thể thay đổi theo số lượng khách, venue, hạng mục sản xuất, nhân sự onsite, yêu cầu branding và deadline triển khai.',
                                        'Các thay đổi phát sinh sau khi chốt brief có thể ảnh hưởng đến ngân sách, tiến độ hoặc điều kiện cung cấp dịch vụ.',
                                        'Đối với Medical Congress, symposium hoặc chương trình chuyên ngành, khách hàng cần cung cấp yêu cầu tuân thủ, nội dung khoa học và danh sách khách mời đúng hạn.',
                                    ],
                                ],
                                [
                                    'heading' => 'Dịch thuật và dịch vụ liên quan',
                                    'bullets' => [
                                        'Thời gian xử lý phụ thuộc vào độ dài, ngôn ngữ, định dạng tài liệu và yêu cầu công chứng hoặc hợp pháp hóa nếu có.',
                                        'Khách hàng chịu trách nhiệm cung cấp bản gốc hoặc bản scan rõ ràng, đầy đủ thông tin cần xử lý.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'trach-nhiem-cac-ben',
                            'heading' => 'Quyền và trách nhiệm của các bên',
                            'subsections' => [
                                [
                                    'heading' => 'Khách hàng',
                                    'bullets' => [
                                        'Thanh toán đúng hạn và chuẩn bị đầy đủ giấy tờ cần thiết.',
                                        'Tuân thủ quy định pháp luật, quy định của điểm đến, hãng vận chuyển, khách sạn và hướng dẫn sử dụng dịch vụ.',
                                        'Tự chịu trách nhiệm nếu bị từ chối xuất nhập cảnh, từ chối visa hoặc vi phạm quy định do lỗi từ thông tin, giấy tờ hoặc hành vi của khách hàng.',
                                    ],
                                ],
                                [
                                    'heading' => 'Travel Plus',
                                    'bullets' => [
                                        'Cung cấp thông tin dịch vụ rõ ràng trong phạm vi có thể tại thời điểm xác nhận.',
                                        'Hỗ trợ khách hàng trong quá trình đặt và sử dụng dịch vụ.',
                                        'Có quyền từ chối phục vụ hoặc điều chỉnh phương án cung cấp dịch vụ khi phát sinh rủi ro, vi phạm hoặc yêu cầu từ đối tác, cơ quan chức năng.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'gioi-han-trach-nhiem',
                            'heading' => 'Giới hạn trách nhiệm',
                            'paragraphs' => [
                                'Travel Plus không chịu trách nhiệm đối với thiệt hại phát sinh từ sự kiện bất khả kháng hoặc các yếu tố nằm ngoài khả năng kiểm soát hợp lý, bao gồm thiên tai, dịch bệnh, chiến tranh, thay đổi chính sách nhập cảnh, chậm trễ hoặc hủy dịch vụ từ hãng vận chuyển, khách sạn hay nhà cung cấp khác.',
                            ],
                        ],
                        [
                            'id' => 'so-huu-tri-tue',
                            'heading' => 'Sở hữu trí tuệ',
                            'bullets' => [
                                'Toàn bộ nội dung trên website, bao gồm văn bản, hình ảnh, bố cục, thiết kế, dữ liệu và tài sản thương hiệu, thuộc quyền sở hữu của Travel Plus hoặc bên cấp phép hợp pháp.',
                                'Mọi hành vi sao chép, phát tán, chỉnh sửa hoặc sử dụng lại khi chưa được phép đều bị nghiêm cấm.',
                            ],
                        ],
                        [
                            'id' => 'cap-nhat-dieu-khoan',
                            'heading' => 'Quyền cập nhật hoặc thay đổi điều khoản',
                            'paragraphs' => [
                                'Travel Plus có quyền cập nhật, bổ sung hoặc điều chỉnh Điều khoản sử dụng để phù hợp với quy định pháp luật, chính sách nhà cung cấp, thay đổi dịch vụ hoặc nhu cầu vận hành.',
                                'Phiên bản mới nhất sẽ được công bố trên website và có hiệu lực kể từ ngày đăng tải, trừ khi có thông báo khác. Khách hàng nên kiểm tra lại điều khoản trước khi đặt dịch vụ hoặc thanh toán.',
                            ],
                        ],
                        [
                            'id' => 'luat-ap-dung-lien-he',
                            'heading' => 'Luật áp dụng và liên hệ',
                            'paragraphs' => [
                                'Điều khoản này được điều chỉnh theo pháp luật Việt Nam. Mọi tranh chấp phát sinh sẽ được ưu tiên giải quyết thông qua thương lượng; nếu không đạt được thỏa thuận, tranh chấp sẽ được giải quyết tại cơ quan có thẩm quyền tại Việt Nam.',
                            ],
                            'bullets' => [
                                'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                                'Mã số doanh nghiệp: 0305475784',
                                'Số giấy phép kinh doanh lữ hành Quốc tế: 79-114/2014/TCDL-GP LHQT',
                                'VP HCM: 3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP.HCM',
                                'Hotline: +84 79 568 1 568',
                                'Email: info@travelplusvn.com',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'id' => 'scope',
                            'heading' => 'Scope of application',
                            'paragraphs' => [
                                'These Terms apply to all access to travelplusvn.com and to all services provided by Travel Plus.',
                                'By using the website, submitting a consultation form, booking a service or making a payment, customers acknowledge that they have read, understood and agreed to these Terms.',
                            ],
                            'bullets' => [
                                'Domestic tours, outbound tours and tailor-made tours.',
                                'Visa support, flights, hotels, transportation and translation services.',
                                'MICE, corporate travel, meetings, conferences, incentive programs, team building, gala dinner and business events.',
                            ],
                        ],
                        [
                            'id' => 'website-use',
                            'heading' => 'Website use conditions',
                            'bullets' => [
                                'Use the website only for lawful purposes and in compliance with applicable regulations.',
                                'Do not interfere with website data, source code, security or technical infrastructure.',
                                'Do not copy, exploit or reuse website content for commercial purposes without written approval from Travel Plus.',
                                'Travel Plus may restrict or terminate access when a violation is detected.',
                            ],
                        ],
                        [
                            'id' => 'customer-information',
                            'heading' => 'Customer information and accounts',
                            'bullets' => [
                                'Customers must provide accurate, complete and up-to-date information when submitting a request, booking a service or registering an account.',
                                'Customers are responsible for safeguarding account credentials and all activities under their account.',
                                'Travel Plus may suspend or refuse processing when information is inaccurate, false or suspected of fraud.',
                            ],
                        ],
                        [
                            'id' => 'service-information',
                            'heading' => 'Service information on the website',
                            'paragraphs' => [
                                'Itineraries, prices, availability, illustrative images, promotion conditions and applicable policies may change depending on timing, supplier policy and actual operations.',
                                'Travel Plus makes reasonable efforts to keep content accurate, but final information will be reconfirmed before a booking is completed.',
                            ],
                        ],
                        [
                            'id' => 'booking-payment',
                            'heading' => 'Booking, pricing and payment',
                            'bullets' => [
                                'Customers submit booking or consultation requests through the website, hotline, email, Zalo, Messenger or official Travel Plus channels.',
                                'Travel Plus confirms availability, total cost, applicable conditions, payment deadline and payment instructions.',
                                'Some services require a deposit or full payment within a stated deadline.',
                                'Customers are responsible for bank fees, payment gateway charges or foreign exchange fees where applicable.',
                            ],
                            'note' => 'A booking is completed only when Travel Plus confirms it in writing and/or records payment according to the relevant service conditions.',
                        ],
                        [
                            'id' => 'changes-cancellations-refunds',
                            'heading' => 'Changes, cancellations and refunds',
                            'paragraphs' => [
                                'Change, cancellation and refund conditions depend on service type, request timing, booking status and supplier policy.',
                                'Where necessary for safety, destination compliance or operational reasons, Travel Plus may adjust itineraries, arrange equivalent services or make reasonable service changes and will notify the customer where practicable.',
                            ],
                        ],
                        [
                            'id' => 'service-specific-terms',
                            'heading' => 'Service-specific terms',
                            'paragraphs' => [
                                'In addition to the general terms, each service group may have separate conditions. Specific conditions will be stated in the tour program, quotation, proposal, confirmation email or service contract.',
                            ],
                            'subsections' => [
                                [
                                    'heading' => 'Tours',
                                    'bullets' => [
                                        'Itineraries may be adjusted due to weather, safety, destination rules or actual operational conditions.',
                                        'Tour prices may depend on departure date, group size, service class, holiday surcharges and exchange rates.',
                                        'Customers must prepare valid personal documents and comply with destination rules.',
                                    ],
                                ],
                                [
                                    'heading' => 'Visa services',
                                    'bullets' => [
                                        'Travel Plus supports consultation, checklists and document preparation based on information provided by the customer.',
                                        'Visa approval or refusal is decided solely by the competent consular or immigration authority.',
                                        'Customers are responsible for the truthfulness, accuracy and completeness of their documents.',
                                    ],
                                ],
                                [
                                    'heading' => 'Flights, hotels and transportation',
                                    'bullets' => [
                                        'Prices, availability, name change, date change, refund and cancellation conditions depend on airlines, hotels or transportation providers.',
                                        'Customers should carefully check passenger details, dates, times, pickup points and baggage conditions before confirmation.',
                                    ],
                                ],
                                [
                                    'heading' => 'MICE and corporate services',
                                    'bullets' => [
                                        'Proposals, quotations and timelines may change according to guest count, venue, production scope, onsite staffing, branding requirements and implementation deadlines.',
                                        'Changes requested after brief approval may affect budget, timing or service conditions.',
                                        'For Medical Congress, symposium or specialized programs, customers should provide compliance requirements, scientific content and guest lists on time.',
                                    ],
                                ],
                                [
                                    'heading' => 'Translation and related services',
                                    'bullets' => [
                                        'Processing time depends on document length, language pair, file format and notarization or legalization requirements where applicable.',
                                        'Customers are responsible for providing clear and complete originals or scanned copies.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'responsibilities',
                            'heading' => 'Rights and responsibilities',
                            'subsections' => [
                                [
                                    'heading' => 'Customer',
                                    'bullets' => [
                                        'Make payments on time and prepare all required documents.',
                                        'Comply with applicable laws, destination rules, carrier rules, hotel policies and service instructions.',
                                        'Bear responsibility for refusals of entry, visa refusal or local violations arising from customer information, documents or conduct.',
                                    ],
                                ],
                                [
                                    'heading' => 'Travel Plus',
                                    'bullets' => [
                                        'Provide service information as clearly as reasonably possible at the time of confirmation.',
                                        'Support customers during the booking and service process.',
                                        'Refuse service or adjust the service plan where risk, non-compliance or third-party requirements arise.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'liability-limits',
                            'heading' => 'Limitation of liability',
                            'paragraphs' => [
                                'Travel Plus is not liable for losses caused by force majeure or events beyond its reasonable control, including natural disasters, epidemics, war, immigration policy changes, carrier delays or cancellations by third-party suppliers.',
                            ],
                        ],
                        [
                            'id' => 'intellectual-property',
                            'heading' => 'Intellectual property',
                            'bullets' => [
                                'All website content, including text, images, layout, design, data and brand assets, is owned by Travel Plus or its lawful licensors.',
                                'Any copying, redistribution, modification or reuse without permission is prohibited.',
                            ],
                        ],
                        [
                            'id' => 'terms-updates',
                            'heading' => 'Right to update or change these Terms',
                            'paragraphs' => [
                                'Travel Plus may update, supplement or amend these Terms to reflect legal requirements, supplier policies, service changes or operational needs.',
                                'The latest version will be published on the website and becomes effective from the publication date unless otherwise stated. Customers should review the Terms before booking or payment.',
                            ],
                        ],
                        [
                            'id' => 'law-contact',
                            'heading' => 'Governing law and contact',
                            'paragraphs' => [
                                'These Terms are governed by the laws of Vietnam. Any dispute should first be addressed through good-faith discussion; if unresolved, it shall be handled by the competent authority in Vietnam.',
                            ],
                            'bullets' => [
                                'Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                                'Business registration number: 0305475784',
                                'International tour operator license: 79-114/2014/TCDL-GP LHQT',
                                'Ho Chi Minh Office: 3/30A Thich Quang Duc Street, Duc Nhuan Ward, Ho Chi Minh City',
                                'Hotline: +84 79 568 1 568',
                                'Email: info@travelplusvn.com',
                            ],
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'title' => [
                    'vi' => 'Chính sách bảo mật',
                    'en' => 'Privacy Statement',
                ],
                'meta_title' => [
                    'vi' => 'Chính sách bảo mật thông tin khách hàng | Travel Plus',
                    'en' => 'Travel Plus Privacy Statement',
                ],
                'meta_desc' => [
                    'vi' => 'Chính sách bảo mật Travel Plus mô tả cách thu thập, sử dụng, lưu trữ, chia sẻ và bảo vệ dữ liệu cá nhân khi khách hàng dùng website, tour, visa, MICE và dịch vụ liên quan.',
                    'en' => 'Travel Plus Privacy Statement explains how personal data is collected, used, stored, shared and protected across the website, tours, visa, MICE and related services.',
                ],
                'subtitle' => [
                    'vi' => 'Chính sách này giúp khách hàng hiểu rõ Travel Plus xử lý dữ liệu cá nhân như thế nào khi tư vấn, đặt dịch vụ hoặc sử dụng website.',
                    'en' => 'This Statement explains how Travel Plus processes personal data when customers request consultation, book services or use the website.',
                ],
                'version' => '2.0',
                'effective_date' => '2026-04-10',
                'updated_at' => '2026-04-10',
                'updated_label' => [
                    'vi' => 'Cập nhật',
                    'en' => 'Updated',
                ],
                'effective_label' => [
                    'vi' => 'Hiệu lực từ',
                    'en' => 'Effective date',
                ],
                'version_label' => [
                    'vi' => 'Phiên bản',
                    'en' => 'Version',
                ],
                'toc_label' => [
                    'vi' => 'Mục lục',
                    'en' => 'Table of contents',
                ],
                'related_title' => [
                    'vi' => 'Chính sách liên quan',
                    'en' => 'Related policies',
                ],
                'print_label' => [
                    'vi' => 'In trang',
                    'en' => 'Print page',
                ],
                'search_label' => [
                    'vi' => 'Tìm trong chính sách',
                    'en' => 'Search within privacy statement',
                ],
                'search_placeholder' => [
                    'vi' => 'Nhập từ khóa như hộ chiếu, cookie, xóa dữ liệu...',
                    'en' => 'Search keywords like passport, cookie, delete data...',
                ],
                'notice_title' => [
                    'vi' => 'Lưu ý về dữ liệu cá nhân',
                    'en' => 'Personal data note',
                ],
                'notice' => [
                    'vi' => 'Travel Plus chỉ thu thập dữ liệu cần thiết cho mục đích tư vấn, đặt dịch vụ, thanh toán, chăm sóc khách hàng và tuân thủ pháp luật. Dữ liệu nhạy cảm như hộ chiếu, visa hoặc hồ sơ xuất nhập cảnh được xử lý trong phạm vi cần thiết để cung cấp dịch vụ.',
                    'en' => 'Travel Plus collects only data necessary for consultation, booking, payment, customer support and legal compliance. Sensitive data such as passport, visa or immigration documents is processed only to deliver the requested service.',
                ],
                'cta' => [
                    'vi' => [
                        'eyebrow' => 'Cần hỗ trợ về dữ liệu cá nhân?',
                        'title' => 'Liên hệ Travel Plus nếu bạn muốn kiểm tra, cập nhật hoặc yêu cầu xử lý dữ liệu cá nhân.',
                        'description' => 'Chúng tôi sẽ tiếp nhận yêu cầu và phản hồi theo phạm vi pháp luật cho phép cũng như quy trình vận hành nội bộ.',
                        'primary_label' => 'Gửi yêu cầu hỗ trợ',
                        'secondary_label' => 'Xem điều khoản sử dụng',
                    ],
                    'en' => [
                        'eyebrow' => 'Need support with personal data?',
                        'title' => 'Contact Travel Plus if you want to review, update or request handling of your personal data.',
                        'description' => 'We will receive and respond to requests within the scope permitted by law and internal operating procedures.',
                        'primary_label' => 'Send support request',
                        'secondary_label' => 'View Terms of Service',
                    ],
                ],
                'faq_title' => [
                    'vi' => 'Câu hỏi thường gặp về bảo mật',
                    'en' => 'Privacy FAQ',
                ],
                'faqs' => [
                    'vi' => [
                        [
                            'question' => 'Travel Plus thu thập những thông tin nào?',
                            'answer' => 'Travel Plus có thể thu thập họ tên, số điện thoại, email, thông tin hành trình, yêu cầu dịch vụ, dữ liệu kỹ thuật website và giấy tờ liên quan khi cần cho tour, visa hoặc dịch vụ doanh nghiệp.',
                        ],
                        [
                            'question' => 'Travel Plus có bán dữ liệu khách hàng không?',
                            'answer' => 'Không. Travel Plus không bán dữ liệu cá nhân. Thông tin chỉ được chia sẻ trong phạm vi cần thiết để cung cấp dịch vụ hoặc khi có yêu cầu hợp pháp từ cơ quan có thẩm quyền.',
                        ],
                        [
                            'question' => 'Tôi có thể yêu cầu chỉnh sửa hoặc xóa dữ liệu không?',
                            'answer' => 'Có. Khách hàng có thể liên hệ Travel Plus để yêu cầu xem, cập nhật, chỉnh sửa, hạn chế xử lý hoặc xóa dữ liệu trong phạm vi pháp luật cho phép.',
                        ],
                        [
                            'question' => 'Dữ liệu hộ chiếu và visa được xử lý thế nào?',
                            'answer' => 'Dữ liệu hộ chiếu, visa và hồ sơ xuất nhập cảnh chỉ được xử lý khi cần để tư vấn, chuẩn bị hồ sơ hoặc thực hiện dịch vụ khách hàng đã yêu cầu.',
                        ],
                    ],
                    'en' => [
                        [
                            'question' => 'What information does Travel Plus collect?',
                            'answer' => 'Travel Plus may collect name, phone number, email, travel details, service requests, website technical data and relevant documents where necessary for tours, visa or corporate services.',
                        ],
                        [
                            'question' => 'Does Travel Plus sell customer data?',
                            'answer' => 'No. Travel Plus does not sell personal data. Information is shared only where necessary to deliver services or comply with lawful requests from competent authorities.',
                        ],
                        [
                            'question' => 'Can I request correction or deletion of my data?',
                            'answer' => 'Yes. Customers may contact Travel Plus to request access, updates, corrections, restriction of processing or deletion within the scope permitted by law.',
                        ],
                        [
                            'question' => 'How is passport and visa data handled?',
                            'answer' => 'Passport, visa and immigration documents are processed only where necessary to consult, prepare documents or deliver services requested by the customer.',
                        ],
                    ],
                ],
                'sections' => [
                    'vi' => [
                        [
                            'id' => 'muc-dich-pham-vi',
                            'heading' => 'Mục đích và phạm vi',
                            'paragraphs' => [
                                'Chính sách này giải thích cách Travel Plus thu thập, sử dụng, lưu trữ, bảo vệ và chia sẻ thông tin cá nhân khi khách hàng truy cập website travelplusvn.com hoặc sử dụng dịch vụ do Travel Plus cung cấp.',
                                'Bằng việc sử dụng website hoặc cung cấp thông tin cho Travel Plus, khách hàng đồng ý với các nội dung được nêu trong chính sách này.',
                            ],
                        ],
                        [
                            'id' => 'thong-tin-thu-thap',
                            'heading' => 'Loại thông tin được thu thập',
                            'subsections' => [
                                [
                                    'heading' => 'Thông tin nhận diện và liên hệ',
                                    'bullets' => [
                                        'Họ và tên, email, số điện thoại, địa chỉ liên hệ.',
                                        'Thông tin tài khoản nếu khách hàng đăng ký tài khoản trên website.',
                                    ],
                                ],
                                [
                                    'heading' => 'Thông tin phục vụ dịch vụ',
                                    'bullets' => [
                                        'Hành trình, ngày đi, số lượng khách, ngân sách, mục đích chuyến đi và yêu cầu đặc biệt.',
                                        'Hộ chiếu, ngày sinh, quốc tịch, hồ sơ công việc, tài chính hoặc giấy tờ liên quan khi cần cho tour quốc tế, visa hoặc dịch vụ MICE.',
                                    ],
                                ],
                                [
                                    'heading' => 'Thông tin kỹ thuật và hành vi sử dụng',
                                    'bullets' => [
                                        'Địa chỉ IP, loại trình duyệt, thiết bị truy cập, cookies và dữ liệu phân tích tương tự.',
                                        'Lịch sử truy cập, thao tác trên website và nội dung khách hàng quan tâm.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'muc-dich-su-dung',
                            'heading' => 'Mục đích sử dụng thông tin',
                            'bullets' => [
                                'Xử lý yêu cầu tư vấn, đặt dịch vụ, thanh toán và chăm sóc sau bán.',
                                'Liên hệ xác nhận thông tin, cập nhật tiến độ xử lý hoặc hỗ trợ phát sinh.',
                                'Làm việc với đối tác liên quan như hãng hàng không, khách sạn, đơn vị vận chuyển, đại sứ quán, lãnh sự quán hoặc nhà cung cấp MICE khi cần.',
                                'Cải thiện trải nghiệm website, chất lượng dịch vụ và hoạt động tiếp thị hợp pháp nếu khách hàng đồng ý nhận thông tin.',
                            ],
                        ],
                        [
                            'id' => 'nguyen-tac-xu-ly',
                            'heading' => 'Nguyên tắc xử lý dữ liệu',
                            'bullets' => [
                                'Chỉ thu thập dữ liệu cần thiết cho mục đích cụ thể, rõ ràng.',
                                'Xử lý dữ liệu trên cơ sở sự đồng ý của khách hàng, việc thực hiện hợp đồng dịch vụ hoặc yêu cầu pháp lý liên quan.',
                                'Không sử dụng dữ liệu cá nhân cho mục đích trái pháp luật hoặc vượt quá phạm vi cần thiết.',
                            ],
                        ],
                        [
                            'id' => 'chia-se-thong-tin',
                            'heading' => 'Chia sẻ thông tin',
                            'paragraphs' => [
                                'Travel Plus không bán dữ liệu cá nhân của khách hàng. Thông tin chỉ được chia sẻ trong phạm vi cần thiết để cung cấp dịch vụ hoặc tuân thủ quy định pháp luật.',
                            ],
                            'bullets' => [
                                'Chia sẻ với nhà cung cấp dịch vụ hoặc đối tác vận hành để thực hiện đơn hàng.',
                                'Cung cấp cho cơ quan nhà nước có thẩm quyền khi có yêu cầu hợp pháp.',
                                'Sử dụng trong phạm vi cần thiết để bảo vệ quyền, tài sản hoặc lợi ích hợp pháp của Travel Plus trong tranh chấp hoặc xử lý rủi ro.',
                            ],
                            'note' => 'Các đối tác nhận dữ liệu chỉ được cung cấp thông tin trong phạm vi cần thiết cho dịch vụ liên quan.',
                        ],
                        [
                            'id' => 'luu-tru-bao-mat',
                            'heading' => 'Thời gian lưu trữ và bảo mật dữ liệu',
                            'bullets' => [
                                'Thông tin được lưu trữ trong thời gian cần thiết để hoàn thành mục đích thu thập hoặc trong thời hạn pháp luật yêu cầu.',
                                'Khi không còn cần thiết, dữ liệu sẽ được xóa, hủy hoặc ẩn danh theo quy trình nội bộ phù hợp.',
                                'Travel Plus áp dụng biện pháp kỹ thuật và quản trị hợp lý để hạn chế truy cập trái phép, mất mát hoặc lộ lọt dữ liệu.',
                                'Quyền truy cập dữ liệu nội bộ được giới hạn theo chức năng và nhu cầu công việc.',
                            ],
                        ],
                        [
                            'id' => 'cookies',
                            'heading' => 'Cookies và công nghệ theo dõi',
                            'bullets' => [
                                'Website có thể sử dụng cookies để ghi nhớ phiên làm việc, hỗ trợ đăng nhập, phân tích hành vi và cải thiện trải nghiệm.',
                                'Khách hàng có thể điều chỉnh trình duyệt để từ chối hoặc xóa cookies, tuy nhiên một số chức năng của website có thể bị ảnh hưởng.',
                            ],
                        ],
                        [
                            'id' => 'quyen-khach-hang',
                            'heading' => 'Quyền của khách hàng',
                            'bullets' => [
                                'Yêu cầu xem, cập nhật hoặc chỉnh sửa dữ liệu cá nhân Travel Plus đang lưu giữ.',
                                'Yêu cầu chấm dứt xử lý, hạn chế xử lý hoặc xóa dữ liệu trong phạm vi pháp luật cho phép.',
                                'Từ chối nhận thông tin tiếp thị bất kỳ lúc nào.',
                                'Liên hệ khi có thắc mắc, khiếu nại hoặc yêu cầu liên quan đến dữ liệu cá nhân.',
                            ],
                        ],
                        [
                            'id' => 'du-lieu-nhay-cam',
                            'heading' => 'Thanh toán và dữ liệu nhạy cảm',
                            'bullets' => [
                                'Travel Plus không chủ động lưu trữ đầy đủ thông tin thẻ thanh toán trên website.',
                                'Một số giao dịch được xử lý qua cổng thanh toán trung gian như VNPay hoặc PayPal và chịu thêm chính sách của bên cung cấp cổng thanh toán.',
                                'Dữ liệu nhạy cảm như hộ chiếu, visa hoặc hồ sơ xuất nhập cảnh chỉ được xử lý trong phạm vi cần thiết để cung cấp dịch vụ.',
                            ],
                        ],
                        [
                            'id' => 'tre-em-lien-ket-ben-thu-ba',
                            'heading' => 'Dữ liệu trẻ em và liên kết bên thứ ba',
                            'paragraphs' => [
                                'Website không được thiết kế nhằm chủ đích thu thập dữ liệu từ trẻ em dưới độ tuổi luật định nếu không có sự tham gia của người giám hộ hợp pháp.',
                                'Website có thể chứa liên kết đến website hoặc nền tảng của bên thứ ba. Travel Plus không chịu trách nhiệm về nội dung hay chính sách bảo mật của các bên đó.',
                            ],
                        ],
                        [
                            'id' => 'cap-nhat-chinh-sach',
                            'heading' => 'Cập nhật chính sách và liên hệ',
                            'paragraphs' => [
                                'Travel Plus có thể cập nhật Chính sách bảo mật theo từng thời điểm để phù hợp với quy định pháp luật, công nghệ, dịch vụ hoặc quy trình vận hành.',
                                'Phiên bản mới nhất sẽ được công bố trên website và có hiệu lực kể từ ngày đăng tải, trừ khi có thông báo khác.',
                            ],
                            'bullets' => [
                                'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                                'Mã số doanh nghiệp: 0305475784',
                                'Số giấy phép kinh doanh lữ hành Quốc tế: 79-114/2014/TCDL-GP LHQT',
                                'Hotline: +84 79 568 1 568',
                                'Email: info@travelplusvn.com',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'id' => 'purpose-scope',
                            'heading' => 'Purpose and scope',
                            'paragraphs' => [
                                'This Privacy Statement explains how Travel Plus collects, uses, stores, protects and shares personal information when customers access travelplusvn.com or use Travel Plus services.',
                                'By using the website or providing information to Travel Plus, customers agree to the practices described in this Statement.',
                            ],
                        ],
                        [
                            'id' => 'information-collected',
                            'heading' => 'Types of information collected',
                            'subsections' => [
                                [
                                    'heading' => 'Identity and contact data',
                                    'bullets' => [
                                        'Full name, email address, phone number and contact address.',
                                        'Account information when a customer registers an account on the website.',
                                    ],
                                ],
                                [
                                    'heading' => 'Service-related data',
                                    'bullets' => [
                                        'Itinerary, travel date, number of guests, budget, travel purpose and special requests.',
                                        'Passport, date of birth, nationality, employment, financial or related documents where necessary for international tours, visa or MICE services.',
                                    ],
                                ],
                                [
                                    'heading' => 'Technical and usage data',
                                    'bullets' => [
                                        'IP address, browser type, device information, cookies and similar analytics data.',
                                        'Browsing history, website actions and content of interest.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'purposes-of-use',
                            'heading' => 'Purposes of use',
                            'bullets' => [
                                'To process consultations, bookings, payments and after-sales support.',
                                'To contact customers for confirmation, service updates or issue resolution.',
                                'To work with relevant partners such as airlines, hotels, transportation providers, embassies, consulates or MICE suppliers when needed.',
                                'To improve website experience, service quality and lawful marketing activities where the customer has agreed to receive communications.',
                            ],
                        ],
                        [
                            'id' => 'processing-principles',
                            'heading' => 'Data processing principles',
                            'bullets' => [
                                'Only data necessary for a specific and legitimate purpose is collected.',
                                'Data is processed based on customer consent, service contract performance or relevant legal requirements.',
                                'Personal data is not used for unlawful purposes or beyond what is reasonably necessary.',
                            ],
                        ],
                        [
                            'id' => 'information-sharing',
                            'heading' => 'Information sharing',
                            'paragraphs' => [
                                'Travel Plus does not sell customer personal data. Information is shared only where necessary to deliver services or comply with legal requirements.',
                            ],
                            'bullets' => [
                                'With service providers or operating partners to fulfill a booking or request.',
                                'With competent authorities when legally required.',
                                'Where reasonably necessary to protect Travel Plus rights, assets or legitimate interests in disputes or risk management situations.',
                            ],
                            'note' => 'Partners receiving data are provided only the information necessary for the relevant service.',
                        ],
                        [
                            'id' => 'retention-security',
                            'heading' => 'Retention and data security',
                            'bullets' => [
                                'Data is retained for as long as necessary to fulfill the purpose of collection or as required by law.',
                                'When no longer needed, data will be deleted, destroyed or anonymized through appropriate internal procedures.',
                                'Travel Plus applies reasonable technical and organizational safeguards to reduce unauthorized access, loss or disclosure.',
                                'Internal access is restricted according to role and operational need.',
                            ],
                        ],
                        [
                            'id' => 'cookies',
                            'heading' => 'Cookies and tracking technologies',
                            'bullets' => [
                                'The website may use cookies to maintain sessions, support sign-in, analyze usage and improve the user experience.',
                                'Customers may adjust browser settings to reject or delete cookies, although some website functions may then be limited.',
                            ],
                        ],
                        [
                            'id' => 'customer-rights',
                            'heading' => 'Customer rights',
                            'bullets' => [
                                'Request access to, correction of or updates to the personal data held by Travel Plus.',
                                'Request restriction, cessation of processing or deletion of data where permitted by law.',
                                'Opt out of marketing communications at any time.',
                                'Contact Travel Plus with questions, complaints or requests relating to personal data processing.',
                            ],
                        ],
                        [
                            'id' => 'sensitive-data',
                            'heading' => 'Payments and sensitive data',
                            'bullets' => [
                                'Travel Plus does not intentionally store complete payment card information on the website.',
                                'Some transactions are processed via third-party gateways such as VNPay or PayPal and are subject to the policies of those providers.',
                                'Sensitive data such as passport, visa or immigration-related information is processed only to the extent necessary to provide the requested service.',
                            ],
                        ],
                        [
                            'id' => 'children-third-party-links',
                            'heading' => 'Children data and third-party links',
                            'paragraphs' => [
                                'The website is not intended to knowingly collect personal data from children below the age defined by applicable law without the involvement of a lawful guardian.',
                                'The website may contain links to third-party sites or platforms. Travel Plus is not responsible for the content or privacy practices of those third parties.',
                            ],
                        ],
                        [
                            'id' => 'policy-updates-contact',
                            'heading' => 'Policy updates and contact',
                            'paragraphs' => [
                                'Travel Plus may update this Privacy Statement from time to time to reflect legal, technology, service or operational changes.',
                                'The latest version will be published on the website and takes effect from the publication date unless otherwise stated.',
                            ],
                            'bullets' => [
                                'Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                                'Business registration number: 0305475784',
                                'International tour operator license: 79-114/2014/TCDL-GP LHQT',
                                'Hotline: +84 79 568 1 568',
                                'Email: info@travelplusvn.com',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
