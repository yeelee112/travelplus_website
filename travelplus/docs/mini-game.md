# Mini game Đoán Biển Số

## Đường dẫn

- MC: `/mini-game/admin`
- Người chơi: `/mini-game/player`
- Màn hình chia sẻ Teams: `/mini-game/screen`
- Quản lý câu hỏi: `/mini-game/questions`

MC mở màn hình trình chiếu ở một tab riêng và share tab đó qua Microsoft Teams. Player giữ nguyên một trình duyệt trong suốt buổi chơi; mã phiên chỉ được lưu trong `localStorage`, không cần tài khoản.

Để điện thoại trong cùng mạng truy cập được, đặt `app.baseURL` trong `.env` thành domain nội bộ hoặc IP LAN của máy chủ (không để `localhost`) và mở cổng HTTP/HTTPS tương ứng trên firewall.

## Cài đặt

```powershell
php spark migrate --all
php spark db:seed MiniGameSeeder
```

Nếu PHP mặc định trên máy thiếu `mbstring`/`mysqli`, dùng PHP đi kèm Laragon.

## Realtime và tải hệ thống

Các màn hình đọc một snapshot nhỏ mỗi giây. Khi bấm giành quyền, server khóa duy nhất dòng `game_state` trong một transaction, cấp `buzz_order`, rồi mới trả kết quả. Nhờ vậy hai lượt bấm sát nhau vẫn có thứ tự xác định mà không cần Redis, queue hay một realtime server riêng.

## Dọn dẹp sau game

Chạy `database/sql/2026-07-29_drop_mini_game.sql`, sau đó có thể xóa các file `MiniGame*`, thư mục view `mini-game` và hai asset `mini-game.css`/`mini-game.js`. Các bảng đều có tiền tố `game_`, không liên quan bảng nghiệp vụ Travel Plus.

## Lưu ý vận hành

Các URL MC và quản lý câu hỏi dành cho mạng nội bộ. Trước khi đưa ra Internet, cần đặt chúng sau đăng nhập admin hoặc một access PIN. Export hiện dùng CSV UTF-8, mở trực tiếp được trong Excel.
