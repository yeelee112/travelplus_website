<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Guest feedback',
        'title' => 'Journeys handled with clear planning and attentive service',
        'desc' => 'Real feedback from travelers and business groups who booked tours, MICE and travel services with Travel Plus.',
    ]
    : [
        'eyebrow' => 'Khách hàng chia sẻ',
        'title' => 'Hành trình được vận hành rõ ràng và chăm sóc sát sao',
        'desc' => 'Những phản hồi từ khách hàng đã đặt tour, chương trình MICE và dịch vụ du lịch cùng Travel Plus.',
    ];
$testimonials = $locale === 'en'
    ? [
        [
            'title' => 'Professional from planning to onsite support',
            'content' => 'The schedule was clear, the hotel arrangement was smooth and the team responded quickly during the trip.',
            'author' => 'Mr. Huy',
            'tour' => 'France - Italy - Switzerland'
        ],
        [
            'title' => 'Strong operation for a company group',
            'content' => 'Transportation, rooming list and onsite coordination were handled carefully. Our guests followed the program without confusion.',
            'author' => 'Ms. Thuy',
            'tour' => 'Corporate MICE Europe'
        ],
        [
            'title' => 'Good value and reliable service',
            'content' => 'The price was reasonable for the quality delivered. Documents, departures and updates were communicated clearly.',
            'author' => 'Ms. Minh',
            'tour' => 'Thailand tour'
        ],
        [
            'title' => 'Visa support saved us a lot of time',
            'content' => 'Travel Plus guided us through every document requirement and helped us prepare confidently for the application process.',
            'author' => 'Mr. Nam',
            'tour' => 'Australia visa'
        ],
        [
            'title' => 'Excellent communication before departure',
            'content' => 'Every update was shared on time. The team answered questions quickly and kept our family informed throughout the trip.',
            'author' => 'Ms. Linh',
            'tour' => 'Japan tour'
        ],
        [
            'title' => 'Well-organized itinerary',
            'content' => 'The program balanced sightseeing and free time very well. Hotels and transportation met our expectations.',
            'author' => 'Mr. Khang',
            'tour' => 'South Korea tour'
        ],
        [
            'title' => 'Reliable partner for incentive travel',
            'content' => 'Our company delegation was managed professionally from arrival to departure. The experience reflected positively on our brand.',
            'author' => 'Ms. Trang',
            'tour' => 'Singapore Incentive Trip'
        ],
    ]
    : [
        [
            'title' => 'Vận hành chuyên nghiệp từ lịch trình đến onsite',
            'content' => 'Lịch trình rõ, khách sạn ổn và đội ngũ phản hồi nhanh trong suốt chuyến đi. Đoàn không bị rối ở các điểm chuyển tiếp.',
            'author' => 'Anh Huy',
            'tour' => 'Tour Pháp - Ý - Thụy Sĩ'
        ],
        [
            'title' => 'Phù hợp cho đoàn công ty',
            'content' => 'Xe đưa đón, rooming list và điều phối onsite được chuẩn bị kỹ. Khách mời đi theo chương trình thuận lợi.',
            'author' => 'Chị Thùy',
            'tour' => 'MICE doanh nghiệp châu Âu'
        ],
        [
            'title' => 'Giá hợp lý, thông tin rõ ràng',
            'content' => 'Chi phí phù hợp với chất lượng. Hồ sơ, lịch khởi hành và các cập nhật đều được Travel Plus thông báo dễ hiểu.',
            'author' => 'Chị Minh',
            'tour' => 'Tour Thái Lan'
        ],
        [
            'title' => 'Hỗ trợ visa rất tận tâm',
            'content' => 'Đội ngũ hướng dẫn hồ sơ chi tiết, kiểm tra giấy tờ kỹ và phản hồi nhanh khi cần bổ sung thông tin.',
            'author' => 'Anh Nam',
            'tour' => 'Visa Úc'
        ],
        [
            'title' => 'Chăm sóc khách hàng trước chuyến đi rất tốt',
            'content' => 'Mọi thông tin khởi hành đều được gửi đầy đủ. Gia đình tôi luôn nhận được hỗ trợ nhanh chóng khi có thắc mắc.',
            'author' => 'Chị Linh',
            'tour' => 'Tour Nhật Bản'
        ],
        [
            'title' => 'Lịch trình hợp lý, trải nghiệm trọn vẹn',
            'content' => 'Chương trình cân bằng giữa tham quan và thời gian tự do. Khách sạn và phương tiện di chuyển đều đáp ứng mong đợi.',
            'author' => 'Anh Khang',
            'tour' => 'Tour Hàn Quốc'
        ],
        [
            'title' => 'Đối tác đáng tin cậy cho đoàn doanh nghiệp',
            'content' => 'Travel Plus quản lý đoàn chuyên nghiệp từ lúc đón khách đến khi kết thúc chương trình. Khách mời đánh giá rất tích cực.',
            'author' => 'Chị Trang',
            'tour' => 'Incentive Trip Singapore'
        ],
    ];
?>

<section class="home-page__testimonials home-section home-section--soft" aria-labelledby="home-testimonial-title">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-testimonial-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
            <div class="home-testimonial-nav" aria-label="Testimonial navigation">
                <button type="button" class="testimonial-slider-prev" aria-label="Previous review"><i class="bi bi-arrow-left"></i></button>
                <button type="button" class="testimonial-slider-next" aria-label="Next review"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <div class="swiper home-page__testimonial-slider">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $item): ?>
                    <div class="swiper-slide">
                        <article class="home-testimonial-card">
                            <div class="home-testimonial-stars" aria-label="5 stars">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <h3><?= esc($item['title']) ?></h3>
                            <p><?= esc($item['content']) ?></p>
                            <footer>
                                <strong><?= esc($item['author']) ?></strong>
                                <span><?= esc($item['tour']) ?></span>
                            </footer>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
