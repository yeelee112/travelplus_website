# Database Structure

## Overview

Schema hiện tại trong database `travelplus_db` có 8 bảng:

- `locations`
- `location_translations`
- `tour_categories`
- `tour_category_translations`
- `tours`
- `tour_translations`
- `tour_departures`
- `tour_destinations`

## Tables

| Bảng | Mục đích | Cột chính | Quan hệ chính |
| --- | --- | --- | --- |
| `locations` | Cây địa điểm dùng cho châu lục, quốc gia, tỉnh/thành | `id`, `parent_id`, `type`, `code` | Tự tham chiếu qua `parent_id -> locations.id` |
| `location_translations` | Tên và slug đa ngôn ngữ của địa điểm | `location_id`, `locale`, `name`, `slug` | `location_id -> locations.id` |
| `tour_categories` | Danh mục tour, hỗ trợ cây cha-con | `id`, `parent_id`, `type` | `parent_id -> tour_categories.id` |
| `tour_category_translations` | Tên và slug đa ngôn ngữ của danh mục tour | `category_id`, `locale`, `name`, `slug` | `category_id -> tour_categories.id` |
| `tours` | Bảng tour chính | `id`, `category_id`, `departure_location_id`, `tour_type`, `duration_days`, `duration_nights`, `thumbnail`, `is_featured`, `status` | `category_id -> tour_categories.id`, `departure_location_id -> locations.id` |
| `tour_translations` | Nội dung đa ngôn ngữ của tour | `tour_id`, `locale`, `name`, `slug`, `short_description`, `description`, `itinerary`, `meta_title`, `meta_description` | `tour_id -> tours.id` |
| `tour_departures` | Ngày khởi hành và giá theo đợt | `tour_id`, `departure_date`, `available_slots`, `price`, `price_up`, `status` | `tour_id -> tours.id` |
| `tour_destinations` | Điểm đến của tour | `tour_id`, `location_id` | `tour_id -> tours.id`, `location_id -> locations.id` |

## Column Details

### `locations`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `parent_id` | `int` | Yes | FK tới `locations.id` |
| `type` | `enum('continent','country','province')` | No | Cấp địa điểm |
| `code` | `varchar(20)` | Yes | Mã viết tắt, ví dụ mã cờ/quốc gia |
| `created_at` | `datetime` | Yes | |
| `updated_at` | `datetime` | Yes | |

### `location_translations`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `location_id` | `int` | No | FK tới `locations.id` |
| `locale` | `varchar(10)` | No | `vi`, `en` |
| `name` | `varchar(255)` | No | Tên hiển thị |
| `slug` | `varchar(255)` | No | Slug theo ngôn ngữ |

Unique key:
- `location_id + locale`

### `tour_categories`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `parent_id` | `int` | Yes | FK tới `tour_categories.id` |
| `type` | `enum('inbound','outbound')` | No | Loại tour |
| `created_at` | `datetime` | Yes | |
| `updated_at` | `datetime` | Yes | |

### `tour_category_translations`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `category_id` | `int` | No | FK tới `tour_categories.id` |
| `locale` | `varchar(10)` | No | `vi`, `en` |
| `name` | `varchar(255)` | No | Tên danh mục |
| `slug` | `varchar(255)` | No | Slug danh mục |

Unique key:
- `category_id + locale`

### `tours`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `category_id` | `int` | No | FK tới `tour_categories.id` |
| `departure_location_id` | `int` | No | FK tới `locations.id`, là nơi khởi hành |
| `tour_type` | `enum('inbound','outbound')` | No | Trong nước hoặc nước ngoài |
| `duration_days` | `int` | No | Số ngày |
| `duration_nights` | `int` | No | Số đêm |
| `thumbnail` | `varchar(255)` | Yes | Ảnh đại diện |
| `is_featured` | `tinyint(1)` | Yes | Tour nổi bật |
| `status` | `enum('draft','published')` | Yes | Trạng thái tour |
| `created_at` | `datetime` | Yes | |
| `updated_at` | `datetime` | Yes | |

### `tour_translations`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `tour_id` | `int` | No | FK tới `tours.id` |
| `locale` | `varchar(10)` | No | `vi`, `en` |
| `name` | `varchar(255)` | No | Tên tour |
| `slug` | `varchar(255)` | No | Slug detail tour |
| `short_description` | `text` | Yes | Mô tả ngắn |
| `description` | `longtext` | Yes | Nội dung dài |
| `itinerary` | `longtext` | Yes | Lịch trình |
| `meta_title` | `varchar(255)` | Yes | SEO title |
| `meta_description` | `text` | Yes | SEO description |

Unique key:
- `tour_id + locale`

### `tour_departures`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `tour_id` | `int` | No | FK tới `tours.id` |
| `departure_date` | `date` | No | Ngày khởi hành |
| `available_slots` | `int` | Yes | Số chỗ còn lại |
| `price` | `int` | No | Giá cơ bản |
| `price_up` | `int` | Yes | Giá nâng cấp hoặc giá từ |
| `status` | `enum('open','closed')` | Yes | Trạng thái mở bán |
| `created_at` | `datetime` | Yes | |

### `tour_destinations`

| Cột | Kiểu | Null | Ghi chú |
| --- | --- | --- | --- |
| `id` | `int` | No | PK |
| `tour_id` | `int` | No | FK tới `tours.id` |
| `location_id` | `int` | No | FK tới `locations.id`, là điểm đến |

## Data Flow

### 1. Địa điểm đa ngôn ngữ

- `locations` giữ cấu trúc cây
- `location_translations` giữ `name` và `slug` theo từng ngôn ngữ

Ví dụ:
- `locations`: Châu Âu -> Pháp -> Paris
- `location_translations`: `chau-au`, `phap`, `paris` cho `vi`; `europe`, `france`, `paris` cho `en`

### 2. Tour

- `tours` giữ thông tin lõi
- `tour_translations` giữ nội dung theo locale
- `tour_departures` giữ ngày khởi hành và giá
- `tour_destinations` gắn tour với một hay nhiều điểm đến

### 3. Ý nghĩa nghiệp vụ quan trọng

- `departure_location_id` là nơi khởi hành, không phải điểm đến
- điểm đến thực tế của tour nằm ở `tour_destinations`
- outbound đang lọc theo `tour_destinations -> locations`
- inbound theo miền hiện tại được map từ `location_id` tỉnh/thành

## Current Relationship Map

```text
locations
└── location_translations

tour_categories
└── tour_category_translations

tours
├── tour_translations
├── tour_departures
├── tour_destinations
├── departure_location_id -> locations.id
└── category_id -> tour_categories.id
```

## Notes

- Migration trong `app/Database/Migrations/20260130_create_content_tables.php` hiện không còn phản ánh đầy đủ schema thật.
- Schema thật hiện đang giàu hơn migration, đặc biệt ở phần:
  - địa điểm đa ngôn ngữ
  - destination của tour
  - ngày khởi hành
  - category đa ngôn ngữ
