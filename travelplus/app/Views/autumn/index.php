<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$en = $currentLocale === 'en';
$t = static fn(string $vi, string $english): string => $en ? $english : $vi;
$url = \App\Data\LocalizedPathCatalog::url('autumn', $currentLocale);
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $currentLocale);
$customTourUrl = \App\Data\LocalizedPathCatalog::url('customTour', $currentLocale) . '?' . http_build_query(['tour' => $t('Tour thiết kế riêng mùa thu ', 'Private autumn journey ') . $autumnYear]);
$rewardUrl = \App\Data\LocalizedPathCatalog::url('passport.program', $currentLocale);
$settings = new \App\Services\WebsiteSettingsService();
$places = [
 ['nhat-ban', 'nhat-ban.webp', 'Nhật Bản', 'Japan', 'Sắc phong đỏ bên núi Phú Sĩ', 'Maple colours by Mount Fuji', 'Ngắm lá đỏ · Văn hóa · Ẩm thực', 'Autumn leaves · Culture · Food'],
 ['han-quoc', 'han-quoc.jpg', 'Hàn Quốc', 'South Korea', 'Một mùa thu thật lãng mạn', 'An autumn to fall in love with', 'Dạo phố · Cảnh đẹp · Mua sắm', 'City walks · Scenery · Shopping'],
 ['trung-quoc', 'china.jpg', 'Trung Quốc', 'China', 'Đi qua những miền cổ tích', 'Step into extraordinary landscapes', 'Thiên nhiên · Phố cổ · Văn hóa', 'Nature · Old towns · Culture'],
 ['sa-pa', 'sa-pa.webp', 'Sa Pa', 'Sa Pa', 'Hẹn một sớm giữa mây ngàn', 'Wake up above the clouds', 'Núi rừng · Bản làng · Nghỉ ngơi', 'Mountains · Villages · Slow days'],
];
$icon = static fn(string $name): string => '<i class="bi bi-' . $name . '" aria-hidden="true"></i>';
?>
<div class="autumn-landing">
 <section class="at-hero" aria-labelledby="at-title">
  <div class="at-container at-hero-grid">
   <div class="at-hero-copy">
    <span class="at-season"><?= $icon('leaf') ?> <?= $t('MÙA THU', 'AUTUMN') ?> <?= (int) $autumnYear ?> <span>09 — 11</span></span>
    <h1 id="at-title"><?= $t('Thu sang.<br><em>Đi thôi!</em>', 'Autumn is here.<br><em>Let’s go!</em>') ?></h1>
    <p><?= $t('Đổi khung cảnh, thêm kỷ niệm.<br>Chọn một hành trình mùa thu dành riêng cho bạn.', 'A change of scenery. A new memory.<br>Find the autumn journey that feels like you.') ?></p>
    <div class="at-hero-actions"><a class="at-btn" href="#autumn-tours"><?= $t('Chọn tour mùa thu', 'Find autumn tours') ?> <?= $icon('arrow-right') ?></a><a class="at-link" href="<?= esc($settings->get('zalo_url'), 'attr') ?>"><?= $t('Tư vấn qua Zalo', 'Chat on Zalo') ?> <?= $icon('chat-dots') ?></a></div>
    <div class="at-hero-note"><?= $icon('check-circle-fill') ?> <?= $t('Lịch trình rõ ràng', 'Clear itineraries') ?><span></span><?= $t('Tư vấn theo nhu cầu', 'Advice tailored to you') ?></div>
   </div>
   <div class="at-hero-visual">
    <img class="at-main-photo" src="<?= base_url('assets/images/destination/nhat-ban.webp') ?>" alt="<?= $t('Lá phong đỏ rực bên hồ Kawaguchi và núi Phú Sĩ, Nhật Bản', 'Red maple leaves at Lake Kawaguchi and Mount Fuji, Japan') ?>" width="800" height="533" fetchpriority="high">
    <div class="at-photo-caption"><?= $icon('geo-alt-fill') ?><span><strong><?= $t('Nhật Bản vào thu', 'Autumn in Japan') ?></strong><small><?= $t('Một khung hình, ngàn thương nhớ', 'A view worth travelling for') ?></small></span></div>
    <div class="at-stamp" aria-hidden="true">TRAVEL PLUS<br><b>hello<br>autumn!</b><span>✦ &nbsp; <?= (int) $autumnYear ?> &nbsp; ✦</span></div>
   </div>
  </div>
 </section>
 <div class="at-container at-search-wrap">
  <form class="at-search" action="<?= esc($url, 'attr') ?>#autumn-tours" method="get">
   <div class="at-search-title"><?= $icon('compass') ?><strong><?= $t('Mùa thu này, <br>bạn muốn đi đâu?', 'Where to <br>this autumn?') ?></strong></div>
   <label><?= $icon('geo-alt') ?><span><?= $t('Điểm đến', 'Destination') ?><select name="destination"><option value=""><?= $t('Tất cả điểm đến', 'All destinations') ?></option><?php foreach ($destinationOptions as $key => $label): ?><option value="<?= esc($key) ?>" <?= $selectedDestination === $key ? 'selected' : '' ?>><?= esc($label[$currentLocale]) ?></option><?php endforeach ?></select></span></label>
   <label><?= $icon('calendar3') ?><span><?= $t('Tháng khởi hành', 'Departure month') ?><select name="month"><option value=""><?= $t('Tháng 9 – 11', 'September – November') ?></option><?php foreach (['09', '10', '11'] as $month): ?><option value="<?= $month ?>" <?= $selectedMonth === $month ? 'selected' : '' ?>><?= $t('Tháng ', 'Month ') . (int) $month ?> / <?= (int) $autumnYear ?></option><?php endforeach ?></select></span></label>
   <button class="at-btn" type="submit"><?= $icon('search') ?> <?= $t('Tìm tour phù hợp', 'Find my tour') ?></button>
  </form>
 </div>
 <nav class="at-container at-page-nav" aria-label="<?= $t('Nội dung trang mùa thu', 'Autumn page navigation') ?>"><a href="#autumn-destinations"><?= $t('Điểm đến mùa thu', 'Destinations') ?></a><a href="#autumn-tours"><?= $t('Tour & lịch khởi hành', 'Tours & departures') ?></a><a href="#autumn-faq"><?= $t('Câu hỏi thường gặp', 'FAQs') ?></a><a href="#autumn-advice"><?= $t('Nhờ tư vấn', 'Get advice') ?> <?= $icon('arrow-up-right') ?></a></nav>
 <section class="at-container at-section" id="autumn-destinations">
  <div class="at-section-head"><div><p class="at-kicker"><?= $t('MỖI NƠI, MỘT SẮC THU', 'FIND YOUR AUTUMN COLOURS') ?></p><h2><?= $t('Chạm vào mùa thu bạn thích', 'Your kind of autumn') ?></h2><p><?= $t('Thích lá đỏ, mê phố cổ hay muốn trốn phố lên núi? Bắt đầu từ đây.', 'Maple leaves, old towns or a mountain escape? Start here.') ?></p></div><span class="at-handwritten"><?= $t('Đi để thấy, đi để nhớ.', 'Go. Explore. Remember.') ?></span></div>
  <div class="at-destinations"><?php foreach ($places as $place): ?><a class="at-destination" href="<?= esc($url . '?' . http_build_query(['destination' => $place[0], 'month' => $selectedMonth]), 'attr') ?>#autumn-tours"><img src="<?= base_url('assets/images/destination/' . $place[1]) ?>" alt="<?= esc($place[$en ? 3 : 2]) ?>" width="800" height="533" loading="lazy"><div class="at-destination-body"><span><?= esc($place[$en ? 7 : 6]) ?></span><h3><?= esc($place[$en ? 3 : 2]) ?></h3><p><?= esc($place[$en ? 5 : 4]) ?></p></div><span class="at-circle"><?= $icon('arrow-up-right') ?></span></a><?php endforeach ?></div>
 </section>
 <section class="at-tour-section" id="autumn-tours"><div class="at-container">
  <div class="at-section-head"><div><p class="at-kicker"><?= $t('CHỌN CHUYẾN ĐI CỦA BẠN', 'YOUR NEXT JOURNEY') ?></p><h2><?= $t('Tour & lịch khởi hành mùa thu', 'Autumn tours & departures') ?></h2><p><?= $t('Xem giá, ngày đi và lịch trình trước khi quyết định.', 'Compare prices, dates and itineraries before you decide.') ?></p></div><a class="at-link" href="<?= esc($allToursUrl, 'attr') ?>"><?= $t('Xem tất cả tour', 'View all tours') ?> <?= $icon('arrow-right') ?></a></div>
  <div class="at-filters" aria-label="<?= $t('Lọc theo điểm đến', 'Filter destinations') ?>"><?php foreach (['' => ['vi' => 'Tất cả', 'en' => 'All']] + $destinationOptions as $key => $label): ?><a class="<?= $selectedDestination === $key ? 'is-active' : '' ?>" <?= $selectedDestination === $key ? 'aria-current="true"' : '' ?> href="<?= esc($url . '?' . http_build_query(['destination' => $key, 'month' => $selectedMonth]), 'attr') ?>#autumn-tours"><?= esc($label[$currentLocale]) ?></a><?php endforeach ?></div>
  <?php if ($autumnTours !== []): ?><div class="at-tour-grid" id="autumn-tour-track" tabindex="0" role="region" aria-label="<?= $t('Danh sách tour mùa thu, vuốt ngang để xem thêm', 'Autumn tours, swipe to explore') ?>">
   <?php foreach ($autumnTours as $tour):
    $amount = (float) ($tour['price']['amount'] ?? 0);
    $points = \App\Services\LoyaltyPointService::previewPoints($amount);
    $benefit = \App\Services\TourPassportPricePresenter::build($amount, is_array($authUser ?? null) ? $authUser : null, is_array($headerMembership ?? null) ? $headerMembership : null, $currentLocale);
   ?><article class="at-tour-card">
    <a class="at-tour-image" href="<?= esc($tour['link'], 'attr') ?>"><img src="<?= esc($tour['image'], 'attr') ?>" alt="<?= esc($tour['title']) ?>" width="600" height="400" loading="lazy"><span><?= $icon('clock') ?> <?= esc($tour['duration']['label'] ?? '') ?></span></a>
    <div class="at-tour-body">
     <p class="at-tour-place"><?= $icon('geo-alt-fill') ?> <?= esc($tour['destination_summary'] ?: $tour['destination_name']) ?></p>
     <h3><a href="<?= esc($tour['link'], 'attr') ?>"><?= esc($tour['title']) ?></a></h3>
     <div class="at-tour-meta"><div><span><?= $icon('calendar3') ?> <?= $t('Khởi hành', 'Departure') ?></span><strong><?= esc($tour['departure'] ?: $t('Liên hệ tư vấn', 'Contact us')) ?></strong></div><?php if (!empty($tour['departure_from'])): ?><div><span><?= $icon('geo-alt') ?> <?= $t('Điểm đi', 'From') ?></span><strong><?= esc($tour['departure_from']) ?></strong></div><?php endif ?></div>
     <div class="at-tour-bottom"><div class="at-tour-price"><small><?= $t('Giá từ / khách', 'From / person') ?></small><?php if (($benefit['state'] ?? '') === 'active'): ?><?= view('components/passport-price-benefit', ['benefit' => $benefit, 'originalPrice' => $tour['price']['label'] ?? '']) ?><?php else: ?><strong><?= $amount > 0 ? esc($tour['price']['label']) : $t('Liên hệ', 'Contact us') ?></strong><?php endif ?></div><a class="at-btn at-btn-small" href="<?= esc($tour['link'], 'attr') ?>"><?= $t('Xem lịch trình', 'View itinerary') ?> <?= $icon('arrow-up-right') ?></a></div>
     <a class="at-reward" href="<?= esc($rewardUrl, 'attr') ?>"><span class="at-reward-icon"><?= $icon('gift') ?></span><span><b>Travel Plus Reward</b><small><?= $points > 0 ? $t('Dự kiến tích ', 'Earn an estimated ') . number_format($points, 0, '.', $en ? ',' : '.') . $t(' điểm thành viên', ' member points') : $t('Khám phá quyền lợi thành viên', 'Explore member benefits') ?></small></span><?= $icon('chevron-right') ?></a>
     <?php if ($points > 0): ?><small class="at-reward-note"><?= $t('Điểm thực nhận tính theo số tiền đã thanh toán.', 'Actual points depend on the paid booking amount.') ?></small><?php endif ?>
    </div>
   </article><?php endforeach ?>
  </div><div class="at-slider-controls" hidden><span><?= $t('Vuốt để khám phá thêm', 'Swipe to explore') ?></span><div><button type="button" data-slide="-1" aria-controls="autumn-tour-track" aria-label="<?= $t('Tour trước', 'Previous tour') ?>"><?= $icon('arrow-left') ?></button><button type="button" data-slide="1" aria-controls="autumn-tour-track" aria-label="<?= $t('Tour tiếp theo', 'Next tour') ?>"><?= $icon('arrow-right') ?></button></div></div><?php else: ?><div class="at-empty"><span class="at-empty-icon"><?= $icon('calendar-heart') ?></span><div><h3><?= $t('Chưa tìm thấy lịch khởi hành phù hợp', 'No matching departures yet') ?></h3><p><?= $t('Bạn vẫn có thể lên kế hoạch từ hôm nay. Travel Plus sẽ tư vấn điểm đến và kiểm tra lịch đi theo nhu cầu của bạn.', 'You can still start planning today. Travel Plus can help choose a destination and check dates for your trip.') ?></p></div><a class="at-btn" href="<?= esc($contactUrl, 'attr') ?>"><?= $t('Nhờ tìm lịch phù hợp', 'Help me find dates') ?> <?= $icon('arrow-right') ?></a></div><?php endif ?>
  <p class="at-price-note"><?= $icon('info-circle') ?> <?= $t('Giá và tình trạng chỗ có thể thay đổi theo ngày khởi hành. Xem điều kiện chi tiết trong từng tour.', 'Prices and availability vary by departure. See individual tours for full conditions.') ?></p>
 </div></section>
 <section class="at-container at-section at-help" id="autumn-advice"><div class="at-help-photo"><img src="<?= base_url('assets/images/destination/sa-pa.webp') ?>" alt="<?= $t('Khung cảnh núi non Sa Pa', 'Mountain scenery in Sa Pa') ?>" loading="lazy" width="800" height="533"><span><?= $t('Chuyến đi đẹp nhất?<br>Là chuyến đi hợp với bạn.', 'The best journey?<br>The one that fits you.') ?></span></div><div class="at-help-copy"><p class="at-kicker"><?= $t('TOUR THIẾT KẾ RIÊNG MÙA THU', 'TAILOR-MADE AUTUMN JOURNEYS') ?></p><h2><?= $t('Mùa thu của bạn.<br>Hành trình theo cách bạn muốn.', 'Your autumn.<br>Your own way to travel.') ?></h2><p><?= $t('Một chuyến ngắm lá đỏ cùng gia đình, kỳ nghỉ riêng với nhóm bạn hay hành trình gắn kết cả công ty. Bạn chọn điểm đến và nhịp đi, Travel Plus thiết kế lịch trình mùa thu phù hợp.', 'A foliage trip with family, a private escape with friends or a company retreat. Choose your destination and pace; Travel Plus will tailor your autumn itinerary.') ?></p><ul><li><?= $icon('check-circle-fill') ?><?= $t('Tư vấn theo ngân sách và thời gian của bạn', 'Advice based on your budget and time') ?></li><li><?= $icon('check-circle-fill') ?><?= $t('Trao đổi rõ chi phí, dịch vụ và điều kiện tour', 'Clear costs, services and tour conditions') ?></li><li><?= $icon('check-circle-fill') ?><?= $t('Hướng dẫn chuẩn bị hồ sơ cho chuyến đi', 'Help preparing your travel documents') ?></li></ul><a class="at-btn" href="<?= esc($customTourUrl, 'attr') ?>"><?= $icon('magic') ?> <?= $t('Thiết kế tour mùa thu riêng', 'Design my autumn journey') ?></a><a class="at-help-phone" href="tel:<?= esc($settings->get('hotline_e164'), 'attr') ?>"><?= $t('Hoặc gọi ', 'Or call ') ?><strong><?= esc($settings->phoneDisplay($currentLocale)) ?></strong></a></div></section>
 <section class="at-container at-section at-faq" id="autumn-faq"><div><p class="at-kicker"><?= $t('GIẢI ĐÁP TRƯỚC CHUYẾN ĐI', 'BEFORE YOU BOOK') ?></p><h2><?= $t('Bạn đang<br>băn khoăn gì?', 'What’s on<br>your mind?') ?></h2><p><?= $t('Một vài điều nên biết để chọn tour dễ hơn.', 'A few answers to make choosing easier.') ?></p></div><div class="at-faq-list">
 <?php $faqs = [
 [$t('Làm sao chọn lịch đi đúng mùa lá đẹp?', 'How do I choose dates for autumn colours?'), $t('Màu lá thay đổi theo điểm đến và thời tiết từng năm. Hãy cho tư vấn viên biết nơi bạn muốn đến để được kiểm tra lịch trình và thời điểm phù hợp; cảnh sắc thực tế không thể bảo đảm giống ảnh.', 'Foliage depends on destination and weather each year. Share your preferred destination with our team to check the itinerary and timing; actual scenery cannot be guaranteed to match photographs.')],
 [$t('Giá tour đã bao gồm những gì?', 'What is included in the tour price?'), $t('Mỗi tour có phần dịch vụ bao gồm và không bao gồm riêng. Mở “Xem tour” để kiểm tra vé máy bay, khách sạn, bữa ăn, visa và phụ thu trước khi đặt.', 'Each tour has its own inclusions and exclusions. Open “View tour” to check flights, hotels, meals, visas and supplements before booking.')],
 [$t('Có thể đi riêng cùng gia đình hoặc công ty không?', 'Can I arrange a private family or company trip?'), $t('Bạn có thể gửi yêu cầu về số khách, điểm đến, ngày đi và ngân sách qua trang liên hệ hoặc Zalo. Travel Plus sẽ trao đổi phương án và báo giá theo nhu cầu.', 'Send your group size, destination, dates and budget via our contact page or Zalo. Travel Plus will discuss options and a tailored quote.')],
 [$t('Chưa có visa thì có đặt tour được không?', 'Can I enquire before I have a visa?'), $t('Hãy liên hệ tư vấn trước khi thanh toán để kiểm tra yêu cầu hồ sơ, thời gian xử lý và điều kiện áp dụng cho tour bạn chọn.', 'Contact our team before paying to check document requirements, processing time and the conditions for your chosen tour.')],
 ]; foreach ($faqs as [$question, $answer]): ?><details><summary><?= esc($question) ?><span aria-hidden="true">+</span></summary><p><?= esc($answer) ?></p></details><?php endforeach ?>
 </div></section>
 <div class="at-mobile-contact"><a href="tel:<?= esc($settings->get('hotline_e164'), 'attr') ?>"><?= $icon('telephone') ?> <?= $t('Gọi tư vấn', 'Call us') ?></a><a href="<?= esc($settings->get('zalo_url'), 'attr') ?>"><?= $icon('chat-dots') ?> <?= $t('Chat Zalo', 'Zalo chat') ?></a><a href="#autumn-tours"><?= $t('Chọn tour', 'Find a tour') ?> <?= $icon('arrow-right') ?></a></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(frontend_asset_url('assets/js/autumn.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>
