# Tour Detail Database Proposal

## Kết luận nhanh

Schema hiện tại đủ để:

- hiển thị danh sách tour
- lọc theo destination
- quản lý bản dịch cơ bản
- quản lý ngày khởi hành và giá đơn giản

Schema hiện tại chưa đủ tốt để:

- dựng trang detail giàu nội dung như `tour-preview`
- quản lý itinerary theo từng ngày
- quản lý nhiều ảnh/banner/gallery cho một tour
- quản lý include/exclude, hotel, transport, FAQ, review
- quản lý nhiều gói giá hoặc nhiều kiểu chỗ cho cùng một tour

## Nên giữ lại

- `tours`
- `tour_translations`
- `tour_destinations`
- `tour_departures`
- `locations`
- `location_translations`

## Nên bổ sung

### 1. `tour_media`

Mục đích:
- banner slider
- gallery ảnh
- video cover nếu cần

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK tới `tours.id` |
| `type` | `enum('cover','gallery','banner','video')` |
| `file_path` | đường dẫn ảnh/video |
| `alt_text` | text mô tả ảnh |
| `sort_order` | thứ tự |
| `created_at` | datetime |

### 2. `tour_itinerary_days`

Mục đích:
- lưu lịch trình theo ngày
- có tiêu đề và mô tả riêng cho từng ngày

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK |
| `day_number` | 1, 2, 3... |
| `title` | ví dụ `Ngày 1: Hà Nội - Tokyo` |
| `meals` | ví dụ `B/L/D` hoặc text |
| `hotel_name` | tên khách sạn nếu muốn |
| `transport_summary` | tóm tắt phương tiện |
| `sort_order` | thứ tự |

### 3. `tour_itinerary_day_translations`

Mục đích:
- đa ngôn ngữ cho itinerary từng ngày

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `itinerary_day_id` | FK |
| `locale` | `vi`, `en` |
| `title` | tiêu đề ngày |
| `description` | mô tả chi tiết |

### 4. `tour_inclusions`

Mục đích:
- quản lý các mục bao gồm / không bao gồm

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK |
| `type` | `enum('included','excluded')` |
| `icon` | icon nếu cần |
| `sort_order` | thứ tự |

### 5. `tour_inclusion_translations`

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_inclusion_id` | FK |
| `locale` | `vi`, `en` |
| `label` | nội dung hiển thị |

### 6. `tour_faqs`

Mục đích:
- phần FAQ trong detail

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK |
| `sort_order` | thứ tự |
| `is_active` | bật/tắt |

### 7. `tour_faq_translations`

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `faq_id` | FK |
| `locale` | `vi`, `en` |
| `question` | câu hỏi |
| `answer` | câu trả lời |

### 8. `tour_reviews`

Mục đích:
- review và rating

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK |
| `reviewer_name` | tên người review |
| `reviewer_email` | email |
| `rating_overall` | tổng điểm |
| `rating_destination` | điểm destination |
| `rating_transport` | điểm transport |
| `rating_value` | điểm value |
| `title` | tiêu đề review |
| `content` | nội dung |
| `status` | `pending`, `approved`, `hidden` |
| `created_at` | datetime |

### 9. `tour_highlights`

Mục đích:
- các bullet nổi bật đầu trang

Gợi ý cột:

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `tour_id` | FK |
| `icon` | icon class |
| `sort_order` | thứ tự |

### 10. `tour_highlight_translations`

| Cột | Gợi ý |
| --- | --- |
| `id` | PK |
| `highlight_id` | FK |
| `locale` | `vi`, `en` |
| `label` | text hiển thị |

## Nên chỉnh bảng hiện có

### `tours`

Nên thêm:

| Cột | Lý do |
| --- | --- |
| `sku` | mã tour nội bộ |
| `code` | mã hiển thị cho khách |
| `max_travelers` | giới hạn khách |
| `min_travelers` | số khách tối thiểu |
| `base_price` | giá từ mặc định cho card/detail |
| `sale_price` | giá khuyến mãi nếu có |
| `currency` | `VND`, `USD` |
| `rating_avg` | cache điểm trung bình |
| `reviews_count` | cache số lượng review |
| `primary_destination_id` | destination chính để canonical URL ổn định |
| `map_embed` | embed map hoặc map URL |

### `tour_translations`

Nên thêm:

| Cột | Lý do |
| --- | --- |
| `overview` | đoạn giới thiệu đầu trang |
| `booking_policy` | chính sách đặt tour |
| `cancellation_policy` | chính sách hủy |
| `price_note` | ghi chú giá |

### `tour_departures`

Nên thêm:

| Cột | Lý do |
| --- | --- |
| `child_price` | giá trẻ em |
| `infant_price` | giá em bé |
| `single_supplement` | phụ thu phòng đơn |
| `booking_deadline` | hạn chốt chỗ |
| `note` | ghi chú đợt khởi hành |

## Không nên làm ngay

- không nên nhét toàn bộ itinerary, FAQ, include/exclude thành JSON trong `tour_translations`
- không nên dùng một cột `content` khổng lồ để lưu mọi thứ của trang detail
- không nên trộn nơi khởi hành và điểm đến trong cùng một field

## Mức ưu tiên triển khai

### Giai đoạn 1

- thêm `tour_media`
- thêm `tour_itinerary_days`
- thêm `tour_itinerary_day_translations`
- thêm `tour_inclusions`
- thêm `tour_inclusion_translations`
- bổ sung vài cột thiết yếu vào `tours`

### Giai đoạn 2

- thêm `tour_faqs`
- thêm `tour_faq_translations`
- thêm `tour_reviews`

### Giai đoạn 3

- tối ưu cache rating, related tours, recommendation
- thêm bảng package option nếu một tour có nhiều gói bán khác nhau

## Kết luận

Không cần đổi lại toàn bộ database.

Hợp lý nhất là:

- giữ schema lõi hiện tại
- bổ sung các bảng con cho detail page
- tránh dồn mọi nội dung vào `tour_translations`
- giữ `tour_destinations` là nguồn điểm đến chuẩn
