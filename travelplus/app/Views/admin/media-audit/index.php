<?php
$stats = is_array($report['stats'] ?? null) ? $report['stats'] : [];
$optimizable = is_array($report['optimizable'] ?? null) ? $report['optimizable'] : [];
$responsiveMissing = is_array($report['responsive_missing'] ?? null) ? $report['responsive_missing'] : [];
$orphans = is_array($report['orphans'] ?? null) ? $report['orphans'] : [];
$formatBytes = static function (int $bytes): string {
    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
    }

    return number_format($bytes / 1024, 0, ',', '.') . ' KB';
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Kiểm tra media</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fa; color:#172033; }
        .media-page { max-width:1320px; margin:28px auto; padding:0 16px; display:grid; gap:18px; }
        .media-panel { background:#fff; border:1px solid #dfe6ee; border-radius:8px; box-shadow:0 8px 24px rgba(24,39,75,.05); }
        .media-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; padding:24px; }
        .media-hero h1 { margin:0 0 7px; color:#0b1f38; font-size:28px; font-weight:800; line-height:1.2; }
        .media-hero p { max-width:820px; margin:0; color:#65748a; line-height:1.55; }
        .media-summary { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); border-top:1px solid #e5ebf2; }
        .media-stat { min-width:0; padding:18px 20px; border-right:1px solid #e5ebf2; }
        .media-stat:last-child { border-right:0; }
        .media-stat span { display:block; margin-bottom:7px; color:#69788d; font-size:12px; font-weight:800; text-transform:uppercase; }
        .media-stat strong { display:block; color:#0b1f38; font-size:27px; line-height:1; }
        .media-stat small { display:block; margin-top:7px; color:#77859a; }
        .media-stat.is-action strong { color:#006eae; }
        .media-stat.is-danger strong { color:#bd3434; }
        .media-section { padding:22px 24px 24px; }
        .media-section__head { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; margin-bottom:16px; }
        .media-section__head h2 { margin:0 0 5px; color:#0b1f38; font-size:21px; font-weight:800; }
        .media-section__head p { margin:0; color:#69788d; }
        .media-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; padding:11px 13px; border:1px solid #dce6ef; border-radius:8px; background:#f8fafc; }
        .media-toolbar__selection { color:#33445c; font-weight:700; }
        .media-toolbar__selection strong { color:#006eae; }
        .media-table { margin:0; }
        .media-table th { color:#69788d; font-size:12px; font-weight:800; text-transform:uppercase; white-space:nowrap; }
        .media-table td { vertical-align:middle; }
        .media-path { max-width:660px; color:#253851; font-family:Consolas,monospace; font-size:13px; overflow-wrap:anywhere; }
        .media-type { display:inline-flex; min-width:48px; justify-content:center; padding:4px 7px; border-radius:6px; background:#eaf4fb; color:#006eae; font-size:11px; font-weight:800; }
        .media-empty { padding:30px 16px; text-align:center; color:#69788d; }
        .media-danger-note { color:#9d3a3a; font-size:13px; }
        @media (max-width: 991px) {
            .media-summary { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .media-stat { border-bottom:1px solid #e5ebf2; }
        }
        @media (max-width: 767px) {
            .media-page { margin:16px auto; padding:0 12px; }
            .media-hero, .media-section { padding:18px; }
            .media-hero, .media-section__head, .media-toolbar { display:grid; }
            .media-hero h1 { font-size:24px; }
            .media-summary { grid-template-columns:1fr 1fr; }
            .media-stat { padding:15px; }
            .media-stat strong { font-size:23px; }
            .media-toolbar .btn { width:100%; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'media_audit']) ?>
<main class="media-page">
    <section class="media-panel">
        <div class="media-hero">
            <div>
                <h1>Kiểm tra và tối ưu media</h1>
                <p>Kiểm tra ảnh đang được blog và tour sử dụng, tối ưu ảnh JPG/PNG/WebP dung lượng lớn và nhận diện file không còn được database tham chiếu.</p>
            </div>
        </div>
        <div class="media-summary">
            <div class="media-stat"><span>Ảnh blog</span><strong><?= esc((string) ($stats['blog_referenced'] ?? 0)) ?></strong><small>đang tham chiếu</small></div>
            <div class="media-stat"><span>Ảnh tour</span><strong><?= esc((string) ($stats['tour_referenced'] ?? 0)) ?></strong><small>đang tham chiếu</small></div>
            <div class="media-stat"><span>File trên ổ đĩa</span><strong><?= esc((string) ((int) ($stats['blog_on_disk'] ?? 0) + (int) ($stats['tour_on_disk'] ?? 0))) ?></strong><small>blog và tour</small></div>
            <div class="media-stat is-action"><span>Có thể tối ưu</span><strong><?= esc((string) ($stats['optimizable_total'] ?? 0)) ?></strong><small><?= esc($formatBytes((int) ($stats['optimizable_bytes'] ?? 0))) ?></small></div>
            <div class="media-stat is-action"><span>Thiếu ảnh responsive</span><strong><?= esc((string) ($stats['responsive_missing_total'] ?? 0)) ?></strong><small>cho mobile và tablet</small></div>
            <div class="media-stat is-danger"><span>File thừa</span><strong><?= esc((string) ($stats['orphan_total'] ?? 0)) ?></strong><small>không còn tham chiếu</small></div>
        </div>
    </section>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success mb-0"><?= esc((string) $success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-danger mb-0"><?= esc((string) $error) ?></div>
    <?php endif; ?>

    <section class="media-panel" id="optimize-media">
        <form class="media-section" method="post" action="<?= site_url('admin/media-audit/optimize') ?>" data-media-selection-form data-media-confirm="Tối ưu ảnh đã chọn sang WebP?">
            <?= csrf_field() ?>
            <div class="media-section__head">
                <div>
                    <h2>Ảnh đang dùng cần tối ưu</h2>
                    <p>Chỉ liệt kê JPG/PNG/WebP từ 300 KB chưa được kiểm tra hoặc vừa thay đổi. Mỗi lượt xử lý tối đa 30 ảnh.</p>
                </div>
            </div>

            <div class="media-toolbar">
                <div class="media-toolbar__selection">Đã chọn <strong data-media-selected-count>0</strong>/<?= esc((string) count($optimizable)) ?> ảnh</div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-media-select-all <?= $optimizable === [] ? 'disabled' : '' ?>>Chọn tất cả</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-media-clear <?= $optimizable === [] ? 'disabled' : '' ?>>Bỏ chọn</button>
                    <button class="btn btn-primary btn-sm" type="submit" data-media-optimize-submit disabled>Tối ưu ảnh đã chọn</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table media-table align-middle">
                    <thead><tr><th style="width:44px"></th><th>File</th><th style="width:90px">Loại</th><th style="width:130px">Dung lượng</th></tr></thead>
                    <tbody>
                    <?php if ($optimizable === []): ?>
                        <tr><td colspan="4" class="media-empty">Không có ảnh JPG/PNG/WebP lớn cần tối ưu.</td></tr>
                    <?php else: ?>
                        <?php foreach ($optimizable as $item): ?>
                            <tr data-media-row>
                                <td><input class="form-check-input" type="checkbox" name="files[]" value="<?= esc((string) $item['path'], 'attr') ?>" data-media-checkbox aria-label="Chọn ảnh <?= esc((string) $item['path'], 'attr') ?>"></td>
                                <td class="media-path"><?= esc((string) $item['path']) ?></td>
                                <td><span class="media-type"><?= esc((string) $item['type']) ?></span></td>
                                <td><?= esc($formatBytes((int) $item['size'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </section>

    <section class="media-panel" id="responsive-media">
        <form class="media-section" method="post" action="<?= site_url('admin/media-audit/generate-responsive') ?>" data-media-selection-form data-media-confirm="Tạo kích thước responsive cho ảnh đã chọn?">
            <?= csrf_field() ?>
            <div class="media-section__head">
                <div>
                    <h2>Ảnh cần kích thước responsive</h2>
                    <p>Tạo các bản 480px, 960px và 1440px để điện thoại không phải tải ảnh gốc quá lớn. Mỗi lượt xử lý tối đa 30 ảnh.</p>
                </div>
            </div>

            <div class="media-toolbar">
                <div class="media-toolbar__selection">Đã chọn <strong data-media-selected-count>0</strong>/<?= esc((string) count($responsiveMissing)) ?> ảnh</div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-media-select-all <?= $responsiveMissing === [] ? 'disabled' : '' ?>>Chọn tất cả</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-media-clear <?= $responsiveMissing === [] ? 'disabled' : '' ?>>Bỏ chọn</button>
                    <button class="btn btn-primary btn-sm" type="submit" data-media-optimize-submit disabled>Tạo ảnh responsive</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table media-table align-middle">
                    <thead><tr><th style="width:44px"></th><th>File</th><th style="width:90px">Loại</th><th style="width:180px">Kích thước còn thiếu</th><th style="width:130px">Dung lượng</th></tr></thead>
                    <tbody>
                    <?php if ($responsiveMissing === []): ?>
                        <tr><td colspan="5" class="media-empty">Tất cả ảnh tour và blog đã có kích thước responsive phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach ($responsiveMissing as $item): ?>
                            <tr data-media-row>
                                <td><input class="form-check-input" type="checkbox" name="files[]" value="<?= esc((string) $item['path'], 'attr') ?>" data-media-checkbox aria-label="Chọn ảnh <?= esc((string) $item['path'], 'attr') ?>"></td>
                                <td class="media-path"><?= esc((string) $item['path']) ?></td>
                                <td><span class="media-type"><?= esc((string) $item['type']) ?></span></td>
                                <td><?= esc(implode(', ', array_map(static fn ($width): string => (int) $width . 'px', (array) ($item['missing_widths'] ?? [])))) ?></td>
                                <td><?= esc($formatBytes((int) $item['size'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </section>

    <section class="media-panel">
        <div class="media-section">
            <div class="media-section__head">
                <div>
                    <h2>File không còn được tham chiếu</h2>
                    <p>Chỉ xóa khi chắc chắn các file này không còn cần dùng.</p>
                </div>
                <?php if ($orphans !== []): ?>
                    <form method="post" action="<?= site_url('admin/media-audit/delete-orphans') ?>" onsubmit="return confirm('Xóa toàn bộ file thừa đang hiển thị?');">
                        <?= csrf_field() ?>
                        <?php foreach ($orphans as $orphan): ?>
                            <input type="hidden" name="files[]" value="<?= esc((string) $orphan['path'], 'attr') ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">Xóa tất cả file thừa</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table media-table align-middle">
                    <thead><tr><th>File</th><th style="width:130px">Dung lượng</th><th style="width:180px">Cập nhật</th></tr></thead>
                    <tbody>
                    <?php if ($orphans === []): ?>
                        <tr><td colspan="3" class="media-empty">Không có file thừa trong phạm vi đang quản lý.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orphans as $orphan): ?>
                            <tr>
                                <td class="media-path"><?= esc((string) $orphan['path']) ?></td>
                                <td><?= esc($formatBytes((int) $orphan['size'])) ?></td>
                                <td><?= esc((string) $orphan['modified_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($orphans !== []): ?><div class="media-danger-note mt-2">Xóa file là thao tác không thể hoàn tác.</div><?php endif; ?>
        </div>
    </section>
</main>
<?= view('admin/partials/app_end') ?>
<script>
(function () {
    document.querySelectorAll('[data-media-selection-form]').forEach((form) => {
        const checkboxes = Array.from(form.querySelectorAll('[data-media-checkbox]'));
        const rows = Array.from(form.querySelectorAll('[data-media-row]'));
        const count = form.querySelector('[data-media-selected-count]');
        const submit = form.querySelector('[data-media-optimize-submit]');

        function update() {
            const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
            if (count) count.textContent = String(selected);
            if (submit) submit.disabled = selected === 0;
            rows.forEach((row) => {
                const checkbox = row.querySelector('[data-media-checkbox]');
                row.classList.toggle('table-primary', Boolean(checkbox && checkbox.checked));
            });
        }

        form.querySelector('[data-media-select-all]')?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => { checkbox.checked = true; });
            update();
        });
        form.querySelector('[data-media-clear]')?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => { checkbox.checked = false; });
            update();
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', update));
        form.addEventListener('submit', (event) => {
            const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
            const confirmation = form.dataset.mediaConfirm || 'Xử lý ảnh đã chọn?';
            if (selected === 0 || !window.confirm(`${confirmation} (${Math.min(selected, 30)} ảnh)`)) {
                event.preventDefault();
            }
        });
        update();
    });
})();
</script>
</body>
</html>
