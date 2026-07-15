# Travel Plus Website

Website du lich song ngu VI/EN duoc xay dung tren CodeIgniter 4, gom website khach hang, booking, thanh toan, CRM lead, AI chat va khu vuc quan tri.

## Yeu cau

- PHP 8.1 tro len; khuyen nghi PHP 8.3.
- MySQL/MariaDB.
- PHP extensions: `intl`, `mbstring`, `mysqli`, `curl`, `json`, `dom`, `xml`, `fileinfo` va `gd`.
- Web root nen tro vao thu muc `public/`.

## Cau truc chinh

- `app/`: controller, model, service, view va cau hinh ung dung.
- `public/`: front controller, CSS/JS, anh va file upload cong khai.
- `database/`: migration va seed du lieu.
- `writable/`: cache, session, log va thong ke runtime; web server phai co quyen ghi.
- `tests/`: unit test dung o local/CI, khong can publish len hosting.
- `scripts/`: cong cu build CSS va toi uu asset dung o local.

## Chay local

1. Tao `.env` tu cau hinh mau va dien `app.baseURL`, database, email, thanh toan va cac API key can thiet.
2. Cau hinh virtual host tro vao `public/`.
3. Import database hoac chay migration trong moi truong local.
4. Mo website va kiem tra ca URL VI va `/en`.

Lenh tuy chon chi dung o local:

```bash
php vendor/bin/phpunit --no-coverage
php scripts/minify-css.php
```

## Publish shared hosting khong co command

1. Upload code da co san thu muc `vendor/`; khong can upload `tests/`, `build/` va log local.
2. Upload ca file an `.htaccess` o root va `public/.htaccess`.
3. Giu `.env` rieng tren hosting, khong ghi de bang file local.
4. Import cac file SQL can thiet bang phpMyAdmin thay cho `php spark migrate`.
5. Dam bao `writable/` co quyen ghi; khong ghi de counter/log runtime dang co tren hosting.
6. Neu domain dang tro vao root du an thay vi `public/`, giu nguyen rule rewrite root va kiem tra URL khong bi chen `/public/`.

Sau khi publish, kiem tra: trang chu VI/EN, booking lookup, form lien he, email, thanh toan, `/sitemap.xml`, `/robots.txt`, mot URL 404 va mot thu muc bi chan 403.

## Bao mat

- Khong commit hoac chia se `.env`, API key, mat khau database va SMTP.
- Khong bat directory listing cho `assets/`, `uploads/` hoac `writable/`.
- File PDF cong khai duoc truy cap bang duong dan day du; truy cap truc tiep thu muc tai lieu phai tra `403`.
