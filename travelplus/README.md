# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

## Bingo Online TravelPlus

### Kiến trúc

Module Bingo sử dụng CodeIgniter 4, MySQL, Bootstrap 5 và Vanilla Javascript ES6 module. Hệ thống không dùng React, Vue, NodeJS, Firebase, Pusher hoặc WebSocket service. Realtime dùng AJAX polling mỗi 1 giây thông qua một endpoint tối ưu: `GET /updates`.

Điều kiện thắng là hoàn thành tối thiểu 2 hàng, mỗi hàng đủ 5 ô. Hàng được tính gồm hàng ngang, hàng dọc và 2 đường chéo. Trạng thái có thể Bingo chỉ được ghi nhận khi người chơi tự đánh dấu số và bảng chuyển từ dưới 2 hàng hoàn chỉnh sang đủ ít nhất 2 hàng hoàn chỉnh.

Polling được tối ưu bằng `game_versions`. Client gửi version đang có. Nếu version không đổi, API chỉ trả:

```json
{
  "success": true,
  "message": "Thành công",
  "data": {
    "changed": false,
    "version": 12
  }
}
```

Toàn bộ trạng thái game chỉ được tải khi version thay đổi. Heartbeat của người chơi được giới hạn ở cả client và server, nên `last_seen_at` không bị ghi database ở mỗi lần poll.

### ERD

```text
game_games 1--1 game_versions
game_games 1--N game_players
game_players 1--1 game_boards
game_boards 1--N game_board_cells
game_games 1--N game_draw_numbers
game_players 1--N game_player_marks
game_games 1--N game_winners
game_games 1--N game_events
```

### Database Schema

Migration `app/Database/Migrations/2026-06-29-000000_create_bingo_tables.php` tạo:

- `game_games`: phòng, trạng thái, số vừa xổ, thời gian bắt đầu/kết thúc.
- `game_versions`: version tăng dần dùng để tối ưu polling.
- `game_players`: thông tin người chơi, heartbeat online, thời điểm ready/winner.
- `game_boards`: mỗi người chơi có một bảng số.
- `game_board_cells`: 25 ô của bảng 5x5, gồm hàng, cột, số và trạng thái đã đánh dấu.
- `game_draw_numbers`: các số đã xổ, không trùng trong cùng game.
- `game_player_marks`: lịch sử đánh dấu số trên server.
- `game_winners`: thứ hạng người thắng từ 1 đến 3.
- `game_events`: timeline replay, `event_data` lưu JSON.

Tất cả bảng Bingo dùng prefix `game_` để sau này dễ nhận diện và xóa theo nhóm. Migration đã thêm index quan trọng cho `room_code`, `game_id`, `player_id`, số đã xổ, số đã đánh dấu, thứ hạng winner và timeline event.

### Routes

Trang:

- `GET /host`
- `GET /host/{room_code}`
- `GET /play`
- `GET /play/{room_code}`
- `GET /display`
- `GET /display/{room_code}`
- `GET /history`
- `GET /replay/{game_id}`
- `GET /bingo/admin`

API:

- `POST /host/create`
- `POST /host/open`
- `POST /host/start`
- `POST /host/draw`
- `POST /host/reset`
- `POST /host/end`
- `GET /game/status?room_code=TP-BINGO-001`
- `POST /player/join`
- `GET /player/board?player_id=1`
- `POST /player/board/regenerate`
- `POST /player/mark`
- `POST /player/leave`
- `POST /player/bingo`
- `GET /updates?room_code=TP-BINGO-001&version=1&player_id=1&heartbeat=0`

Tất cả API trả JSON theo mẫu:

```json
{
  "success": true,
  "message": "Thành công",
  "data": {}
}
```

### Cấu trúc thư mục

```text
app/Controllers/ApiController.php
app/Controllers/DisplayController.php
app/Controllers/HostController.php
app/Controllers/PlayerController.php
app/Controllers/ReplayController.php
app/Controllers/HistoryController.php
app/Controllers/AdminController.php
app/Services/GameService.php
app/Services/PlayerService.php
app/Services/BoardService.php
app/Services/DrawService.php
app/Services/WinnerService.php
app/Services/PollingService.php
app/Services/ReplayService.php
app/Services/EventService.php
app/Repositories/*Repository.php
app/Models/*Model.php
app/Views/bingo/*.php
public/assets/bingo/js/*.js
public/assets/bingo/css/bingo.css
```

### Migration và Seeder

Chạy migration:

```bash
php spark migrate
```

Không cần seeder bắt buộc. Có thể tạo phòng từ giao diện Host hoặc API:

Khi publish lên hosting, cần tạo database MySQL trước trong hosting control panel, cấu hình thông tin kết nối trong `.env`, sau đó chạy:

```bash
php spark migrate
```

Lệnh này sẽ tạo các bảng Bingo `game_*` nếu chưa có. Nếu database đã chạy migration trước đó, CodeIgniter chỉ chạy các migration còn thiếu.

```bash
curl -X POST http://localhost/host/create \
  -H "Content-Type: application/json" \
  -d "{\"room_code\":\"TP-BINGO-001\"}"
```

### Cài đặt

1. Cài dependency bằng `composer install`.
2. Cấu hình database trong `.env`.
3. Chạy `php spark migrate`.
4. Mở `/host` hoặc `/host/TP-BINGO-001` để tạo/mở/bắt đầu phòng.
5. Người chơi vào `/play` hoặc `/play/TP-BINGO-001`.
6. Share `/display` hoặc `/display/TP-BINGO-001` lên Microsoft Teams/TV.

### Deploy lên hosting CodeIgniter 4

- Trỏ web root về thư mục `public/`.
- Cấu hình `.env` theo môi trường production và tắt debug.
- Chạy migration trên MySQL production.
- Đảm bảo PHP đã bật `mysqli`, `intl`, `mbstring` và `json`.
- Giữ `pConnect` ở trạng thái tắt trừ khi hosting hỗ trợ persistent MySQL connection rõ ràng.
- Nếu hosting có `max_connections` thấp, vẫn giữ polling 1 giây nhưng không được bỏ qua kiểm tra `game_versions`.

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
