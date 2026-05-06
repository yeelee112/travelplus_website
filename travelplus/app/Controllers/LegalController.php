<?php

namespace App\Controllers;

class LegalController extends BaseController
{
    public function terms(string $locale = 'vi')
    {
        return view('legal/page', $this->buildPageData('terms', $locale));
    }

    public function privacy(string $locale = 'vi')
    {
        return view('legal/page', $this->buildPageData('privacy', $locale));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPageData(string $type, string $locale): array
    {
        $pages = [
            'terms' => [
                'title' => $locale === 'en' ? 'Terms of Service' : 'Điều Khoản Sử Dụng',
                'subtitle' => 'Website: travelplusvn.com - Đơn vị vận hành: Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                'updated_at' => '10/04/2026',
                'sections' => [
                    [
                        'heading' => '1. Giới thiệu và phạm vi áp dụng',
                        'paragraphs' => [
                            'Chào mừng Quý khách đến với website travelplusvn.com, được sở hữu và vận hành bởi Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus.',
                            'Điều khoản sử dụng này áp dụng đối với việc truy cập, sử dụng Website và các dịch vụ do Travel Plus cung cấp, bao gồm nhưng không giới hạn: tour du lịch trong nước và quốc tế, dịch vụ visa, vé máy bay, khách sạn, dịch vụ MICE, dịch thuật, vận chuyển và các dịch vụ liên quan.',
                            'Bằng việc truy cập hoặc sử dụng Website, Quý khách xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi toàn bộ nội dung của Điều khoản này.',
                        ],
                    ],
                    [
                        'heading' => '2. Định nghĩa',
                        'bullets' => [
                            '“Khách hàng” là cá nhân hoặc tổ chức truy cập hoặc sử dụng dịch vụ.',
                            '“Dịch vụ” là các sản phẩm do Travel Plus cung cấp.',
                            '“Đơn hàng” là yêu cầu đặt dịch vụ của khách hàng.',
                            '“Đối tác” là bên thứ ba cung cấp dịch vụ như hãng bay, khách sạn, lãnh sự quán.',
                        ],
                    ],
                    [
                        'heading' => '3. Điều kiện sử dụng Website',
                        'bullets' => [
                            'Sử dụng Website đúng mục đích hợp pháp.',
                            'Không sử dụng để gian lận, lừa đảo hoặc gây thiệt hại.',
                            'Không can thiệp trái phép vào hệ thống, dữ liệu hoặc bảo mật.',
                            'Không sử dụng thông tin trên Website cho mục đích thương mại khi chưa được phép.',
                            'Travel Plus có quyền từ chối truy cập hoặc ngừng cung cấp dịch vụ nếu phát hiện vi phạm.',
                        ],
                    ],
                    [
                        'heading' => '4. Tài khoản người dùng',
                        'subsections' => [
                            [
                                'heading' => '4.1. Đăng ký tài khoản',
                                'bullets' => [
                                    'Khách hàng cần cung cấp thông tin chính xác: họ tên, email, số điện thoại.',
                                ],
                            ],
                            [
                                'heading' => '4.2. Bảo mật',
                                'bullets' => [
                                    'Khách hàng tự chịu trách nhiệm bảo mật tài khoản.',
                                    'Mọi hoạt động phát sinh từ tài khoản được xem là do chủ tài khoản thực hiện.',
                                ],
                            ],
                            [
                                'heading' => '4.3. Tạm khóa hoặc chấm dứt',
                                'bullets' => [
                                    'Travel Plus có quyền khóa tài khoản nếu cung cấp thông tin sai lệch, có dấu hiệu gian lận hoặc vi phạm điều khoản sử dụng.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'heading' => '5. Nội dung và thông tin trên Website',
                        'bullets' => [
                            'Thông tin về tour, giá cả, lịch trình có thể thay đổi tùy thời điểm.',
                            'Hình ảnh trên Website mang tính minh họa.',
                            'Travel Plus không đảm bảo mọi thông tin luôn chính xác tuyệt đối tại mọi thời điểm.',
                            'Chúng tôi có quyền chỉnh sửa nội dung mà không cần thông báo trước.',
                        ],
                    ],
                    [
                        'heading' => '6. Quy trình đặt dịch vụ',
                        'bullets' => [
                            'Khách hàng lựa chọn dịch vụ và gửi yêu cầu đặt dịch vụ.',
                            'Travel Plus xác nhận tình trạng chỗ và hướng dẫn thanh toán.',
                            'Đơn hàng chỉ có hiệu lực khi được xác nhận thanh toán.',
                            'Travel Plus có quyền từ chối đơn hàng trong một số trường hợp phù hợp.',
                        ],
                    ],
                    [
                        'heading' => '7. Giá cả và thanh toán',
                        'subsections' => [
                            [
                                'heading' => '7.1. Giá dịch vụ',
                                'bullets' => [
                                    'Giá có thể thay đổi tùy thời điểm và tình trạng chỗ.',
                                    'Giá có thể chưa bao gồm các chi phí phát sinh.',
                                ],
                            ],
                            [
                                'heading' => '7.2. Phương thức thanh toán',
                                'bullets' => [
                                    'Chuyển khoản ngân hàng.',
                                    'Tiền mặt.',
                                    'VNPay.',
                                    'PayPal.',
                                    'Momo.',
                                ],
                            ],
                            [
                                'heading' => '7.3. Điều kiện thanh toán',
                                'bullets' => [
                                    'Khách hàng phải thanh toán đúng hạn và có thể phải đặt cọc theo quy định của từng dịch vụ.',
                                ],
                            ],
                            [
                                'heading' => '7.4. Phí phát sinh',
                                'bullets' => [
                                    'Khách hàng chịu các chi phí như phí ngân hàng và phí chuyển đổi ngoại tệ (nếu có).',
                                ],
                            ],
                        ],
                    ],
                    [
                        'heading' => '8. Thay đổi, hủy dịch vụ',
                        'bullets' => [
                            'Các thay đổi hoặc hủy dịch vụ từ phía khách hàng áp dụng theo chính sách hủy hoặc hoàn tiền riêng.',
                            'Travel Plus có quyền thay đổi lịch trình hoặc điều chỉnh dịch vụ tương đương trong trường hợp cần thiết để đảm bảo an toàn hoặc do yếu tố khách quan.',
                        ],
                    ],
                    [
                        'heading' => '9. Dịch vụ visa',
                        'bullets' => [
                            'Travel Plus đóng vai trò tư vấn và hỗ trợ hồ sơ.',
                            'Quyết định cấp visa thuộc về cơ quan lãnh sự.',
                            'Travel Plus không cam kết tỷ lệ đậu visa.',
                            'Phí visa thường không hoàn lại.',
                            'Khách hàng chịu trách nhiệm về tính chính xác của hồ sơ cung cấp.',
                        ],
                    ],
                    [
                        'heading' => '10. Quyền và trách nhiệm của khách hàng',
                        'bullets' => [
                            'Cung cấp thông tin chính xác.',
                            'Thanh toán đúng hạn.',
                            'Tuân thủ pháp luật và quy định của điểm đến.',
                            'Khách hàng tự chịu trách nhiệm nếu bị từ chối visa, xuất nhập cảnh hoặc vi phạm pháp luật.',
                        ],
                    ],
                    [
                        'heading' => '11. Quyền và trách nhiệm của Travel Plus',
                        'bullets' => [
                            'Cung cấp dịch vụ đúng mô tả.',
                            'Hỗ trợ khách hàng trong quá trình sử dụng dịch vụ.',
                            'Có quyền từ chối phục vụ hoặc điều chỉnh dịch vụ khi cần thiết nếu khách hàng vi phạm.',
                        ],
                    ],
                    [
                        'heading' => '12. Giới hạn trách nhiệm',
                        'bullets' => [
                            'Travel Plus không chịu trách nhiệm đối với sự chậm trễ của hãng vận chuyển, thay đổi chính sách nhập cảnh, thiên tai, dịch bệnh, chiến tranh hoặc mất mát tài sản cá nhân nằm ngoài khả năng kiểm soát hợp lý.',
                        ],
                    ],
                    [
                        'heading' => '13. Sở hữu trí tuệ',
                        'bullets' => [
                            'Toàn bộ nội dung trên Website thuộc quyền sở hữu của Travel Plus.',
                            'Nghiêm cấm sao chép hoặc sử dụng khi chưa được cho phép.',
                        ],
                    ],
                    [
                        'heading' => '14. Bảo mật thông tin',
                        'paragraphs' => [
                            'Travel Plus thu thập và sử dụng thông tin như email, số điện thoại để phục vụ dịch vụ và không chia sẻ trái phép. Chi tiết áp dụng theo Chính sách bảo mật riêng của Website.',
                        ],
                    ],
                    [
                        'heading' => '15. Liên kết bên thứ ba',
                        'paragraphs' => [
                            'Website có thể chứa liên kết đến bên thứ ba. Travel Plus không chịu trách nhiệm về nội dung hoặc chính sách của các bên này.',
                        ],
                    ],
                    [
                        'heading' => '16. Chấm dứt sử dụng',
                        'paragraphs' => [
                            'Travel Plus có quyền ngừng cung cấp dịch vụ hoặc hủy tài khoản nếu khách hàng vi phạm điều khoản.',
                        ],
                    ],
                    [
                        'heading' => '17. Luật áp dụng và giải quyết tranh chấp',
                        'paragraphs' => [
                            'Điều khoản này được điều chỉnh theo pháp luật Việt Nam. Tranh chấp phát sinh sẽ được giải quyết tại tòa án có thẩm quyền.',
                        ],
                    ],
                    [
                        'heading' => '18. Thông tin liên hệ',
                        'bullets' => [
                            'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                            'VP HCM: 3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP. HCM',
                            'VP Hà Nội: 47 đường Lê Văn Hưu, Phường Hai Bà Trưng, TP. Hà Nội',
                            'VP Đà Nẵng: Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, TP. Đà Nẵng',
                        ],
                    ],
                    [
                        'heading' => '19. Điều khoản cuối',
                        'paragraphs' => [
                            'Điều khoản này có hiệu lực kể từ ngày đăng tải. Travel Plus có quyền thay đổi nội dung mà không cần thông báo trước. Việc tiếp tục sử dụng Website đồng nghĩa với việc chấp nhận các thay đổi đó.',
                        ],
                    ],
                ],
            ],
            'privacy' => [
                'title' => $locale === 'en' ? 'Privacy Statement' : 'Chính Sách Bảo Mật',
                'subtitle' => 'Website: travelplusvn.com - Đơn vị vận hành: Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                'updated_at' => '10/04/2026',
                'sections' => [
                    [
                        'heading' => '1. Giới thiệu',
                        'paragraphs' => [
                            'Travel Plus cam kết tôn trọng và bảo vệ quyền riêng tư của khách hàng khi truy cập và sử dụng website travelplusvn.com.',
                            'Chính sách này giải thích cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin cá nhân của khách hàng.',
                            'Bằng việc sử dụng Website, Quý khách đồng ý với các nội dung được mô tả trong Chính sách này.',
                        ],
                    ],
                    [
                        'heading' => '2. Phạm vi áp dụng',
                        'bullets' => [
                            'Người truy cập Website.',
                            'Khách hàng sử dụng dịch vụ của Travel Plus.',
                            'Người đăng ký tài khoản hoặc để lại thông tin tư vấn.',
                        ],
                    ],
                    [
                        'heading' => '3. Loại thông tin thu thập',
                        'subsections' => [
                            [
                                'heading' => '3.1. Thông tin cá nhân',
                                'bullets' => [
                                    'Họ và tên.',
                                    'Email.',
                                    'Số điện thoại.',
                                    'Địa chỉ liên hệ.',
                                ],
                            ],
                            [
                                'heading' => '3.2. Thông tin nhạy cảm khi cần thiết',
                                'bullets' => [
                                    'Số hộ chiếu.',
                                    'Ngày sinh.',
                                    'Quốc tịch.',
                                    'Thông tin hành trình.',
                                ],
                            ],
                            [
                                'heading' => '3.3. Thông tin tự động',
                                'bullets' => [
                                    'Địa chỉ IP.',
                                    'Loại trình duyệt.',
                                    'Thiết bị truy cập.',
                                    'Lịch sử truy cập và hành vi sử dụng.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'heading' => '4. Mục đích thu thập thông tin',
                        'bullets' => [
                            'Xác nhận và xử lý đơn hàng.',
                            'Tư vấn dịch vụ phù hợp.',
                            'Liên hệ và hỗ trợ khách hàng.',
                            'Cung cấp thông tin về chương trình khuyến mãi nếu khách hàng đồng ý.',
                            'Cải thiện chất lượng Website và dịch vụ.',
                        ],
                    ],
                    [
                        'heading' => '5. Nguyên tắc thu thập',
                        'bullets' => [
                            'Chỉ thu thập thông tin cần thiết.',
                            'Thu thập minh bạch và có sự đồng ý của khách hàng.',
                            'Không sử dụng thông tin cho mục đích trái pháp luật.',
                        ],
                    ],
                    [
                        'heading' => '6. Chia sẻ thông tin',
                        'subsections' => [
                            [
                                'heading' => '6.1. Đối tác cung cấp dịch vụ',
                                'bullets' => [
                                    'Thông tin có thể được chia sẻ với hãng hàng không, khách sạn, đại sứ quán, lãnh sự quán hoặc đối tác tổ chức tour nhằm phục vụ việc cung cấp dịch vụ.',
                                ],
                            ],
                            [
                                'heading' => '6.2. Yêu cầu pháp lý',
                                'bullets' => [
                                    'Thông tin có thể được cung cấp khi có yêu cầu từ cơ quan nhà nước có thẩm quyền.',
                                ],
                            ],
                            [
                                'heading' => '6.3. Trường hợp cần thiết',
                                'bullets' => [
                                    'Để bảo vệ quyền và lợi ích hợp pháp của Travel Plus trong các tranh chấp.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'heading' => '7. Thời gian lưu trữ thông tin',
                        'bullets' => [
                            'Thông tin được lưu trữ trong thời gian cần thiết để phục vụ mục đích đã nêu.',
                            'Có thể lưu trữ lâu hơn theo yêu cầu pháp luật.',
                            'Khi không còn cần thiết, dữ liệu sẽ được xóa hoặc ẩn danh.',
                        ],
                    ],
                    [
                        'heading' => '8. Bảo mật thông tin',
                        'bullets' => [
                            'Giới hạn quyền truy cập nội bộ.',
                            'Sử dụng hệ thống bảo mật và mã hóa.',
                            'Bảo vệ dữ liệu khỏi truy cập trái phép.',
                            'Tuy nhiên không có hệ thống nào đảm bảo an toàn tuyệt đối trên Internet; khách hàng cần tự bảo vệ thông tin cá nhân của mình.',
                        ],
                    ],
                    [
                        'heading' => '9. Quyền của khách hàng',
                        'bullets' => [
                            'Yêu cầu xem, chỉnh sửa hoặc cập nhật thông tin cá nhân.',
                            'Yêu cầu xóa thông tin khi không còn sử dụng dịch vụ.',
                            'Từ chối nhận thông tin quảng cáo.',
                            'Khiếu nại về việc sử dụng dữ liệu.',
                        ],
                    ],
                    [
                        'heading' => '10. Cookies và công nghệ theo dõi',
                        'bullets' => [
                            'Ghi nhớ thông tin đăng nhập.',
                            'Phân tích hành vi người dùng.',
                            'Cá nhân hóa trải nghiệm.',
                            'Khách hàng có thể tắt cookies trong trình duyệt, nhưng điều này có thể ảnh hưởng đến trải nghiệm sử dụng.',
                        ],
                    ],
                    [
                        'heading' => '11. Bảo mật thanh toán',
                        'bullets' => [
                            'Travel Plus không lưu trữ thông tin thẻ thanh toán.',
                            'Các giao dịch được xử lý qua cổng thanh toán trung gian như VNPay hoặc PayPal.',
                            'Áp dụng các tiêu chuẩn bảo mật của đối tác thanh toán.',
                        ],
                    ],
                    [
                        'heading' => '12. Dữ liệu trẻ em',
                        'paragraphs' => [
                            'Website không nhằm mục đích thu thập thông tin từ trẻ em dưới 16 tuổi. Nếu phát hiện có dữ liệu thu thập ngoài ý muốn, chúng tôi sẽ tiến hành xóa.',
                        ],
                    ],
                    [
                        'heading' => '13. Liên kết bên thứ ba',
                        'paragraphs' => [
                            'Website có thể chứa liên kết đến các website khác. Travel Plus không chịu trách nhiệm đối với nội dung và chính sách bảo mật của các website này.',
                        ],
                    ],
                    [
                        'heading' => '14. Chuyển dữ liệu quốc tế',
                        'paragraphs' => [
                            'Trong một số trường hợp như đặt tour quốc tế hoặc làm visa, thông tin có thể được chuyển ra ngoài lãnh thổ Việt Nam để phục vụ dịch vụ. Travel Plus đảm bảo việc chuyển dữ liệu được thực hiện phù hợp với quy định pháp luật.',
                        ],
                    ],
                    [
                        'heading' => '15. Sửa đổi chính sách',
                        'paragraphs' => [
                            'Travel Plus có quyền cập nhật Chính sách bảo mật bất kỳ lúc nào. Phiên bản mới sẽ được đăng tải trên Website. Việc tiếp tục sử dụng dịch vụ đồng nghĩa với việc khách hàng đồng ý với các thay đổi đó.',
                        ],
                    ],
                    [
                        'heading' => '16. Thông tin liên hệ',
                        'bullets' => [
                            'Công ty TNHH MTV Ưu Thế Du Lịch - Travel Plus',
                            'VP HCM: 3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP. HCM',
                            'VP Hà Nội: 47 đường Lê Văn Hưu, Phường Hai Bà Trưng, TP. Hà Nội',
                            'VP Đà Nẵng: Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, TP. Đà Nẵng',
                        ],
                    ],
                    [
                        'heading' => '17. Hiệu lực',
                        'paragraphs' => [
                            'Chính sách này có hiệu lực kể từ ngày đăng tải trên Website.',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'page' => $pages[$type],
            'pageType' => $type,
            'locale' => $locale,
        ];
    }
}
