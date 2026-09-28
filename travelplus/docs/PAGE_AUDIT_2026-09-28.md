# Kiểm tra font và bố cục các trang — 28/09/2026

## Phạm vi

- 93 URL từ route thực tế và liên kết có sẵn: giao diện khách hàng VI/EN, quản trị đã đăng nhập, mẫu trang chi tiết và form chỉnh sửa.
- Kiểm tra font tính toán, tải Google Sans, lỗi JavaScript, trạng thái HTTP và tràn ngang ở 375/768/1440px.
- Trang Inbound dùng Georgia cho các tiêu đề theo thiết kế riêng; đây không phải lỗi font dự phòng. Nội dung quản trị và các trang thông thường dùng Google Sans.
- Trang 403 được kiểm tra tải font lại sau khi chờ `document.fonts.ready`: Google Sans tải thành công. Mã 403/404 tại các trang lỗi tương ứng là đúng dự kiến.

## Các lỗi đã sửa

1. Khách tiềm năng: bộ lọc chuyển thành hai cột ở tablet, tránh tràn ngang.
2. Kiểm tra media: cho phép cột grid và panel co lại đúng chiều rộng màn hình nhỏ.
3. Form sửa tour: thanh thao tác cho phép xuống dòng, tránh nút lưu tràn khỏi màn hình.

Cả ba trang đã kiểm tra lại ở 320/375/768/1440px, không còn tràn ngang. Ba file PHP qua kiểm tra cú pháp; `git diff --check` không có lỗi khoảng trắng. Không ghi nhận lỗi JavaScript trong lượt quét.

## Tái kiểm tra

- Script: `writable/all-pages-font-audit.cjs` (nhận thông tin đăng nhập lúc chạy, không lưu mật khẩu).
- Kết quả: `writable/all-pages-font-audit.json`.
- Kết quả kiểm tra lại sau sửa: `writable/all-pages-followup.json`.

## Giới hạn

Đây là kiểm tra các màn hình có thể truy cập và mẫu bản ghi, không phải duyệt mọi bản ghi hay xác nhận mọi trạng thái tương tác. Không gửi form lưu dữ liệu, đặt tour, thanh toán, kết nối OAuth hoặc các tác vụ thay đổi dữ liệu. Các luồng cần token và trạng thái tài khoản riêng chưa được kiểm chứng đầy đủ.
