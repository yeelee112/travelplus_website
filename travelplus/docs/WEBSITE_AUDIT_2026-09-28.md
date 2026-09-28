# Rà soát website Travel Plus — 28/09/2026

## Phạm vi và kết quả

Kiểm tra bản local tại http://localhost, không phải xác nhận trạng thái trên hosting. Không sửa mã nguồn ứng dụng trong lượt rà soát này.

- 27 URL chính: trang chủ VI/EN, tìm tour, danh mục tour, mùa thu, dịch vụ, giới thiệu, blog, liên hệ, tour theo yêu cầu, Reward, đăng nhập/đăng ký, tra cứu booking, pháp lý, đường dẫn quản trị và trang 404.
- Kiểm tra 70 liên kết nội bộ từ trang chủ: 3 liên kết trả 404.
- Mở thêm 4 tour mẫu: 3 tour trong nước và tour Mỹ.
- Desktop 1440px và mobile 375px: không ghi nhận lỗi JavaScript trong lượt quét trang chính, không tràn ngang toàn trang ở 375px. Không xem các carousel có cuộn ngang chủ động là lỗi.
- Tìm “Da Nang” có gợi ý “Đà Nẵng”; gửi tìm “Đà Nẵng” trả 2 tour trên dữ liệu local.
- Luồng chọn tour Hà Nội – Đà Nẵng → Đặt ngay → Tiếp tục với tư cách khách → checkout chạy được trên mobile, không tràn ngang. Dừng trước nhập thông tin và xác nhận/thanh toán; không tạo booking hoàn tất.
- PHPUnit: 118 tests, 473 assertions, đều đạt.
- Truy cập /admin/tours khi chưa đăng nhập chuyển về trang đăng nhập. Chưa kiểm thử thao tác lưu/sửa trong phiên quản trị; đã xem mã xử lý lưu tour, transaction, báo lỗi và bộ test liên quan.

## Việc cần làm theo ưu tiên

### 1. P1 — Sửa liên kết điểm đến đang dẫn tới 404

Các liên kết trong khối điểm đến trang chủ:
- /tour-trong-nuoc/mien-trung/nha-trang
- /tour-trong-nuoc/mien-nam/da-lat
- /tour-trong-nuoc/mien-nam/phu-quoc

Nguồn danh mục: app/Data/FeaturedDestinationCatalog.php, các mục quanh dòng 55–76. Danh sách được khai báo tĩnh nên không bảo đảm khớp điểm đến và slug trong dữ liệu hiện tại.

Đề xuất: lấy điểm đến, URL và số tour từ dữ liệu tour đã publish; chỉ hiển thị điểm đến phù hợp hoặc điều hướng rõ sang yêu cầu tư vấn. Áp dụng cho khối điểm đến trang chủ, không chỉ hộp hero search vốn đã dùng dữ liệu động.

### 2. P1 — Kiểm tra nội dung tour trước khi publish

Tour Mỹ /tour-nuoc-ngoai/bac-my/tham-quan-du-lich-my-hai-bo-dong-tay-13n1d2:
- Tiêu đề ghi 13N12Đ.
- Nhãn thời lượng ghi 11 ngày 10 đêm.
- Lịch trình hiện có 11 ngày.
- Phần bao gồm ghi “Chi phí visa châu Âu”.

Cần người phụ trách tour xác nhận nội dung đúng; không tự suy đoán sửa dữ liệu thương mại. Đề xuất cảnh báo trong quản trị khi số ngày lịch trình, thời lượng và tiêu đề không khớp; rà lại các hạng mục được copy từ tour khác.

### 3. P2 — Giảm tải ảnh phía trên trang chủ

Đo tài nguyên trong trình duyệt local:
- assets/images/home/banner00.png: 2.064.454 byte (~1,97 MiB).
- assets/images/landing/autumn/banner01.png: ~2,31 MB tải qua mạng.
- Hai ảnh này đã hơn 4 MB, chưa tính các tài nguyên khác.
- app/Views/layouts/main.php:219 preload banner01.webp và biến thể responsive, nhưng ảnh đầu thực tế ở app/Views/sections/hero-search.php:18 là banner00.png. Trình duyệt đang ưu tiên thêm một ảnh chưa xuất hiện đầu tiên.
- Các banner đã có biến thể WebP 768/1280/1600 nhưng hero hiện chỉ xuất một nguồn ảnh cho mỗi slide.

