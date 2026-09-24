<?php

namespace App\Data;

final class CustomTourContent
{
    public static function faqs(string $locale): array
    {
        $items = $locale === 'en' ? [
            ['What is a tailor-made tour?', 'A tailor-made tour is planned around your group’s destination, travel dates, interests and budget. You discuss the itinerary and services with a consultant before confirming the booking.'],
            ['Can I request a trip without choosing a destination?', 'Yes. Share your departure city, approximate dates, group size and preferred experiences. Leave undecided fields blank and TravelPlus will help you explore suitable destinations.'],
            ['How is a private tour priced?', 'The quote depends on destination, season, trip length, group size, transport, accommodation and activities. Your budget helps us suggest suitable options. The final quote specifies included services and any additional costs.'],
            ['Can the itinerary suit children or older travelers?', 'Tell us the children’s ages, preferred pace and any mobility or dietary needs. Our consultant will discuss suitable activities, travel times and accommodation with you.'],
            ['Can I customize a tour already on the website?', 'Yes. Select “Customize this tour” on its detail page or enter the tour name in this form. TravelPlus will discuss changes to the dates, itinerary or services, subject to availability.'],
            ['Does sending a request confirm a booking?', 'No. This form sends a consultation request. TravelPlus will contact you to discuss the itinerary and quote. Booking and payment follow after you agree on the travel plan and applicable terms.'],
        ] : [
            ['Tour theo yêu cầu là gì?', 'Tour theo yêu cầu là chương trình du lịch được thiết kế riêng theo điểm đến, ngày đi, sở thích và ngân sách của nhóm khách. Bạn trao đổi với chuyên viên về lịch trình và dịch vụ trước khi xác nhận đặt tour.'],
            ['Chưa biết đi đâu có gửi yêu cầu được không?', 'Có. Bạn chỉ cần chia sẻ nơi khởi hành, thời gian dự kiến, số người và trải nghiệm mong muốn. Các mục chưa quyết định có thể để trống; TravelPlus sẽ tư vấn điểm đến phù hợp.'],
            ['Chi phí thiết kế tour riêng được tính như thế nào?', 'Báo giá phụ thuộc vào điểm đến, mùa du lịch, số ngày, số lượng khách, phương tiện, tiêu chuẩn lưu trú và hoạt động trong tour. Ngân sách dự kiến giúp chuyên viên đề xuất phương án phù hợp. Báo giá cụ thể sẽ nêu dịch vụ bao gồm và các khoản chi phí ngoài chương trình.'],
            ['Gia đình có trẻ nhỏ hoặc người lớn tuổi có đi tour riêng được không?', 'Bạn có thể yêu cầu lịch trình phù hợp với gia đình, chia sẻ độ tuổi của các bé, nhịp di chuyển, nhu cầu ăn uống hoặc hỗ trợ đi lại. Chuyên viên sẽ trao đổi để lựa chọn hoạt động, thời gian nghỉ và nơi lưu trú phù hợp.'],
            ['Có thể điều chỉnh một tour có sẵn trên website không?', 'Có. Bấm “Tùy chỉnh tour này” tại trang chi tiết hoặc nhập tên tour vào biểu mẫu. TravelPlus sẽ trao đổi về thay đổi ngày đi, lịch trình và dịch vụ theo nhu cầu, tùy tình trạng cung ứng thực tế.'],
            ['Gửi yêu cầu có phải đặt tour hoặc thanh toán ngay không?', 'Không. Biểu mẫu này dùng để gửi nhu cầu tư vấn. TravelPlus sẽ liên hệ trao đổi lịch trình và báo giá; việc đặt tour và thanh toán được thực hiện sau khi bạn thống nhất phương án và các điều kiện dịch vụ.'],
        ];
        return array_map(static fn(array $item): array => ['question' => $item[0], 'answer' => $item[1]], $items);
    }
}
