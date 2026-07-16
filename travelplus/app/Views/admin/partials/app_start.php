<?php
$adminSection = is_string($adminSection ?? null) ? $adminSection : 'dashboard';
$authUser = session()->get('auth_user');
$displayName = is_array($authUser) ? trim((string) ($authUser['full_name'] ?? $authUser['email'] ?? 'Admin')) : 'Admin';
$displayRole = is_array($authUser) && ! empty($authUser['is_admin']) ? 'Quản trị viên' : 'Nhân sự vận hành';

$navGroups = [
    [
        'key' => 'overview',
        'label' => 'Tổng quan',
        'items' => [
            ['key' => 'dashboard', 'label' => 'Bảng điều khiển', 'url' => site_url('admin')],
            ['key' => 'analytics', 'label' => 'Analytics', 'url' => site_url('admin/analytics')],
        ],
    ],
    [
        'key' => 'sales',
        'label' => 'Kinh doanh',
        'items' => [
            ['key' => 'leads', 'label' => 'CRM khách hàng', 'url' => site_url('admin/leads')],
            ['key' => 'bookings', 'label' => 'Booking', 'url' => site_url('admin/bookings')],
            ['key' => 'booking_emails', 'label' => 'Email booking', 'url' => site_url('admin/booking-emails')],
            ['key' => 'promotion_codes', 'label' => 'Mã khuyến mãi', 'url' => site_url('admin/promotion-codes')],
        ],
    ],
    [
        'key' => 'content',
        'label' => 'Nội dung',
        'items' => [
            ['key' => 'tours', 'label' => 'Quản lý tour', 'url' => site_url('admin/tours')],
            ['key' => 'blogs', 'label' => 'Bài viết', 'url' => site_url('admin/blogs')],
            ['key' => 'reviews', 'label' => 'Đánh giá', 'url' => site_url('admin/reviews')],
            ['key' => 'media_audit', 'label' => 'Tối ưu media', 'url' => site_url('admin/media-audit')],
        ],
    ],
    [
        'key' => 'system',
        'label' => 'Hệ thống',
        'items' => [
            ['key' => 'website_settings', 'label' => 'Cấu hình website', 'url' => site_url('admin/website-settings')],
            ['key' => 'system_health', 'label' => 'Kiểm tra hệ thống', 'url' => site_url('admin/system-health')],
            ['key' => 'system_logs', 'label' => 'Nhật ký lỗi', 'url' => site_url('admin/system-logs')],
            ['key' => 'users', 'label' => 'Người dùng', 'url' => site_url('admin/users')],
        ],
    ],
];

$sectionMeta = [
    'dashboard' => ['label' => 'Tổng quan', 'hint' => 'Theo dõi việc cần xử lý và dữ liệu chính.'],
    'analytics' => ['label' => 'Analytics', 'hint' => 'Hiểu hành vi truy cập và hiệu quả nội dung.'],
    'leads' => ['label' => 'CRM leads', 'hint' => 'Quản lý lead, nguồn vào và trạng thái tư vấn.'],
    'bookings' => ['label' => 'Đơn đặt tour', 'hint' => 'Theo dõi booking, thanh toán và đối soát.'],
    'booking_emails' => ['label' => 'Email booking', 'hint' => 'Gửi email nhắc thanh toán có kiểm soát.'],
    'tours' => ['label' => 'Tour', 'hint' => 'Quản lý tour, giá và lịch khởi hành.'],
    'promotion_codes' => ['label' => 'Mã khuyến mãi', 'hint' => 'Tạo và kiểm soát mã giảm giá.'],
    'reviews' => ['label' => 'Đánh giá', 'hint' => 'Duyệt và quản lý review khách hàng.'],
    'blogs' => ['label' => 'Bài viết', 'hint' => 'Quản lý nội dung, SEO và xuất bản.'],
    'users' => ['label' => 'Người dùng', 'hint' => 'Quản lý tài khoản và quyền truy cập.'],
    'media_audit' => ['label' => 'Kiểm tra media', 'hint' => 'Rà soát file hình ảnh đang dùng và file thừa.'],
    'system_health' => ['label' => 'Trạng thái hệ thống', 'hint' => 'Kiểm tra cấu hình và khả năng vận hành trên hosting.'],
    'system_logs' => ['label' => 'Nhật ký lỗi', 'hint' => 'Theo dõi lỗi gần đây mà không cần mở File Manager.'],
    'website_settings' => ['label' => 'Cấu hình website', 'hint' => 'Quản lý thông tin liên hệ công khai dùng chung.'],
];
$currentSectionMeta = $sectionMeta[$adminSection] ?? ['label' => 'Admin', 'hint' => 'Bảng điều khiển nội bộ.'];
?>
<div class="admin-app__layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar__brand">
            <a href="<?= site_url('admin') ?>">Travel Plus Admin</a>
            <button type="button" class="admin-sidebar__close" data-admin-sidebar-close aria-label="Đóng menu">×</button>
        </div>

        <div class="admin-sidebar__section admin-sidebar__section--navigation">
            <nav class="admin-sidebar__nav" aria-label="Admin navigation">
                <?php foreach ($navGroups as $group): ?>
                    <?php
                    $groupKeys = array_column($group['items'], 'key');
                    $isCurrentGroup = in_array($adminSection, $groupKeys, true);
                    $groupLabelId = 'adminNavGroup-' . $group['key'];
                    ?>
                    <section class="admin-sidebar__group<?= $isCurrentGroup ? ' is-current' : '' ?>" aria-labelledby="<?= esc($groupLabelId, 'attr') ?>">
                        <div class="admin-sidebar__group-label" id="<?= esc($groupLabelId, 'attr') ?>"><?= esc($group['label']) ?></div>
                        <div class="admin-sidebar__group-links">
                            <?php foreach ($group['items'] as $item): ?>
                                <a class="admin-sidebar__link<?= $adminSection === $item['key'] ? ' is-active' : '' ?>" href="<?= esc($item['url'], 'attr') ?>"<?= $adminSection === $item['key'] ? ' aria-current="page"' : '' ?>>
                                    <span><?= esc($item['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="admin-sidebar__section">
            <div class="admin-sidebar__label">Truy cập nhanh</div>
            <div class="admin-sidebar__stack">
                <a class="admin-sidebar__sub-link" href="<?= site_url('/') ?>">Mở website</a>
                <a class="admin-sidebar__sub-link" href="<?= site_url('admin/bookings/export') ?>">Xuất bookings</a>
            </div>
        </div>

        <div class="admin-sidebar__profile">
            <strong><?= esc($displayName) ?></strong>
            <span><?= esc($displayRole) ?></span>
        </div>
    </aside>

    <div class="admin-app__backdrop" data-admin-sidebar-close></div>

    <div class="admin-app__content">
        <div class="admin-topbar">
            <button type="button" class="admin-topbar__toggle" data-admin-sidebar-open aria-label="Mở menu">☰</button>
            <div class="admin-topbar__meta">
                <span class="admin-topbar__eyebrow">Travel Plus Admin</span>
                <strong><?= esc($currentSectionMeta['label']) ?> · <?= esc($currentSectionMeta['hint']) ?></strong>
            </div>
        </div>