Đề xuất: tối ưu banner00 và banner mùa thu sang WebP/AVIF, dùng srcset theo kích thước màn hình, và preload đúng ảnh đầu. Không dùng thời gian tải local để kết luận tốc độ hosting hoặc điểm Lighthouse.

### 4. P2 — Hoàn thiện thao tác bằng bàn phím

- app/Views/partials/header.php:347 và :597 dùng div cho nút đóng/mở menu mobile, không có tabindex hay ngữ nghĩa button.
- public/assets/js/main.js:369 xử lý click nhưng chưa quản lý focus và Escape cho menu mobile.
- app/Views/layouts/main.php:265 đưa nội dung trang ra trực tiếp; bố cục công khai thiếu landmark main chung và liên kết bỏ qua điều hướng.
- Hero có cơ chế tôn trọng prefers-reduced-motion, nhưng chưa có điều khiển tạm dừng slideshow cho người dùng thông thường.

Đề xuất: dùng button có nhãn, aria-expanded/aria-controls, quản lý focus khi mở/đóng; thêm main/skip link và nút dừng/chạy banner nhỏ gọn.

### 5. P3 — Thu gọn thứ tự nội dung trang chủ mobile

Đây là đề xuất thiết kế, không phải lỗi chức năng. Trang chủ hiện nối tiếp nhiều khối: hero → mùa thu → cam kết → khuyến mãi → Reward → tour nổi bật → tour riêng → MICE → điểm đến → blog → đánh giá → thống kê → gallery → liên hệ/footer.

Nên ưu tiên tìm tour, tour đang bán và lịch khởi hành; gộp bớt khối quảng bá phụ. Chuẩn hóa kích thước chữ phụ, icon và nút theo bộ quy tắc chung để tránh phải chỉnh từng chỗ rồi lệch nhau.

## Các bước còn cần xác nhận riêng

- Kiểm thử quản trị với phiên đăng nhập được cung cấp: tạo/sửa/publish tour, sắp xếp bao gồm/không bao gồm, tìm kiếm realtime, bộ sưu tập và upload.
- Thanh toán, webhook, email và OTP cần môi trường thử nghiệm tương ứng; lượt audit này chưa xác nhận tích hợp đầu cuối.
- Kiểm tra hosting thật: cấu hình production, cache, minified assets, tốc độ mạng, sitemap và canonical theo domain thật. Debug toolbar xuất hiện trên local là đặc điểm môi trường phát triển, không phải bằng chứng hosting đang bật debug.

## Bằng chứng

- writable/site-audit-results.json: kết quả 27 URL.
- writable/site-audit-flows.json: tài nguyên trang chủ, tìm kiếm và tour trong nước.
- writable/site-audit-links.json: trạng thái 70 liên kết và nội dung tour Mỹ.
- writable/site-audit-booking.json: checkout khách.
- writable/audit-fresh-mobile.png, writable/audit-us-tour-desktop.png, writable/audit-checkout-mobile.png: ảnh chụp giao diện.


## Cập nhật thực hiện theo yêu cầu

Chỉ triển khai mục 4 (P2) và 5 (P3). Các mục 1–3 không chỉnh trong lượt này theo xác nhận của chủ website.

- Menu mobile dùng button có nhãn, aria-controls/expanded; hỗ trợ Enter, Escape, giữ focus trong menu và trả focus khi đóng. Menu đóng được loại khỏi thứ tự Tab bằng inert; đổi sang desktop sẽ khôi phục điều hướng.
- Các nút mở menu con dùng được bằng Enter/Space và có trạng thái aria-expanded.
- Thêm skip link và điểm nhận focus cho nội dung. Trang đã có main được giữ nguyên để không tạo main lồng nhau.
- Banner có nút dừng/chạy, tôn trọng reduced motion và ngừng timer khi tab không hiển thị.
- Trang chủ đưa tour nổi bật lên trước khuyến mãi; bỏ banner giới thiệu lặp lại; gộp tour riêng/MICE/Reward thành 3 thẻ ngắn; đưa số liệu và gallery vào details mở rộng; phần cam kết dùng 2 cột trên mobile.
- Kiểm thử trình duyệt VI/EN, 320/375/768/1024/1440px, focus, menu con, pause/resume, reduced motion, details và landmark; không tràn ngang. PHPUnit vẫn đạt 118 tests / 473 assertions.
