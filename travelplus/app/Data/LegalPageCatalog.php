<?php

namespace App\Data;

final class LegalPageCatalog
{
    /**
     * @return array<string, mixed>
     */
    public static function get(string $type, string $locale = 'vi'): array
    {
        $pages = self::pages();
        $page = $pages[$type] ?? null;

        if ($page === null) {
            return [];
        }

        return [
            'title' => $page['title'][$locale] ?? $page['title']['vi'],
            'meta_title' => $page['meta_title'][$locale] ?? $page['meta_title']['vi'],
            'meta_desc' => $page['meta_desc'][$locale] ?? $page['meta_desc']['vi'],
            'subtitle' => $page['subtitle'][$locale] ?? $page['subtitle']['vi'],
            'updated_at' => $page['updated_at'],
            'updated_label' => $page['updated_label'][$locale] ?? $page['updated_label']['vi'],
            'sections' => $page['sections'][$locale] ?? $page['sections']['vi'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function pages(): array
    {
        return [
            'terms' => [
                'title' => [
                    'vi' => 'Điều Khoản Sử Dụng',
                    'en' => 'Terms of Service',
                ],
                'meta_title' => [
                    'vi' => 'Điều Khoản Sử Dụng | Travel Plus',
                    'en' => 'Terms of Service | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Điều khoản sử dụng website và dịch vụ của Travel Plus, bao gồm đặt tour, thanh toán, thay đổi dịch vụ, trách nhiệm các bên và giải quyết tranh chấp.',
                    'en' => 'Terms governing the use of Travel Plus website and services, including bookings, payments, service changes, responsibilities, and dispute resolution.',
                ],
                'subtitle' => [
                    'vi' => 'Website: travelplusvn.com - Đơn vị vận hành: Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                    'en' => 'Website: travelplusvn.com - Operated by Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                ],
                'updated_at' => '10/04/2026',
                'updated_label' => [
                    'vi' => 'Ngày cập nhật',
                    'en' => 'Last updated',
                ],
                'sections' => [
                    'vi' => [
                        [
                            'heading' => '1. Phạm vi áp dụng',
                            'paragraphs' => [
                                'Điều khoản này áp dụng cho toàn bộ việc truy cập website travelplusvn.com và việc sử dụng các dịch vụ do Travel Plus cung cấp, bao gồm tour du lịch, visa, vé máy bay, khách sạn, MICE, vận chuyển, dịch thuật và các dịch vụ liên quan.',
                                'Khi truy cập website hoặc gửi yêu cầu sử dụng dịch vụ, khách hàng được xem là đã đọc, hiểu và đồng ý với các điều khoản này.',
                            ],
                        ],
                        [
                            'heading' => '2. Điều kiện sử dụng website',
                            'bullets' => [
                                'Chỉ sử dụng website cho các mục đích hợp pháp.',
                                'Không can thiệp trái phép vào dữ liệu, mã nguồn, chức năng, bảo mật hoặc hạ tầng kỹ thuật của website.',
                                'Không sử dụng nội dung trên website cho mục đích thương mại khi chưa có chấp thuận bằng văn bản từ Travel Plus.',
                                'Travel Plus có quyền hạn chế hoặc chấm dứt quyền truy cập nếu phát hiện hành vi vi phạm.',
                            ],
                        ],
                        [
                            'heading' => '3. Thông tin khách hàng và tài khoản',
                            'bullets' => [
                                'Khách hàng phải cung cấp thông tin chính xác, đầy đủ và cập nhật khi đăng ký tài khoản hoặc đặt dịch vụ.',
                                'Khách hàng tự chịu trách nhiệm bảo mật tài khoản, mật khẩu và mọi hoạt động phát sinh từ tài khoản của mình.',
                                'Travel Plus có quyền tạm khóa hoặc chấm dứt tài khoản nếu phát hiện thông tin sai lệch, giả mạo hoặc có dấu hiệu gian lận.',
                            ],
                        ],
                        [
                            'heading' => '4. Thông tin dịch vụ trên website',
                            'paragraphs' => [
                                'Thông tin về hành trình, giá, tình trạng chỗ, hình ảnh minh họa và các điều kiện áp dụng có thể thay đổi theo thời điểm, theo chính sách của đối tác hoặc theo điều kiện vận hành thực tế.',
                                'Travel Plus nỗ lực duy trì tính chính xác của nội dung nhưng không cam kết mọi thông tin luôn cố định tại mọi thời điểm trước khi đơn hàng được xác nhận chính thức.',
                            ],
                        ],
                        [
                            'heading' => '5. Quy trình đặt dịch vụ',
                            'bullets' => [
                                'Khách hàng lựa chọn dịch vụ và gửi yêu cầu đặt chỗ hoặc yêu cầu tư vấn.',
                                'Travel Plus xác nhận tình trạng chỗ, điều kiện áp dụng, tổng chi phí và hướng dẫn thanh toán.',
                                'Đơn hàng chỉ được xem là hoàn tất khi Travel Plus xác nhận thành công và/hoặc ghi nhận thanh toán theo quy định của từng dịch vụ.',
                                'Travel Plus có quyền từ chối đơn hàng trong trường hợp hết chỗ, thông tin không hợp lệ, thanh toán không thành công hoặc có dấu hiệu rủi ro.',
                            ],
                        ],
                        [
                            'heading' => '6. Giá và thanh toán',
                            'bullets' => [
                                'Giá hiển thị có thể thay đổi theo thời điểm khởi hành, tình trạng chỗ, tỷ giá, thuế, phụ phí hoặc chính sách của nhà cung cấp.',
                                'Một số dịch vụ yêu cầu đặt cọc hoặc thanh toán toàn bộ trong thời hạn cụ thể do Travel Plus thông báo.',
                                'Khách hàng chịu các chi phí phát sinh từ ngân hàng, cổng thanh toán hoặc chuyển đổi ngoại tệ nếu có.',
                                'Travel Plus có thể cung cấp nhiều phương thức thanh toán như chuyển khoản, tiền mặt, VNPay, PayPal, MoMo hoặc các phương thức khác được thông báo tại thời điểm thanh toán.',
                            ],
                        ],
                        [
                            'heading' => '7. Thay đổi, hủy dịch vụ và hoàn tiền',
                            'paragraphs' => [
                                'Điều kiện thay đổi, hủy dịch vụ hoặc hoàn tiền phụ thuộc vào loại dịch vụ, thời điểm yêu cầu và chính sách của đối tác cung cấp dịch vụ.',
                                'Trong trường hợp cần thiết để bảo đảm an toàn, tuân thủ quy định của điểm đến hoặc xử lý các yếu tố khách quan, Travel Plus có thể điều chỉnh lịch trình, sắp xếp dịch vụ tương đương hoặc thay đổi một số hạng mục hợp lý và sẽ thông báo cho khách hàng trong phạm vi có thể.',
                            ],
                        ],
                        [
                            'heading' => '8. Dịch vụ visa',
                            'bullets' => [
                                'Travel Plus cung cấp dịch vụ tư vấn, tiếp nhận và hỗ trợ chuẩn bị hồ sơ visa.',
                                'Quyết định cấp hay từ chối visa hoàn toàn thuộc thẩm quyền của cơ quan lãnh sự hoặc cơ quan quản lý xuất nhập cảnh.',
                                'Travel Plus không cam kết tỷ lệ đậu visa và không chịu trách nhiệm đối với việc hồ sơ bị từ chối do quyết định từ cơ quan có thẩm quyền.',
                                'Khách hàng chịu trách nhiệm về tính trung thực, chính xác và đầy đủ của hồ sơ cung cấp.',
                            ],
                        ],
                        [
                            'heading' => '9. Quyền và trách nhiệm của các bên',
                            'subsections' => [
                                [
                                    'heading' => '9.1. Khách hàng',
                                    'bullets' => [
                                        'Tuân thủ quy định pháp luật, quy định của điểm đến và hướng dẫn sử dụng dịch vụ.',
                                        'Thanh toán đúng hạn và chuẩn bị đầy đủ giấy tờ cần thiết.',
                                        'Tự chịu trách nhiệm nếu bị từ chối xuất nhập cảnh, bị từ chối visa hoặc vi phạm quy định địa phương do lỗi từ phía khách hàng.',
                                    ],
                                ],
                                [
                                    'heading' => '9.2. Travel Plus',
                                    'bullets' => [
                                        'Cung cấp thông tin dịch vụ rõ ràng trong phạm vi có thể tại thời điểm xác nhận.',
                                        'Hỗ trợ khách hàng trong quá trình đặt và sử dụng dịch vụ.',
                                        'Có quyền từ chối phục vụ hoặc điều chỉnh phương án cung cấp dịch vụ khi phát sinh rủi ro, vi phạm hoặc yêu cầu từ đối tác, cơ quan chức năng.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'heading' => '10. Giới hạn trách nhiệm',
                            'paragraphs' => [
                                'Travel Plus không chịu trách nhiệm đối với các thiệt hại phát sinh do sự kiện bất khả kháng hoặc các yếu tố nằm ngoài khả năng kiểm soát hợp lý như thiên tai, dịch bệnh, chiến tranh, thay đổi chính sách nhập cảnh, chậm trễ hoặc hủy dịch vụ từ hãng vận chuyển, khách sạn hay các bên cung cấp khác.',
                            ],
                        ],
                        [
                            'heading' => '11. Sở hữu trí tuệ',
                            'bullets' => [
                                'Toàn bộ nội dung trên website, bao gồm văn bản, hình ảnh, bố cục, thiết kế và dữ liệu, thuộc quyền sở hữu của Travel Plus hoặc bên cấp phép hợp pháp.',
                                'Mọi hành vi sao chép, phát tán, chỉnh sửa hoặc sử dụng lại khi chưa được phép đều bị nghiêm cấm.',
                            ],
                        ],
                        [
                            'heading' => '12. Luật áp dụng và liên hệ',
                            'paragraphs' => [
                                'Điều khoản này được điều chỉnh theo pháp luật Việt Nam. Mọi tranh chấp phát sinh sẽ được ưu tiên giải quyết thông qua thương lượng; nếu không đạt được thỏa thuận, tranh chấp sẽ được giải quyết tại cơ quan có thẩm quyền tại Việt Nam.',
                            ],
                            'bullets' => [
                                'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                                'VP HCM: 3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP.HCM',
                                'VP Hà Nội: 47 đường Lê Văn Hưu, Phường Hai Bà Trưng, Hà Nội',
                                'VP Đà Nẵng: Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, Đà Nẵng',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'heading' => '1. Scope of application',
                            'paragraphs' => [
                                'These Terms apply to all access to travelplusvn.com and to all services provided by Travel Plus, including tours, visa support, flight tickets, hotels, MICE services, transportation, translation, and related services.',
                                'By using the website or submitting a service request, you acknowledge that you have read, understood, and agreed to these Terms.',
                            ],
                        ],
                        [
                            'heading' => '2. Website use conditions',
                            'bullets' => [
                                'The website may only be used for lawful purposes.',
                                'Users must not attempt to interfere with the website, its data, security, infrastructure, or source code.',
                                'Website content may not be reused for commercial purposes without Travel Plus written approval.',
                                'Travel Plus may suspend or terminate access if a violation is detected.',
                            ],
                        ],
                        [
                            'heading' => '3. Customer information and accounts',
                            'bullets' => [
                                'Customers must provide accurate, complete, and up-to-date information when registering or booking services.',
                                'Customers are responsible for safeguarding account credentials and all activities under their account.',
                                'Travel Plus may suspend or terminate an account in cases of inaccurate information, impersonation, or suspected fraud.',
                            ],
                        ],
                        [
                            'heading' => '4. Service information on the website',
                            'paragraphs' => [
                                'Itineraries, prices, availability, illustrative images, and service conditions may change depending on timing, supplier policies, and operational circumstances.',
                                'Travel Plus makes reasonable efforts to keep information accurate, but cannot guarantee that all content will remain unchanged before a booking is formally confirmed.',
                            ],
                        ],
                        [
                            'heading' => '5. Booking process',
                            'bullets' => [
                                'Customers select a service and submit a booking or consultation request.',
                                'Travel Plus confirms availability, applicable conditions, total cost, and payment instructions.',
                                'A booking is considered completed only after it is confirmed by Travel Plus and/or payment is successfully recorded, depending on the service type.',
                                'Travel Plus may decline a booking in cases such as no availability, invalid information, payment failure, or risk concerns.',
                            ],
                        ],
                        [
                            'heading' => '6. Pricing and payment',
                            'bullets' => [
                                'Displayed prices may change based on departure date, seat availability, exchange rates, taxes, surcharges, or supplier policies.',
                                'Some services require a deposit or full payment within a deadline communicated by Travel Plus.',
                                'Customers are responsible for banking fees, payment gateway charges, or foreign exchange fees where applicable.',
                                'Payment methods may include bank transfer, cash, VNPay, PayPal, MoMo, or other methods announced at checkout.',
                            ],
                        ],
                        [
                            'heading' => '7. Changes, cancellations, and refunds',
                            'paragraphs' => [
                                'Change, cancellation, and refund conditions depend on the service type, timing of the request, and supplier policy.',
                                'Where necessary for safety, destination compliance, or operational reasons, Travel Plus may adjust itineraries, arrange equivalent services, or make reasonable service changes and will notify the customer where practicable.',
                            ],
                        ],
                        [
                            'heading' => '8. Visa services',
                            'bullets' => [
                                'Travel Plus provides consultation and document preparation support for visa applications.',
                                'Visa approval or refusal is solely at the discretion of the competent consular or immigration authority.',
                                'Travel Plus does not guarantee visa approval and is not liable for refusals issued by competent authorities.',
                                'Customers are responsible for the accuracy and completeness of the documents they provide.',
                            ],
                        ],
                        [
                            'heading' => '9. Rights and responsibilities',
                            'subsections' => [
                                [
                                    'heading' => '9.1. Customer',
                                    'bullets' => [
                                        'Comply with applicable laws, destination rules, and service instructions.',
                                        'Make payments on time and prepare all required documents.',
                                        'Bear responsibility for refusals of entry, visa refusal, or local violations arising from the customer’s own conduct or documentation.',
                                    ],
                                ],
                                [
                                    'heading' => '9.2. Travel Plus',
                                    'bullets' => [
                                        'Provide service information as clearly as reasonably possible at the time of confirmation.',
                                        'Support customers during the booking and service process.',
                                        'Refuse service or adjust the service plan where risk, non-compliance, or third-party requirements arise.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'heading' => '10. Limitation of liability',
                            'paragraphs' => [
                                'Travel Plus is not liable for losses caused by force majeure or events beyond its reasonable control, including natural disasters, epidemics, war, immigration policy changes, carrier delays, or cancellations by third-party suppliers.',
                            ],
                        ],
                        [
                            'heading' => '11. Intellectual property',
                            'bullets' => [
                                'All website content, including text, images, layout, design, and data, is owned by Travel Plus or its lawful licensors.',
                                'Any copying, redistribution, modification, or reuse without permission is prohibited.',
                            ],
                        ],
                        [
                            'heading' => '12. Governing law and contact',
                            'paragraphs' => [
                                'These Terms are governed by the laws of Vietnam. Any dispute should first be addressed through good-faith discussion; if unresolved, it shall be handled by the competent authority in Vietnam.',
                            ],
                            'bullets' => [
                                'Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                                'Ho Chi Minh Office: 3/30A Thich Quang Duc Street, Duc Nhuan Ward, Ho Chi Minh City',
                                'Hanoi Office: 47 Le Van Huu Street, Hai Ba Trung Ward, Hanoi',
                                'Da Nang Office: 4th Floor, Mien Trung Helicopter Building, Nguyen Van Linh Street, Hoa Cuong Ward, Da Nang',
                            ],
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'title' => [
                    'vi' => 'Chính Sách Bảo Mật',
                    'en' => 'Privacy Statement',
                ],
                'meta_title' => [
                    'vi' => 'Chính Sách Bảo Mật | Travel Plus',
                    'en' => 'Privacy Statement | Travel Plus',
                ],
                'meta_desc' => [
                    'vi' => 'Chính sách bảo mật mô tả cách Travel Plus thu thập, sử dụng, lưu trữ, chia sẻ và bảo vệ dữ liệu cá nhân khi khách hàng sử dụng website và dịch vụ.',
                    'en' => 'Privacy Statement describing how Travel Plus collects, uses, stores, shares, and protects personal data when customers use the website and services.',
                ],
                'subtitle' => [
                    'vi' => 'Website: travelplusvn.com - Đơn vị vận hành: Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                    'en' => 'Website: travelplusvn.com - Operated by Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                ],
                'updated_at' => '10/04/2026',
                'updated_label' => [
                    'vi' => 'Ngày cập nhật',
                    'en' => 'Last updated',
                ],
                'sections' => [
                    'vi' => [
                        [
                            'heading' => '1. Mục đích và phạm vi',
                            'paragraphs' => [
                                'Chính sách này giải thích cách Travel Plus thu thập, sử dụng, lưu trữ, bảo vệ và chia sẻ thông tin cá nhân của khách hàng khi truy cập website travelplusvn.com hoặc sử dụng các dịch vụ do Travel Plus cung cấp.',
                                'Bằng việc sử dụng website hoặc cung cấp thông tin cho Travel Plus, khách hàng đồng ý với các nội dung được nêu trong chính sách này.',
                            ],
                        ],
                        [
                            'heading' => '2. Loại thông tin được thu thập',
                            'subsections' => [
                                [
                                    'heading' => '2.1. Thông tin nhận diện và liên hệ',
                                    'bullets' => [
                                        'Họ và tên, email, số điện thoại, địa chỉ liên hệ.',
                                        'Thông tin tài khoản nếu khách hàng đăng ký tài khoản trên website.',
                                    ],
                                ],
                                [
                                    'heading' => '2.2. Thông tin phục vụ dịch vụ',
                                    'bullets' => [
                                        'Thông tin hành trình, ngày đi, số lượng khách, yêu cầu đặc biệt.',
                                        'Thông tin hộ chiếu, ngày sinh, quốc tịch hoặc giấy tờ liên quan khi cần cho tour quốc tế hoặc visa.',
                                    ],
                                ],
                                [
                                    'heading' => '2.3. Thông tin kỹ thuật và hành vi sử dụng',
                                    'bullets' => [
                                        'Địa chỉ IP, loại trình duyệt, thiết bị truy cập, cookies và các dữ liệu phân tích tương tự.',
                                        'Lịch sử truy cập, thao tác trên website và nội dung quan tâm.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'heading' => '3. Mục đích sử dụng thông tin',
                            'bullets' => [
                                'Xử lý yêu cầu tư vấn, đặt dịch vụ, thanh toán và chăm sóc sau bán.',
                                'Liên hệ với khách hàng để xác nhận thông tin, cập nhật tiến độ xử lý hoặc hỗ trợ phát sinh.',
                                'Thực hiện dịch vụ với các đối tác liên quan như hãng hàng không, khách sạn, đơn vị tổ chức tour, đại sứ quán hoặc lãnh sự quán khi cần.',
                                'Cải thiện trải nghiệm website, chất lượng dịch vụ và hoạt động tiếp thị hợp pháp nếu khách hàng đồng ý nhận thông tin.',
                            ],
                        ],
                        [
                            'heading' => '4. Cơ sở và nguyên tắc xử lý dữ liệu',
                            'bullets' => [
                                'Chỉ thu thập dữ liệu cần thiết cho mục đích cụ thể, rõ ràng.',
                                'Thông tin được xử lý trên cơ sở sự đồng ý của khách hàng, việc thực hiện hợp đồng dịch vụ hoặc yêu cầu pháp lý liên quan.',
                                'Travel Plus không sử dụng dữ liệu cá nhân cho mục đích trái pháp luật hoặc vượt quá phạm vi cần thiết.',
                            ],
                        ],
                        [
                            'heading' => '5. Chia sẻ thông tin',
                            'paragraphs' => [
                                'Travel Plus không bán dữ liệu cá nhân của khách hàng. Thông tin chỉ được chia sẻ trong phạm vi cần thiết để cung cấp dịch vụ hoặc tuân thủ quy định pháp luật.',
                            ],
                            'bullets' => [
                                'Chia sẻ với nhà cung cấp dịch vụ hoặc đối tác vận hành để thực hiện đơn hàng.',
                                'Cung cấp cho cơ quan nhà nước có thẩm quyền khi có yêu cầu hợp pháp.',
                                'Sử dụng trong phạm vi cần thiết để bảo vệ quyền, tài sản hoặc lợi ích hợp pháp của Travel Plus trong tranh chấp hoặc xử lý rủi ro.',
                            ],
                        ],
                        [
                            'heading' => '6. Thời gian lưu trữ',
                            'bullets' => [
                                'Thông tin được lưu trữ trong thời gian cần thiết để hoàn thành mục đích thu thập hoặc trong thời hạn pháp luật yêu cầu.',
                                'Khi không còn cần thiết, dữ liệu sẽ được xóa, hủy hoặc ẩn danh theo quy trình nội bộ phù hợp.',
                            ],
                        ],
                        [
                            'heading' => '7. Bảo mật dữ liệu',
                            'bullets' => [
                                'Travel Plus áp dụng các biện pháp kỹ thuật và quản trị hợp lý để hạn chế truy cập trái phép, mất mát hoặc lộ lọt dữ liệu.',
                                'Quyền truy cập dữ liệu nội bộ được giới hạn theo chức năng và nhu cầu công việc.',
                                'Dù vậy, không có hệ thống truyền tải hoặc lưu trữ dữ liệu nào trên Internet đạt mức an toàn tuyệt đối; khách hàng cũng cần chủ động bảo vệ thông tin cá nhân của mình.',
                            ],
                        ],
                        [
                            'heading' => '8. Cookies và công nghệ theo dõi',
                            'bullets' => [
                                'Website có thể sử dụng cookies để ghi nhớ phiên làm việc, hỗ trợ đăng nhập, phân tích hành vi và cải thiện trải nghiệm.',
                                'Khách hàng có thể điều chỉnh trình duyệt để từ chối hoặc xóa cookies, tuy nhiên một số chức năng của website có thể bị ảnh hưởng.',
                            ],
                        ],
                        [
                            'heading' => '9. Quyền của khách hàng',
                            'bullets' => [
                                'Yêu cầu xem, cập nhật hoặc chỉnh sửa dữ liệu cá nhân do Travel Plus đang lưu giữ.',
                                'Yêu cầu chấm dứt xử lý, hạn chế xử lý hoặc xóa dữ liệu trong phạm vi pháp luật cho phép.',
                                'Từ chối nhận thông tin tiếp thị bất kỳ lúc nào.',
                                'Liên hệ khi có thắc mắc, khiếu nại hoặc yêu cầu liên quan đến việc xử lý dữ liệu cá nhân.',
                            ],
                        ],
                        [
                            'heading' => '10. Thanh toán và dữ liệu nhạy cảm',
                            'bullets' => [
                                'Travel Plus không chủ động lưu trữ thông tin thẻ thanh toán đầy đủ trên website.',
                                'Một số giao dịch được xử lý thông qua cổng thanh toán trung gian như VNPay hoặc PayPal và sẽ chịu thêm chính sách của bên cung cấp cổng thanh toán.',
                                'Với dữ liệu nhạy cảm như hộ chiếu, visa hoặc thông tin phục vụ xuất nhập cảnh, Travel Plus chỉ xử lý trong phạm vi cần thiết để cung cấp dịch vụ.',
                            ],
                        ],
                        [
                            'heading' => '11. Dữ liệu trẻ em và liên kết bên thứ ba',
                            'paragraphs' => [
                                'Website không được thiết kế nhằm chủ đích thu thập dữ liệu từ trẻ em dưới độ tuổi luật định nếu không có sự tham gia của người giám hộ hợp pháp.',
                                'Website có thể chứa liên kết đến website hoặc nền tảng của bên thứ ba. Travel Plus không chịu trách nhiệm về nội dung hay chính sách bảo mật của các bên đó.',
                            ],
                        ],
                        [
                            'heading' => '12. Cập nhật chính sách và liên hệ',
                            'paragraphs' => [
                                'Travel Plus có thể cập nhật Chính sách bảo mật theo từng thời điểm. Phiên bản mới nhất sẽ được công bố trên website và có hiệu lực kể từ thời điểm đăng tải.',
                            ],
                            'bullets' => [
                                'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                                'VP HCM: 3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP.HCM',
                                'VP Hà Nội: 47 đường Lê Văn Hưu, Phường Hai Bà Trưng, Hà Nội',
                                'VP Đà Nẵng: Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, Đà Nẵng',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'heading' => '1. Purpose and scope',
                            'paragraphs' => [
                                'This Privacy Statement explains how Travel Plus collects, uses, stores, protects, and shares personal data when customers access travelplusvn.com or use services provided by Travel Plus.',
                                'By using the website or providing information to Travel Plus, customers agree to the practices described in this Statement.',
                            ],
                        ],
                        [
                            'heading' => '2. Types of information collected',
                            'subsections' => [
                                [
                                    'heading' => '2.1. Identity and contact data',
                                    'bullets' => [
                                        'Full name, email address, phone number, and contact address.',
                                        'Account information when a customer registers an account on the website.',
                                    ],
                                ],
                                [
                                    'heading' => '2.2. Service-related data',
                                    'bullets' => [
                                        'Travel details, departure dates, number of guests, and special requests.',
                                        'Passport data, date of birth, nationality, or related documents where necessary for international travel or visa services.',
                                    ],
                                ],
                                [
                                    'heading' => '2.3. Technical and usage data',
                                    'bullets' => [
                                        'IP address, browser type, device information, cookies, and similar analytics data.',
                                        'Browsing history, actions performed on the website, and areas of interest.',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'heading' => '3. Purposes of use',
                            'bullets' => [
                                'To process consultations, bookings, payments, and after-sales support.',
                                'To contact customers for confirmation, service updates, or issue resolution.',
                                'To work with relevant suppliers and partners such as airlines, hotels, tour operators, embassies, or consulates when required to deliver the service.',
                                'To improve website performance, service quality, and lawful marketing activities where the customer has agreed to receive communications.',
                            ],
                        ],
                        [
                            'heading' => '4. Basis and principles of processing',
                            'bullets' => [
                                'Only data necessary for a specific and legitimate purpose is collected.',
                                'Data is processed on the basis of customer consent, performance of a service contract, or compliance with legal obligations.',
                                'Travel Plus does not use personal data for unlawful purposes or beyond what is reasonably necessary.',
                            ],
                        ],
                        [
                            'heading' => '5. Information sharing',
                            'paragraphs' => [
                                'Travel Plus does not sell customer personal data. Information is shared only where necessary to deliver services or comply with legal requirements.',
                            ],
                            'bullets' => [
                                'With service providers or operating partners to fulfill a booking or request.',
                                'With competent authorities when legally required.',
                                'Where reasonably necessary to protect the lawful rights, assets, or interests of Travel Plus in disputes or risk management situations.',
                            ],
                        ],
                        [
                            'heading' => '6. Retention period',
                            'bullets' => [
                                'Data is retained for as long as necessary to fulfill the purpose of collection or as required by law.',
                                'When no longer needed, data will be deleted, destroyed, or anonymized through appropriate internal procedures.',
                            ],
                        ],
                        [
                            'heading' => '7. Data security',
                            'bullets' => [
                                'Travel Plus applies reasonable technical and organizational safeguards to reduce the risk of unauthorized access, loss, or disclosure.',
                                'Internal access is restricted according to role and operational need.',
                                'No internet-based transmission or storage system is completely secure, so customers should also take reasonable steps to protect their own information.',
                            ],
                        ],
                        [
                            'heading' => '8. Cookies and tracking technologies',
                            'bullets' => [
                                'The website may use cookies to maintain sessions, support sign-in, analyze usage, and improve the user experience.',
                                'Customers may adjust browser settings to reject or delete cookies, although some website functions may then be limited.',
                            ],
                        ],
                        [
                            'heading' => '9. Customer rights',
                            'bullets' => [
                                'Request access to, correction of, or updates to the personal data held by Travel Plus.',
                                'Request restriction, cessation of processing, or deletion of data where permitted by law.',
                                'Opt out of marketing communications at any time.',
                                'Contact Travel Plus with questions, complaints, or requests relating to personal data processing.',
                            ],
                        ],
                        [
                            'heading' => '10. Payments and sensitive data',
                            'bullets' => [
                                'Travel Plus does not intentionally store complete payment card information on the website.',
                                'Some transactions are processed via third-party gateways such as VNPay or PayPal and are subject to the policies of those providers.',
                                'Sensitive data such as passport, visa, or immigration-related information is processed only to the extent necessary to provide the requested service.',
                            ],
                        ],
                        [
                            'heading' => '11. Children’s data and third-party links',
                            'paragraphs' => [
                                'The website is not intended to knowingly collect personal data from children below the age defined by applicable law without the involvement of a lawful guardian.',
                                'The website may contain links to third-party sites or platforms. Travel Plus is not responsible for the content or privacy practices of those third parties.',
                            ],
                        ],
                        [
                            'heading' => '12. Policy updates and contact',
                            'paragraphs' => [
                                'Travel Plus may update this Privacy Statement from time to time. The latest version will be published on the website and takes effect from the time of publication.',
                            ],
                            'bullets' => [
                                'Uu The Du Lich One Member Co., Ltd. - Travel Plus',
                                'Ho Chi Minh Office: 3/30A Thich Quang Duc Street, Duc Nhuan Ward, Ho Chi Minh City',
                                'Hanoi Office: 47 Le Van Huu Street, Hai Ba Trung Ward, Hanoi',
                                'Da Nang Office: 4th Floor, Mien Trung Helicopter Building, Nguyen Van Linh Street, Hoa Cuong Ward, Da Nang',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
