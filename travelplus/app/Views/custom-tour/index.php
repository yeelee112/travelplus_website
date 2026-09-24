<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$locale = service('request')->getLocale() ?: 'vi';
$en = $locale === 'en';
$copy = static fn(string $vi, string $english): string => $en ? $english : $vi;
$url = \App\Data\LocalizedPathCatalog::url('customTour', $locale);
$settings = new \App\Services\WebsiteSettingsService();
$key = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
$reference = old('reference_tour', mb_substr((string) service('request')->getGet('tour'), 0, 250), false);
$input = static function (string $name, string $label, string $placeholder = '', string $type = 'text', string $default = '', string $extra = ''): void {
?>
<label class="ct-field"><span><?= esc($label) ?></span><input type="<?= esc($type) ?>" name="<?= esc($name) ?>" value="<?= esc(old($name, $default, false), 'attr') ?>" placeholder="<?= esc($placeholder, 'attr') ?>" <?= $extra ?>></label>
<?php };
?>
<link rel="stylesheet" href="<?= esc(frontend_asset_url('assets/css/custom-tour.css'), 'attr') ?>">
<main class="ct-page">
    <section class="ct-hero">
        <div class="container ct-hero-grid">
            <picture class="ct-hero-photo"><source srcset="<?= frontend_asset_url('assets/images/tailor-made/img-01.webp') ?>" type="image/webp"><img src="<?= frontend_asset_url('assets/images/tailor-made/img-01.png') ?>" width="1934" height="813" fetchpriority="high" alt="<?= $copy('Du khách lên kế hoạch hành trình riêng bên vịnh biển lúc hoàng hôn', 'A traveler planning a private journey overlooking a bay at sunset') ?>"></picture>
            <div class="ct-hero-copy">
                <span class="ct-eyebrow">TRAVEL PLUS · <?= $copy('HÀNH TRÌNH THIẾT KẾ RIÊNG', 'TAILOR-MADE JOURNEYS') ?></span>
                <h1><?= $copy('Thiết kế tour<br>theo yêu cầu', 'Tailor-made tours,<br>designed for you') ?></h1>
                <p class="ct-hero-tagline"><?= $copy('Đi theo cách bạn thích.', 'Your journey. Your way.') ?></p>
                <p><?= $copy('Chọn điểm đến, nhịp trải nghiệm và ngân sách. TravelPlus cùng bạn tạo nên chuyến đi riêng cho gia đình, nhóm bạn hoặc doanh nghiệp.', 'Choose your destination, pace and budget. TravelPlus helps create a private journey for your family, friends or team.') ?></p>
                <div class="ct-hero-actions"><a class="ct-button" href="#trip-form"><?= $copy('Bắt đầu thiết kế tour', 'Start planning your tour') ?> <span aria-hidden="true">↗</span></a><a class="ct-hero-link" href="#custom-tour-faq"><?= $copy('Bạn cần biết gì?', 'Good to know') ?> <span aria-hidden="true">↓</span></a></div>
                <span class="ct-hero-note"><?= $copy('Chia sẻ nhu cầu trước. Quyết định đặt tour sau.', 'Share your ideas now. Decide on booking later.') ?></span>
            </div>
        </div>
    </section>
    <div class="container ct-value-strip">
        <div><i class="bi bi-calendar2-week" aria-hidden="true"></i><span><b><?= $copy('Chủ động ngày đi', 'Your travel dates') ?></b><small><?= $copy('Lên kế hoạch theo thời gian của bạn', 'Plan around your own schedule') ?></small></span></div>
        <div><i class="bi bi-signpost-split" aria-hidden="true"></i><span><b><?= $copy('Lịch trình mang dấu ấn riêng', 'An itinerary that fits') ?></b><small><?= $copy('Chọn trải nghiệm, nhịp đi và nơi nghỉ', 'Choose your experiences and pace') ?></small></span></div>
        <div><i class="bi bi-wallet2" aria-hidden="true"></i><span><b><?= $copy('Ngân sách có định hướng', 'A budget to work with') ?></b><small><?= $copy('Trao đổi phương án trước khi đặt tour', 'Discuss your options before booking') ?></small></span></div>
    </div>
    <section class="container ct-layout" id="trip-form">
        <aside class="ct-aside">
            <span class="ct-eyebrow"><?= $copy('BẮT ĐẦU TỪ Ý TƯỞNG CỦA BẠN', 'START WITH YOUR IDEAS') ?></span>
            <h2><?= $copy('Kể về chuyến đi<br> bạn đang hình dung.', 'Tell us about<br> your ideal trip.') ?></h2>
            <p><?= $copy('Chưa có kế hoạch cụ thể? Bạn có thể để trống hoặc chọn “Cần tư vấn”.', 'Still deciding? Leave optional fields blank or select “Help me choose”.') ?></p>
            <ol class="ct-process">
                <li><b><?= $copy('Chia sẻ mong muốn', 'Share your wishes') ?></b><span><?= $copy('Điểm đến, thời gian và phong cách du lịch.', 'Destination, timing and travel style.') ?></span></li>
                <li><b><?= $copy('Trao đổi cùng TravelPlus', 'Talk with TravelPlus') ?></b><span><?= $copy('Chuyên viên liên hệ để hiểu rõ nhu cầu.', 'A consultant contacts you to discuss your needs.') ?></span></li>
                <li><b><?= $copy('Nhận lịch trình & báo giá', 'Receive a plan & quote') ?></b><span><?= $copy('Cùng điều chỉnh trước khi quyết định đặt tour.', 'Fine-tune the plan before booking.') ?></span></li>
            </ol>
            <div class="ct-help"><i class="bi bi-headset" aria-hidden="true"></i><div><?= $copy('Muốn trao đổi trực tiếp?', 'Prefer to talk?') ?><a href="tel:<?= esc($settings->get('hotline_e164'), 'attr') ?>"><?= esc($settings->phoneDisplay($locale)) ?></a></div></div>
        </aside>
        <div class="ct-card">
            <?php if ($success = session()->getFlashdata('success')): ?>
                <div class="ct-success" role="status"><span aria-hidden="true">✓</span><h2><?= $copy('Đã nhận yêu cầu của bạn!', 'Your request has been received!') ?></h2><p><?= $copy('TravelPlus sẽ liên hệ theo thông tin bạn đã cung cấp để tư vấn lịch trình và báo giá phù hợp.', 'TravelPlus will contact you using the details you provided to discuss your itinerary and quote.') ?></p><a class="ct-button" href="<?= esc($settings->get('zalo_url'), 'attr') ?>"><?= $copy('Trao đổi qua Zalo', 'Chat on Zalo') ?></a><a class="ct-reset" href="<?= esc($url, 'attr') ?>"><?= $copy('Tạo yêu cầu khác', 'Create another request') ?></a></div>
            <?php else: ?>
            <div class="ct-form-heading"><span class="ct-eyebrow"><?= $copy('CHUYẾN ĐI BẮT ĐẦU TỪ ĐÂY', 'YOUR JOURNEY STARTS HERE') ?></span><h2><?= $copy('Nhận tư vấn tour riêng', 'Request a private tour') ?></h2><p><?= $copy('3 bước chia sẻ nhu cầu · Chưa cần thanh toán', '3 steps to share your plans · No payment required') ?></p></div>
            <?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger" role="alert"><?= nl2br(esc($error)) ?></div><?php endif; ?>
            <form id="customTourForm" action="<?= esc($url, 'attr') ?>" method="post" data-key="<?= esc($key, 'attr') ?>" data-locale="<?= esc($locale) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="contact_form_token" value="<?= esc($contact_form_token, 'attr') ?>">
                <input type="hidden" name="recaptcha_token" value="">
                <div class="ct-progress" aria-label="<?= $copy('Tiến độ', 'Progress') ?>"><span data-step-label>1. <?= $copy('Điểm đến', 'Destination') ?></span><span data-step-label>2. <?= $copy('Trải nghiệm', 'Experience') ?></span><span data-step-label>3. <?= $copy('Liên hệ', 'Contact') ?></span></div>
                <fieldset data-step><legend tabindex="-1"><?= $copy('Bạn muốn đi đâu?', 'Where would you like to go?') ?></legend>
                    <p class="ct-step-description"><?= $copy('Một điểm đến mơ ước, hoặc chỉ là ý tưởng cho kỳ nghỉ sắp tới.', 'A dream destination, or just an idea for your next escape.') ?></p>
                    <div class="ct-fields">
                        <?php $input('destination', $copy('Điểm đến mong muốn', 'Preferred destination'), $copy('Ví dụ: Đà Nẵng, Nhật Bản…', 'E.g. Da Nang, Japan…'), 'text', '', 'maxlength="160"'); ?>
                        <?php $input('departure', $copy('Nơi khởi hành', 'Departing from'), $copy('Thành phố bạn đang ở', 'Your city'), 'text', '', 'maxlength="160"'); ?>
                        <?php $input('estimated_time', $copy('Thời gian dự kiến', 'Preferred travel period'), $copy('Tháng/năm hoặc cần tư vấn', 'Month/year or help me choose'), 'text', '', 'maxlength="80"'); ?>
                        <?php $input('trip_length', $copy('Bạn muốn đi bao lâu?', 'How long would you like to travel?'), $copy('Ví dụ: 4 ngày 3 đêm', 'E.g. 4 days, 3 nights'), 'text', '', 'maxlength="120"'); ?>
                    </div>
                    <p class="ct-hint"><?= $copy('Bạn có thể để trống các mục chưa quyết định, chuyên viên sẽ gợi ý cùng bạn.', 'You can leave undecided details blank. Our consultant will help you choose.') ?></p>
                </fieldset>
                <fieldset data-step><legend tabindex="-1"><?= $copy('Chuyến đi như thế nào?', 'What is your ideal trip?') ?></legend>
                    <div class="ct-fields">
                        <?php $input('adults', $copy('Người lớn *', 'Adults *'), '', 'number', '2', 'required min="1" max="1000" step="1"'); ?>
                        <?php $input('children', $copy('Trẻ em *', 'Children *'), '', 'number', '0', 'required min="0" max="1000" step="1"'); ?>
                        <?php $input('child_ages', $copy('Độ tuổi của các bé', 'Children’s ages'), $copy('Ví dụ: 4 và 8 tuổi', 'E.g. 4 and 8 years'), 'text', '', 'maxlength="160"'); ?>
                        <label class="ct-field"><span><?= $copy('Ngân sách dự kiến / người (VND)', 'Budget per person (VND)') ?></span><select name="budget"><?php foreach (['' => $copy('Cần tư vấn', 'Help me choose'), 'Dưới 5 triệu' => $copy('Dưới 5 triệu', 'Under 5 million'), '5–10 triệu' => $copy('5–10 triệu', '5–10 million'), '10–20 triệu' => $copy('10–20 triệu', '10–20 million'), '20–40 triệu' => $copy('20–40 triệu', '20–40 million'), 'Trên 40 triệu' => $copy('Trên 40 triệu', 'Over 40 million')] as $value => $label): ?><option value="<?= esc($value, 'attr') ?>" <?= old('budget') === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></label>
                    </div>
                    <p class="ct-field-title"><?= $copy('Bạn ưu tiên trải nghiệm nào?', 'What matters most to you?') ?></p>
                    <div class="ct-choices"><?php foreach (['' => $copy('Cần tư vấn', 'Help me choose'), 'relax' => $copy('Nghỉ dưỡng', 'Relaxation'), 'explore' => $copy('Khám phá', 'Exploration'), 'food' => $copy('Ẩm thực', 'Food'), 'team' => 'Team building', 'family' => $copy('Gia đình', 'Family')] as $value => $label): ?><label><input type="radio" name="interests" value="<?= esc($value) ?>" <?= old('interests', '') === $value ? 'checked' : '' ?>><span><?= esc($label) ?></span></label><?php endforeach; ?></div>
                </fieldset>
                <fieldset data-step><legend tabindex="-1"><?= $copy('Nhận tư vấn hành trình', 'Let’s plan your journey') ?></legend>
                    <div id="tripSummary" class="ct-summary" aria-live="polite"></div>
                    <div class="ct-fields">
                        <?php $input('name', $copy('Họ và tên *', 'Full name *'), '', 'text', '', 'required minlength="2" maxlength="120" autocomplete="name"'); ?>
                        <?php $input('phone', $copy('Điện thoại / Zalo *', 'Phone / Zalo *'), '090… / +84…', 'tel', '', 'required maxlength="30" autocomplete="tel"'); ?>
                        <?php $input('email', $copy('Email (không bắt buộc)', 'Email (optional)'), '', 'email', '', 'maxlength="160" autocomplete="email"'); ?>
                        <?php $input('reference_tour', $copy('Tour tham khảo (nếu có)', 'Reference tour (optional)'), '', 'text', (string) $reference, 'maxlength="250"'); ?>
                    </div>
                    <label class="ct-field"><span><?= $copy('Mong muốn khác', 'Anything else?') ?></span><textarea name="message" rows="3" maxlength="3000" placeholder="<?= $copy('Khách sạn, món ăn, dịp kỷ niệm hoặc yêu cầu đặc biệt…', 'Hotels, food, celebrations or special requests…') ?>"><?= esc(old('message', '', false)) ?></textarea></label>
                    <label class="ct-consent"><input type="checkbox" name="privacy_agree" value="1" required <?= old('privacy_agree') ? 'checked' : '' ?>><span><?= $copy('Tôi đồng ý với', 'I agree to the') ?> <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.privacy', $locale), 'attr') ?>" target="_blank" rel="noopener"><?= $copy('Chính sách bảo mật', 'Privacy Statement') ?></a> <?= $copy('và', 'and') ?> <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.terms', $locale), 'attr') ?>" target="_blank" rel="noopener"><?= $copy('Điều khoản sử dụng', 'Terms of Service') ?></a>.</span></label>
                    <p class="ct-hint"><?= $copy('Gửi yêu cầu chưa phải đặt tour hay thanh toán.', 'Sending a request does not make a booking or require payment.') ?></p>
                </fieldset>
                <div class="ct-error" role="alert" hidden></div>
                <div class="ct-actions"><button type="button" class="ct-back" hidden><?= $copy('← Quay lại', '← Back') ?></button><button type="button" class="ct-button ct-next" hidden><?= $copy('Tiếp tục →', 'Continue →') ?></button><button type="submit" class="ct-button ct-submit"><?= $copy('Gửi yêu cầu tư vấn', 'Send consultation request') ?></button></div>
                <noscript><p><?= $copy('Vui lòng bật JavaScript để xác minh và gửi yêu cầu, hoặc liên hệ hotline bên cạnh.', 'Please enable JavaScript to verify and send your request, or call our hotline.') ?></p></noscript>
            </form>
            <?php endif; ?>
        </div>
    </section>
    <section class="container ct-inspiration" aria-labelledby="ct-inspiration-title">
        <div class="ct-section-head"><div><span class="ct-eyebrow"><?= $copy('MỖI CHUYẾN ĐI, MỘT CÂU CHUYỆN', 'EVERY JOURNEY HAS A STORY') ?></span><h2 id="ct-inspiration-title"><?= $copy('Tour riêng cho những điều bạn trân trọng', 'Private travel for what matters to you') ?></h2></div><p><?= $copy('Du lịch theo yêu cầu giúp bạn chủ động lựa chọn trải nghiệm thay vì chỉ chọn một lịch trình có sẵn. Bắt đầu từ người đồng hành và mục đích chuyến đi.', 'Tailor-made travel lets you choose the experiences that matter to you. Start with your travel companions and the purpose of your trip.') ?></p></div>
        <div class="ct-audiences">
            <?php foreach ([
                ['family', 'bi-house-heart', $copy('Cùng gia đình', 'With family'), $copy('Thêm thời gian bên nhau', 'More time together'), $copy('Lịch trình vừa sức, có khoảng nghỉ và hoạt động phù hợp cho trẻ nhỏ, bố mẹ hoặc ông bà. Chia sẻ độ tuổi và nhu cầu để chuyên viên tư vấn.', 'A comfortable pace, time to rest and activities for children, parents or grandparents. Share ages and needs with your consultant.')],
                ['explore', 'bi-compass', $copy('Cùng nhóm bạn', 'With friends'), $copy('Cùng sở thích, cùng khám phá', 'Shared interests, new discoveries'), $copy('Kết hợp khám phá, ẩm thực, nghỉ dưỡng hay những điểm dừng yêu thích. Chủ động trao đổi thời gian khởi hành và tiêu chuẩn lưu trú.', 'Combine exploration, food, relaxation and favorite stops. Discuss departure dates and the accommodation style that suits your group.')],
                ['team', 'bi-people', $copy('Cùng doanh nghiệp', 'With your team'), $copy('Kết nối qua từng trải nghiệm', 'Bring people together'), $copy('Định hướng chương trình du lịch đoàn, team building hoặc chuyến đi khen thưởng theo mục tiêu, quy mô và ngân sách của doanh nghiệp.', 'Plan a group trip, team-building program or incentive journey around your company’s objectives, group size and budget.')],
            ] as [$interest, $icon, $tag, $heading, $description]): ?>
            <article class="ct-audience"><div class="ct-audience-top"><i class="bi <?= esc($icon) ?>" aria-hidden="true"></i><span><?= esc($tag) ?></span></div><h3><?= esc($heading) ?></h3><p><?= esc($description) ?></p><a href="#trip-form" data-interest="<?= esc($interest) ?>"><?= $copy('Lên kế hoạch chuyến đi', 'Plan this journey') ?> <span aria-hidden="true">↗</span></a></article>
            <?php endforeach; ?>
        </div>
        <div class="ct-related"><span><?= $copy('Cần cảm hứng về điểm đến?', 'Looking for destination ideas?') ?></span><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('domestic', $locale), 'attr') ?>"><?= $copy('Tour trong nước', 'Domestic tours') ?> ↗</a><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('outbound', $locale), 'attr') ?>"><?= $copy('Tour nước ngoài', 'Outbound tours') ?> ↗</a><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('service.mice', $locale), 'attr') ?>"><?= $copy('Du lịch MICE', 'MICE travel') ?> ↗</a></div>
    </section>
    <section class="container ct-faq" id="custom-tour-faq" aria-labelledby="ct-faq-title"><div><span class="ct-eyebrow"><?= $copy('GIẢI ĐÁP TRƯỚC CHUYẾN ĐI', 'BEFORE YOU GO') ?></span><h2 id="ct-faq-title"><?= $copy('Bạn hỏi,<br> TravelPlus giải đáp.', 'Your questions,<br> answered.') ?></h2><p><?= $copy('Những điều cần biết khi thiết kế tour theo yêu cầu và nhận báo giá cho hành trình riêng.', 'What to know about tailor-made tours and requesting a quote for your private journey.') ?></p><a class="ct-text-link" href="<?= esc($settings->get('zalo_url'), 'attr') ?>"><?= $copy('Trao đổi thêm qua Zalo', 'Ask us on Zalo') ?> ↗</a></div><div class="ct-faq-list"><?php foreach ($faqs as $faq): ?><details><summary><?= esc($faq['question']) ?></summary><p><?= esc($faq['answer']) ?></p></details><?php endforeach; ?></div></section>
</main>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($key !== ''): ?><script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc(rawurlencode($key), 'attr') ?>"></script><?php endif; ?>
<script defer src="<?= esc(frontend_asset_url('assets/js/custom-tour.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
