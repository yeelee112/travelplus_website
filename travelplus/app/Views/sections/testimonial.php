<?php
$testimonials = [
    ['title' => 'Dịch vụ rất chuyên nghiệp và tận tâm', 'content' => 'Mọi thứ được sắp xếp khá ổn, ăn uống ngon và không bị gấp gáp. Rất thích cách chăm sóc khách hàng của công ty.', 'author' => 'Mr. Huy', 'tour' => 'Tour Pháp - Ý - Thụy Sĩ'],
    ['title' => 'Tour tổ chức chuyên nghiệp', 'content' => 'Xe đưa đón đúng giờ, khách sạn sạch sẽ và lịch trình rất rõ ràng. Dịch vụ tốt hơn mong đợi.', 'author' => 'Ms. Thùy', 'tour' => 'Tour MICE - Đức'],
    ['title' => 'Giá hợp lý, dịch vụ tốt', 'content' => 'Chi phí phù hợp nhưng chất lượng vượt mong đợi. Nhân viên hỗ trợ nhanh và tư vấn rất tận tình.', 'author' => 'Ms. Minh', 'tour' => 'Tour Thái Lan'],
    ['title' => 'Dịch vụ chu đáo', 'content' => 'Từ lúc đặt tour đến khi kết thúc đều được hỗ trợ tận tình. Xe sạch sẽ, khách sạn ổn và ăn uống khá ngon.', 'author' => 'Mr. Dũng', 'tour' => 'Tour Mỹ'],
    ['title' => 'Sẽ quay lại lần sau', 'content' => 'Tour có lịch trình thú vị và hướng dẫn viên cực kỳ thân thiện. Một trải nghiệm rất đáng để thử cùng gia đình và bạn bè.', 'author' => 'Ms. Tuyết', 'tour' => 'Tour Trung Quốc'],
];
?>
<div class="home2-testimonial-section">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc(lang('Frontend.home.testimonial.title')) ?></h2>
                    <p><?= esc(lang('Frontend.home.testimonial.desc')) ?></p>
                </div>
            </div>
        </div>
        <div class="row mb-40">
            <div class="col-lg-12">
                <div class="swiper home1-testimonial-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($testimonials as $item): ?>
                            <div class="swiper-slide">
                                <div class="testimonial-card three">
                                    <h5><?= esc($item['title']) ?></h5>
                                    <p><?= esc($item['content']) ?></p>
                                    <div class="author-area">
                                        <div class="author-info">
                                            <h5><?= esc($item['author']) ?></h5>
                                            <span><?= esc($item['tour']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="review-and-slider-btn wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="slider-btn-grp">
                <div class="slider-btn testimonial-slider-prev"><svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><g><path d="M11.002 13.0005C10.002 10.5005 5.00195 8.00049 2.00195 7.00049C5.00195 6.00049 9.50195 4.50049 11.002 1.00049" stroke-width="1.5" stroke-linecap="round"></path></g></svg></div>
                <div class="slider-btn testimonial-slider-next"><svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><g><path d="M2.99805 13.0005C3.99805 10.5005 8.99805 8.00049 11.998 7.00049C8.99805 6.00049 4.49805 4.50049 2.99805 1.00049" stroke-width="1.5" stroke-linecap="round"></path></g></svg></div>
            </div>
        </div>
    </div>
</div>
