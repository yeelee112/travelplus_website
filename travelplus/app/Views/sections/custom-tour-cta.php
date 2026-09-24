<?php $customLocale = service('request')->getLocale() ?: 'vi'; ?>
<section class="container my-5">
    <div style="padding:clamp(24px,4vw,48px);border-radius:24px;background:#edf3eb;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;color:#153e39">
        <div style="max-width:650px">
            <p style="font-size:12px;letter-spacing:1.5px;font-weight:700;margin-bottom:12px">TRAVEL PLUS · <?= $customLocale === 'en' ? 'TAILOR-MADE JOURNEYS' : 'TOUR THEO YÊU CẦU' ?></p>
            <h2 style="font-size:clamp(26px,3vw,38px);color:#153e39"><?= $customLocale === 'en' ? 'Your journey, your way.' : 'Chuyến đi của bạn, theo cách bạn muốn.' ?></h2>
            <p style="margin:14px 0 0;line-height:1.7"><?= $customLocale === 'en' ? 'Share your destination, timing and budget. We will help shape a journey for your family, friends or team.' : 'Chia sẻ điểm đến, thời gian và ngân sách. TravelPlus tư vấn hành trình riêng cho gia đình, nhóm bạn hoặc doanh nghiệp.' ?></p>
        </div>
        <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('customTour', $customLocale), 'attr') ?>" style="display:inline-flex;align-items:center;min-height:48px;padding:14px 22px;border-radius:12px;background:#176653;color:white;font-weight:600"><?= $customLocale === 'en' ? 'Design your trip ↗' : 'Thiết kế chuyến đi của bạn ↗' ?></a>
    </div>
</section>
