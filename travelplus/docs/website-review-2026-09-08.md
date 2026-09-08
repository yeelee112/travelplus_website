# Rà soát website Travel Plus — 08/09/2026

## Phạm vi và quyết định
- Giữ điểm dự kiến theo giá gốc tour, trước giảm hạng/voucher. Chỉ làm rõ tooltip; không thay đổi cơ chế ghi nhận điểm thực tế.
- Xử lý trải nghiệm mobile, lỗi thông báo Reward, phạm vi CSS và đo lường.
- Giữ nguyên các thay đổi .htaccess có sẵn của người dùng.

## Thay đổi
- Một nút hỗ trợ trên mobile; menu có gọi, Messenger, Zalo và AI. Đóng chat trả focus về nút hỗ trợ; Escape đóng panel. Ẩn nút lên đầu trang trên mobile.
- Nút hỗ trợ nằm trên thanh đặt tour; ẩn các lớp nổi khi mở modal. Chat sử dụng chiều cao viewport động và trường nhập 16px.
- Chặn gửi form chọn tour lặp khi request chưa xong; trả nút về trạng thái dùng được sau lỗi.
- Checkout chuyển về bước thông tin và focus ô sai; lỗi chung có role alert, được cuộn vào tầm nhìn.
- Thông báo voucher đổi Passport thành Reward, cả Việt/Anh. Giữ tên lớp/mã dữ liệu và thuật ngữ hộ chiếu trong nội dung visa.
- CSS nhãn và giá Reward chuyển thành bundle style-reward, chỉ tải khi trang có component. Các selector giá cũ chỉ tác động span con trực tiếp. Bundles được sinh lại từ style.css bằng script hiện có, tránh sửa riêng file đã build.
- Thêm sự kiện booking_proceed, booking_proceed_error (reason dạng mã), booking_validation_error (stage dạng mã) vào cơ chế analytics hiện có, không thêm dữ liệu liên hệ của khách.
- Thêm site_performance: page_type, device_layout, ttfb_ms, dom_ready_ms, lcp_ms, cls. Chỉ gửi khi đồng ý analytics, tối đa một lần mỗi document. Đây chưa phải phép đo INP hoặc báo cáo Core Web Vitals ngoài thực tế.

## Kiểm tra đã thực hiện
- 86 unit tests PHP, 365 assertions: đạt. Cảnh báo duy nhất: môi trường không có bộ đo code coverage.
- 2 kiểm thử JS: không gửi khi chưa đồng ý analytics; chỉ gửi một lần và tính CLS theo cửa sổ đúng.
- PHP lint và JS syntax/build: đạt.
- Trình duyệt mobile 390x844: menu hỗ trợ mở/đóng, AI mở từ menu, không tràn ngang.
- Chọn tour Tây Âu giá gốc 160 triệu: 2 người lớn = 320 triệu; thêm 1 trẻ em = 456 triệu. Tổng checkout 456 triệu, cọc 10% = 45,6 triệu.
- Tiếp tục với tư cách khách; dùng dữ liệu kiểm thử, kiểm tra trường bắt buộc, chọn tỉnh/phường, sang bước thanh toán, voucher không tồn tại, quay lại sửa thông tin vẫn giữ tên đã nhập.
- 320x640: checkout và trang Reward không tràn ngang.
- Không thực hiện giao dịch thanh toán, gửi tư vấn hay tin nhắn AI trong lần rà này.

## Đo HTTP tại localhost
Ba lần GET mỗi URL; bảng ghi trung vị thời gian tới response headers. Không mô phỏng mạng di động; không dùng số này làm kết quả production.

| Trang | HTTP | Trung vị (ms) | Dung lượng HTML (bytes) |
|---|---:|---:|---:|
| `/` | 200 | 368 | 213818 |
| `/tim-kiem-tour` | 200 | 396 | 164557 |
| `/travelplus-reward` | 200 | 296 | 108763 |
| `/tour-nuoc-ngoai/chau-au/kham-pha-tay-au-phap-thuy-si-y-10n9d` | 200 | 473 | 270287 |
| `/booking/lookup` | 200 | 313 | 85370 |
| `/en/travelplus-reward` | 200 | 282 | 107042 |

Các response localhost hiện không nén (Content-Encoding identity). Cần kiểm tra gzip/Brotli trên hosting trước khi tối ưu cấu hình máy chủ; không thay đổi .htaccess đang được người dùng chỉnh.

## Sau khi đưa bản này lên hosting
- Xem các sự kiện đã có: view_item → booking_proceed → begin_checkout → add_payment_info → purchase. Các mã lỗi mới giúp phân biệt lỗi nhập liệu và lỗi gửi booking. Không suy ra tỷ lệ bỏ dở khi chưa có dữ liệu người dùng thực tế.
- Trong GA4 khai báo các custom metric tương ứng site_performance nếu muốn tổng hợp số đo; đối chiếu mobile/desktop theo loại trang. Tránh so sánh mẫu ít với số liệu trên điện thoại thật.
- Kiểm tra thanh toán end-to-end bằng tài khoản/cổng sandbox được cấu hình cho staging. Lần này chỉ kiểm tra tới trước thao tác trả tiền.
