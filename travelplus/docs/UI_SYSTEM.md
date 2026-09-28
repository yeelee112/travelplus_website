# Quy tắc UI dùng chung

Nguồn duy nhất: `public/assets/css/ui-system.css`.

| Vai trò | Quy tắc |
| --- | --- |
| Chữ phụ, metadata, ghi chú, quyền lợi thành viên | 12px, line-height 1.5 |
| Nhãn form và chữ nút | 14px, line-height 1.4 |
| Icon bên chữ phụ | 14px, hộp 1em, canh giữa |
| Icon trong nút | 16px, hộp 1em, canh giữa |
| Nút thường | tối thiểu 44px, padding ngang 16px, gap 8px |
| Nút gọn trong card/liên kết hành động | tối thiểu 40px |
| Nút chỉ có icon trên thiết bị cảm ứng | tối thiểu 44 × 44px |
| Focus nút bằng bàn phím | outline 2px, offset 3px |

Giữ kích thước chữ phụ nhất quán giữa mobile và desktop. Nút tìm kiếm hero cao 60px trên desktop để khớp ô nhập có hai dòng; trên mobile dùng mức nút thường. Headings, giá tiền, logo và icon trang trí có thang riêng, không bị ép về cỡ icon thao tác.

## Áp dụng

Layout khách hàng thêm class `tp-ui` và tải file này sau các bundle theo trang. Các selector trong file ánh xạ các component hiện tại (card tour, tìm kiếm, mùa thu, bài viết, tài khoản, liên hệ, checkout) vào cùng biến CSS. Quản trị dùng cùng file qua import ở đầu `admin.css`, áp dụng cho `.admin-shell`.

Component mới dùng `.tp-ui-caption`, `.tp-ui-label`, `.tp-ui-button`, `.tp-ui-button--compact`, `.tp-ui-icon`, hoặc bổ sung selector vào đúng nhóm trong file. Không tạo thêm giá trị 10/11/13px cho cùng vai trò trong stylesheet riêng. Không áp quy tắc icon nhỏ cho icon minh họa lớn.

Màu sắc và hình dáng đặc trưng của mùa thu, Reward và các CTA vẫn do component quản lý. Tránh override toàn bộ `small`, `i`, `svg` hoặc tất cả button vì có icon trang trí, lịch và nút đóng riêng.

## Build

`php scripts/build-frontend-assets.php` đã bao gồm `ui-system.min.css` và `admin.min.css`. Khi chỉ chỉnh hệ thống UI có thể chạy:

```sh
npx --yes esbuild@0.25.6 public/assets/css/ui-system.css --minify --outfile=public/assets/css/ui-system.min.css
```

Khi publish phải upload cả file nguồn UI dùng qua import quản trị và file minified cho giao diện khách hàng.

## Kiểm tra

Script local: `node writable/ui-system-check.cjs`. Kiểm tra kích thước chữ thực tế và tràn ngang trên 11 trang ở 320/375/768/1440px. Luồng checkout được kiểm tra riêng bằng `writable/site-audit-booking.cjs`. Đã đăng nhập kiểm tra 9 trang quản trị và form sửa tour trên local ở 375/768/1440px. Đã sửa nút đầu trang bị bó hẹp trên mobile; bổ sung phạm vi CSS cho Bộ sưu tập và Cấu hình website. Kiểm tra giao diện, không gửi form thay đổi dữ liệu.
