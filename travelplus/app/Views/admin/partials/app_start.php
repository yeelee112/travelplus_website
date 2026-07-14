<?php
$adminSection = is_string($adminSection ?? null) ? $adminSection : 'dashboard';
$authUser = session()->get('auth_user');
$displayName = is_array($authUser) ? trim((string) ($authUser['full_name'] ?? $authUser['email'] ?? 'Admin')) : 'Admin';
$displayRole = is_array($authUser) && ! empty($authUser['is_admin']) ? 'Quản trị viên' : 'Nhân sự vận hành';

$navItems = [
    ['key' => 'dashboard', 'label' => 'Tổng quan', 'url' => site_url('admin')],
    ['key' => 'analytics', 'label' => 'Analytics', 'url' => site_url('admin/analytics')],
    ['key' => 'leads', 'label' => 'CRM leads', 'url' => site_url('admin/leads')],
    ['key' => 'bookings', 'label' => 'Đơn đặt tour', 'url' => site_url('admin/bookings')],
    ['key' => 'tours', 'label' => 'Tour', 'url' => site_url('admin/tours')],
    ['key' => 'promotion_codes', 'label' => 'Mã khuyến mãi', 'url' => site_url('admin/promotion-codes')],
    ['key' => 'reviews', 'label' => 'Đánh giá', 'url' => site_url('admin/reviews')],
    ['key' => 'blogs', 'label' => 'Bài viết', 'url' => site_url('admin/blogs')],
    ['key' => 'users', 'label' => 'Người dùng', 'url' => site_url('admin/users')],
    ['key' => 'media_audit', 'label' => 'Kiểm tra media', 'url' => site_url('admin/media-audit')],
];
?>
<div class="admin-app__layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar__brand">
            <a href="<?= site_url('admin') ?>">Travel Plus Admin</a>
            <button type="button" class="admin-sidebar__close" data-admin-sidebar-close aria-label="Đóng menu">×</button>
        </div>

        <div class="admin-sidebar__section">
            <div class="admin-sidebar__label">Điều hướng</div>
            <nav class="admin-sidebar__nav" aria-label="Admin navigation">
                <?php foreach ($navItems as $item): ?>
                    <a class="admin-sidebar__link<?= $adminSection === $item['key'] ? ' is-active' : '' ?>" href="<?= esc($item['url'], 'attr') ?>">
                        <span><?= esc($item['label']) ?></span>
                    </a>
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
                <span class="admin-topbar__eyebrow">Bảng điều khiển nội bộ</span>
                <strong>Ưu tiên thao tác nhanh, dữ liệu rõ và ít nhiễu.</strong>
            </div>
        </div>
