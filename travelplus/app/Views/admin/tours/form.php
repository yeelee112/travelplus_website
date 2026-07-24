<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tour Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1220px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:22px; }
        .section-title { font-size:18px; font-weight:700; margin:0 0 14px; }
        label { font-weight:600; margin-bottom:6px; }
        textarea { min-height:105px; }
        .help { color:#6b778c; font-size:13px; }
        .repeat-item { border:1px solid #e5ebf2; border-radius:14px; padding:16px; background:#fbfcfe; margin-bottom:12px; position:relative; }
        .repeat-remove { position:absolute; top:14px; right:14px; }
        .repeat-duplicate { position:absolute; top:14px; right:94px; }
        .repeat-drag { position:absolute; top:14px; left:14px; cursor:grab; user-select:none; }
        .repeat-item.is-dragging { opacity:.55; border-style:dashed; }
        .repeat-item.is-sortable { padding-left:58px; }
        .new-country-fields, .new-province-fields { display:none; }
        .repeat-item.is-new-country .new-country-fields, .repeat-item.is-new-province .new-province-fields { display:flex; }
        .rich-editor-wrap { border:1px solid #d8dee6; border-radius:8px; background:#fff; overflow:hidden; }
        .rich-editor-toolbar { display:flex; gap:6px; padding:8px; border-bottom:1px solid #edf1f5; background:#f8fafc; }
        .rich-editor-toolbar button { border:1px solid #d8dee6; background:#fff; border-radius:6px; padding:4px 9px; font-weight:700; }
        .rich-editor { min-height:118px; padding:10px 12px; outline:0; }
        .form-section { border:1px solid #e6ebf0; border-radius:18px; background:#fff; padding:22px; margin-bottom:18px; scroll-margin-top:120px; }
        .section-meta { color:#6b778c; font-size:12px; margin:-4px 0 12px; }
        .tour-form-nav { position:sticky; top:12px; z-index:10; background:rgba(244,246,248,.92); backdrop-filter:blur(8px); padding:10px 12px; border:1px solid #e6ebf0; border-radius:16px; margin-bottom:16px; }
        .tour-form-nav .nav { gap:8px; flex-wrap:wrap; }
        .tour-form-nav .nav-link { border:1px solid #d9e2ec; border-radius:999px; padding:7px 12px; color:#334155; font-weight:600; background:#fff; font-size:13px; }
        .tour-form-nav .nav-link:hover { background:#f8fafc; color:#0f172a; }
        .sticky-action-bar { position:sticky; bottom:14px; z-index:20; display:flex; justify-content:space-between; align-items:center; gap:16px; margin-top:20px; padding:14px 18px; background:rgba(255,255,255,.96); border:1px solid #dce4ec; border-radius:18px; box-shadow:0 16px 36px rgba(15,23,42,.10); }
        .sticky-action-bar .meta { color:#64748b; font-size:13px; }
        .toolbar-wrap { flex-wrap:wrap; }
        .summary-pills { display:flex; gap:8px; flex-wrap:wrap; }
        .summary-pill { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; font-size:12px; font-weight:600; }
        .subtle-card { border:1px dashed #d9e2ec; border-radius:14px; padding:14px 16px; background:#fbfdff; }
        .live-summary { display:grid; grid-template-columns:1.5fr 1fr; gap:10px; margin-top:12px; }
        .live-summary-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:14px; }
        .live-summary-card:last-child { display:none; }
        .live-summary-label { color:#64748b; font-size:12px; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
        .live-summary-value { font-size:15px; font-weight:700; color:#0f172a; }
        .live-summary-sub { color:#64748b; font-size:13px; margin-top:4px; }
        .metric-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
        .metric-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px; }
        .metric-box .num { display:block; font-size:22px; font-weight:700; line-height:1.1; color:#0f172a; }
        .metric-box .lbl { color:#64748b; font-size:12px; margin-top:4px; }
        .departure-generator { border:1px dashed #cbd5e1; border-radius:14px; background:#f8fafc; padding:16px; margin-bottom:16px; }
        .departure-generator .weekday-list { display:flex; flex-wrap:wrap; gap:8px; }
        .departure-generator .weekday-list label { display:inline-flex; align-items:center; gap:6px; margin:0; padding:7px 10px; border:1px solid #d9e2ec; border-radius:999px; background:#fff; font-size:13px; }
        .itinerary-importer { border:1px solid #cfeefa; border-radius:18px; background:linear-gradient(135deg,#f2fbff 0%,#fff 65%); padding:18px; margin-bottom:18px; }
        .itinerary-importer__head { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; flex-wrap:wrap; margin-bottom:12px; }
        .itinerary-importer__title { font-size:16px; font-weight:800; color:#0f172a; margin:0; }
        .itinerary-importer__hint { color:#64748b; font-size:13px; line-height:1.55; margin:4px 0 0; max-width:720px; }
        .itinerary-importer__controls { display:flex; gap:10px; align-items:end; flex-wrap:wrap; margin-bottom:12px; }
        .itinerary-importer__controls label { min-width:190px; margin:0; }
        .itinerary-importer__editor { min-height:190px; padding:14px 16px; border:1px solid #d8dee6; border-radius:12px; background:#fff; outline:0; line-height:1.65; }
        .itinerary-importer__editor:empty:before { content:attr(data-placeholder); color:#94a3b8; white-space:pre-line; }
        .itinerary-importer__editor:focus { border-color:#009cde; box-shadow:0 0 0 3px rgba(0,156,222,.14); }
        .itinerary-importer__editor p { margin:0 0 10px; }
        .itinerary-importer__editor ul, .itinerary-importer__editor ol { margin:0 0 10px 22px; padding:0; }
        .itinerary-importer__actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
        .itinerary-importer__preview { display:none; margin-top:14px; border:1px solid #d9edf5; border-radius:14px; background:#fff; overflow:hidden; }
        .itinerary-importer__preview.is-visible { display:block; }
        .itinerary-importer__preview-head { padding:12px 14px; background:#f8fcfe; border-bottom:1px solid #e3f2f7; color:#334155; font-size:13px; font-weight:800; }
        .itinerary-importer__preview-list { max-height:260px; overflow:auto; }
        .itinerary-importer__preview-item { padding:12px 14px; border-bottom:1px solid #eef5f8; }
        .itinerary-importer__preview-item:last-child { border-bottom:0; }
        .itinerary-importer__preview-day { color:#0ea5e9; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.04em; margin-bottom:4px; }
        .itinerary-importer__preview-title { color:#111827; font-weight:800; margin-bottom:4px; }
        .itinerary-importer__preview-desc { color:#64748b; font-size:13px; line-height:1.55; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .departure-row { padding-right:102px; }
        .departure-row .form-control, .departure-row .form-select { min-height:42px; }
        .section-title-row { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:14px; flex-wrap:wrap; }
        .section-count { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; font-size:13px; font-weight:600; }
        .media-preview { display:flex; align-items:center; gap:12px; padding:10px 12px; border:1px dashed #d9e2ec; border-radius:12px; background:#fff; min-height:92px; }
        .media-preview img { width:88px; height:64px; object-fit:cover; border-radius:10px; border:1px solid #e2e8f0; background:#f8fafc; }
        .media-preview-meta { color:#64748b; font-size:12px; }
        .accordion-toggle { display:inline-flex; align-items:center; gap:8px; border:1px solid #d9e2ec; border-radius:999px; padding:8px 12px; background:#fff; color:#334155; font-size:13px; font-weight:700; cursor:pointer; }
        .accordion-toggle .icon { font-size:12px; transition:transform .18s ease; }
        .form-section.is-collapsed .accordion-toggle .icon { transform:rotate(-90deg); }
        .accordion-content { overflow:hidden; transition:max-height .22s ease, opacity .18s ease; }
        .form-section.is-collapsed .accordion-content { max-height:0 !important; opacity:.0; pointer-events:none; margin-top:0 !important; }
        .lang-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .lang-tab { border:1px solid #d9e2ec; border-radius:999px; padding:9px 16px; font-weight:700; color:#334155; background:#fff; cursor:pointer; }
        .lang-tab.is-active { background:#172033; color:#fff; border-color:#172033; }
        .lang-panel { display:none; }
        .lang-panel.is-active { display:block; }
        .lang-actions { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
        .nav-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; }
        .nav-head .title { font-size:13px; font-weight:700; color:#334155; }
        .nav-head .text-muted { font-size:12px; }
        .draft-status { font-size:12px; color:#64748b; }
        .compact-links { display:flex; gap:8px; flex-wrap:wrap; }
        .compact-links .btn { padding:8px 12px; }
        @media (max-width: 991px) {
            .sticky-action-bar { flex-direction:column; align-items:stretch; }
            .tour-form-nav { position:static; }
            .live-summary { grid-template-columns:1fr; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'tours'; ?>
<?php
$formData = $formData ?? [];
$tourType = old('tour_type', $formData['tour_type'] ?? 'outbound');
$fv = static fn(string $key, $default = '') => old($key, $formData[$key] ?? $default);
$destinationsRows = old('destinations') ?: ($formData['destinations'] ?? []);
$departureRows = old('departures') ?: ($formData['departures'] ?? []);
if ($departureRows === []) {
    $departureRows = [[
        'departure_date' => $fv('departure_date'),
        'available_slots' => $fv('available_slots'),
        'price' => $fv('departure_price', $fv('base_price')),
        'price_up' => $fv('price_up'),
        'status' => $fv('departure_status', 'open'),
    ]];
}
$itineraryRows = old('itinerary_days') ?: ($formData['itinerary_days'] ?? []);
$mediaRows = old('media') ?: ($formData['media'] ?? []);
$mediaUploadLimitMb = (int) ($mediaUploadLimitMb ?? 2);
$mediaUploadLimitBytes = (int) ($mediaUploadLimitBytes ?? ($mediaUploadLimitMb * 1024 * 1024));
$postMaxBytes = (int) ($postMaxBytes ?? 0);
$faqRows = old('faqs') ?: ($formData['faqs'] ?? []);
$includedRows = old('included_items') ?: ($formData['included_items'] ?? []);
$excludedRows = old('excluded_items') ?: ($formData['excluded_items'] ?? []);
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= esc($pageTitle ?? 'Form tour') ?></h1>
                <p class="text-muted mb-0"><?= esc($pageDesc ?? '') ?></p>
            </div>
            <div class="compact-links">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Quay lại danh sách</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?><div><?= esc($error) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="alert alert-warning d-none" id="draftRestoreBar">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <div class="fw-semibold">Có bản nháp cục bộ chưa khôi phục.</div>
                    <div class="small text-muted" id="draftRestoreTime"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearDraftButton">Xóa nháp</button>
                    <button type="button" class="btn btn-sm btn-primary" id="restoreDraftButton">Khôi phục nháp</button>
                </div>
            </div>
        </div>

        <div class="tour-form-nav">
            <div class="nav-head">
                <div>
                    <div class="title">Điều hướng nhanh</div>
                    <div class="text-muted">Nhảy nhanh giữa các nhóm thông tin.</div>
                </div>
                <div class="summary-pills">
                    <span class="summary-pill">Loại tour: <?= esc(ucfirst($tourType)) ?></span>
                    <span class="summary-pill">Trạng thái: <?= esc((string) $fv('status', 'draft')) ?></span>
                </div>
            </div>
            <div class="nav mt-3">
                <a class="nav-link" href="#section-main">Thông tin chính</a>
                <a class="nav-link" href="#section-locations">Điểm đi/đến</a>
                <a class="nav-link" href="#section-destinations">Điểm đến</a>
                <a class="nav-link" href="#section-content-vi">Nội dung</a>
                <a class="nav-link" href="#section-seo">SEO</a>
                <a class="nav-link" href="#section-departure">Khởi hành</a>
                <a class="nav-link" href="#section-itinerary">Lịch trình</a>
                <a class="nav-link" href="#section-media">Hình ảnh</a>
                <a class="nav-link" href="#section-inclusions">Chi tiết giá</a>
            </div>
            <div class="draft-status mt-2" id="draftStatusText">Tự lưu cục bộ: chưa có cập nhật mới.</div>
        </div>

        <div class="live-summary mb-3">
            <div class="live-summary-card">
                <div class="live-summary-label">Tóm tắt nhanh</div>
                <div class="live-summary-value" id="summaryName"><?= esc($fv('name_vi', 'Chưa có tên tour')) ?></div>
                <div class="live-summary-sub" id="summaryMeta">
                    <?= esc($fv('code', 'Chưa có mã')) ?> · <?= esc((string) $fv('duration_days', '5')) ?> ngày / <?= esc((string) $fv('duration_nights', '4')) ?> đêm
                </div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Thông tin bán</div>
                <div class="live-summary-value" id="summaryPrice"><?= esc($fv('base_price') !== '' ? number_format((int) $fv('base_price'), 0, ',', '.') . ' đ' : 'Chưa có giá') ?></div>
                <div class="live-summary-sub" id="summaryStatus"><?= esc(ucfirst((string) $fv('status', 'draft'))) ?> · <?= esc(ucfirst($tourType)) ?></div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Cấu trúc nội dung</div>
                <div class="metric-list">
                    <div class="metric-box"><span class="num" id="metricDestinations"><?= esc((string) count($destinationsRows)) ?></span><span class="lbl">Điểm đến</span></div>
                    <div class="metric-box"><span class="num" id="metricDepartures"><?= esc((string) count($departureRows)) ?></span><span class="lbl">Lịch khởi hành</span></div>
                    <div class="metric-box"><span class="num" id="metricItinerary"><?= esc((string) count($itineraryRows)) ?></span><span class="lbl">Ngày lịch trình</span></div>
                    <div class="metric-box"><span class="num" id="metricMedia"><?= esc((string) count($mediaRows)) ?></span><span class="lbl">Hình ảnh</span></div>
                    <div class="metric-box"><span class="num" id="metricFaq"><?= esc((string) count($faqRows)) ?></span><span class="lbl">FAQ</span></div>
                </div>
            </div>
        </div>

        <form method="post" action="<?= esc($formAction) ?>" enctype="multipart/form-data" id="tourForm">
            <?= csrf_field() ?>
            <section id="section-main" class="form-section">
            <h2 class="section-title">Thông tin chính</h2>
            <div class="section-meta">Thông tin nền của tour, giá cơ bản, trạng thái hiển thị và giới hạn khách.</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Loại tour</label>
                    <select name="tour_type" class="form-select" required>
                        <option value="outbound" <?= $tourType === 'outbound' ? 'selected' : '' ?>>Tour nước ngoài</option>
                        <option value="inbound" <?= $tourType === 'inbound' ? 'selected' : '' ?>>Tour trong nước</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Danh mục</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>" <?= (string) $fv('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                #<?= esc($category['id']) ?> - <?= esc($category['name']) ?> (<?= esc($category['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label>Mã tour</label><input name="code" class="form-control" value="<?= esc($fv('code')) ?>"></div>
                <div class="col-md-2"><label>SKU</label><input name="sku" class="form-control" value="<?= esc($fv('sku')) ?>"></div>
                <div class="col-md-2"><label>Số ngày</label><input type="number" min="1" name="duration_days" class="form-control" value="<?= esc($fv('duration_days', '5')) ?>" required></div>
                <div class="col-md-2"><label>Số đêm</label><input type="number" min="0" name="duration_nights" class="form-control" value="<?= esc($fv('duration_nights', '4')) ?>" required></div>
                <div class="col-md-2"><label>Số khách tối thiểu</label><input type="number" min="0" name="min_travelers" class="form-control" value="<?= esc($fv('min_travelers')) ?>"></div>
                <div class="col-md-2"><label>Số khách tối đa</label><input type="number" min="0" name="max_travelers" class="form-control" value="<?= esc($fv('max_travelers', '15')) ?>"></div>
                <div class="col-md-3"><label>Giá người lớn</label><input type="number" min="0" name="base_price" class="form-control" value="<?= esc($fv('base_price')) ?>"></div>
                <div class="col-md-3"><label>Giá khuyến mãi</label><input type="number" min="0" name="sale_price" class="form-control" value="<?= esc($fv('sale_price')) ?>"></div>
                  <div class="col-md-3">
                    <label>Phụ thu phòng đơn</label>
                    <input type="number" min="0" name="single_room_supplement" class="form-control" value="<?= esc($fv('single_room_supplement')) ?>">
                    <div class="help">Khoản phụ thu áp dụng khi khách yêu cầu ở phòng riêng trong suốt hành trình.</div>
                  </div>
                <div class="col-md-3">
                    <label>Tỷ lệ giá trẻ em</label>
                    <input type="number" min="0" max="1" step="0.01" name="child_price_rate" class="form-control" value="<?= esc($fv('child_price_rate', '0.85')) ?>">
                    <div class="help">0.85 = 85% giá người lớn</div>
                </div>
                <div class="col-md-3">
                    <label>Tỷ lệ giá em bé</label>
                    <input type="number" min="0" max="1" step="0.01" name="infant_price_rate" class="form-control" value="<?= esc($fv('infant_price_rate', '0.25')) ?>">
                    <div class="help">0.25 = 25% giá người lớn</div>
                </div>
                <input type="hidden" name="thumbnail" value="<?= esc($fv('thumbnail'), 'attr') ?>">
                <div class="col-md-3">
                    <label>Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= $fv('status', 'draft') === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="published" <?= $fv('status') === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" <?= (int) $fv('is_featured') === 1 ? 'checked' : '' ?>>
                        <span class="form-check-label">Tour nổi bật</span>
                    </label>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="is_promotion" value="1" class="form-check-input" <?= (int) $fv('is_promotion') === 1 ? 'checked' : '' ?>>
                        <span class="form-check-label">Hiển thị ở khuyến mãi trang chủ</span>
                    </label>
                </div>
                <div class="col-md-3"><label>Nhãn khuyến mãi</label><input name="promotion_badge" class="form-control" value="<?= esc($fv('promotion_badge', 'Tour khuyến mãi')) ?>"></div>
                <div class="col-md-3"><label>Thời điểm kết thúc ưu đãi</label><input type="datetime-local" name="promotion_ends_at" class="form-control" value="<?= esc($fv('promotion_ends_at')) ?>"></div>
                <div class="col-md-3"><label>Thứ tự khuyến mãi</label><input type="number" name="promotion_sort" class="form-control" value="<?= esc($fv('promotion_sort', '0')) ?>"></div>
            </div>
            </section>

            <section id="section-locations" class="form-section">
            <h2 class="section-title">Điểm đi và điểm đến chính</h2>
            <div class="section-meta">Điểm khởi hành và điểm đến chính dùng cho phân loại, breadcrumb và filter.</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Điểm đón / khởi hành</label>
                    <select name="departure_location_id" class="form-select" required>
                        <option value="">-- Chọn điểm đón --</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= esc($location['id']) ?>" <?= (string) $fv('departure_location_id') === (string) $location['id'] ? 'selected' : '' ?>>
                                #<?= esc($location['id']) ?> - <?= esc($location['name']) ?> (<?= esc($location['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Điểm đến chính</label>
                    <select name="primary_destination_id" class="form-select">
                        <option value="">-- Không bắt buộc --</option>
                        <optgroup label="Quốc gia cho tour nước ngoài" data-tour-type="outbound">
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= esc($country['id']) ?>" data-tour-type="outbound" <?= (string) $fv('primary_destination_id') === (string) $country['id'] ? 'selected' : '' ?>>
                                    #<?= esc($country['id']) ?> - <?= esc($country['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Tỉnh/thành cho tour trong nước" data-tour-type="inbound">
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= esc($province['id']) ?>" data-tour-type="inbound" <?= (string) $fv('primary_destination_id') === (string) $province['id'] ? 'selected' : '' ?>>
                                    #<?= esc($province['id']) ?> - <?= esc($province['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            </section>

            <section id="section-destinations" class="form-section is-collapsed">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <h2 class="section-title">Danh sách điểm đến</h2>
                    <div class="section-meta mb-0">Tour nước ngoài: châu lục - quốc gia. Tour trong nước: vùng - tỉnh/thành.</div>
                </div>
                <div class="subtle-card">
                    <div class="fw-semibold small mb-1">Gợi ý</div>
                    <div class="small text-muted">Tour đi nhiều điểm nên thêm đủ tất cả điểm đến ở đây trước khi nhập lịch trình.</div>
                </div>
            </div>
            <div class="section-title-row">
                <div class="section-count">Số dòng: <span id="destinationCountBadge"><?= esc((string) count($destinationsRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
            </div>
            <div class="accordion-content">
            <div id="destinationRows">
                <?php foreach (array_values($destinationsRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item destination-row<?= ! empty($row['new_country_name_vi']) ? ' is-new-country' : '' ?><?= ! empty($row['new_province_name_vi']) ? ' is-new-province' : '' ?>">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                        <div class="row g-3 js-outbound-fields">
                            <div class="col-md-4">
                                <label>Châu lục</label>
                                <select name="destinations[<?= $index ?>][continent_id]" class="form-select js-continent-select">
                                    <option value="">-- Chọn châu lục --</option>
                                    <?php foreach ($continents as $continent): ?>
                                        <option value="<?= esc($continent['id']) ?>" <?= (string) ($row['continent_id'] ?? '') === (string) $continent['id'] ? 'selected' : '' ?>><?= esc($continent['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Quốc gia</label>
                                <select name="destinations[<?= $index ?>][country_id]" class="form-select js-country-select" data-selected="<?= esc((string) ($row['country_id'] ?? '')) ?>">
                                    <option value="">-- Chọn quốc gia có sẵn --</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label>Hoặc tạo quốc gia</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Tạo quốc gia mới</button></div>
                        </div>
                        <div class="row g-3 js-inbound-fields d-none">
                            <div class="col-md-4">
                                <label>Vùng miền</label>
                                <select name="destinations[<?= $index ?>][region_key]" class="form-select js-region-select">
                                    <option value="">-- Chọn vùng miền --</option>
                                    <?php foreach ($domesticRegions as $region): ?>
                                        <option value="<?= esc($region['key']) ?>" <?= (string) ($row['region_key'] ?? '') === (string) $region['key'] ? 'selected' : '' ?>><?= esc($region['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tỉnh / thành phố</label>
                                <select name="destinations[<?= $index ?>][province_id]" class="form-select js-province-select" data-selected="<?= esc((string) ($row['province_id'] ?? '')) ?>">
                                    <option value="">-- Chọn tỉnh/thành --</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label>Hoặc tạo tỉnh/thành</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-province">Tạo tỉnh/thành mới</button></div>
                        </div>
                        <div class="row g-3 mt-1 new-province-fields">
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_name_vi]" class="form-control" value="<?= esc((string) ($row['new_province_name_vi'] ?? '')) ?>" placeholder="Tên tỉnh/thành tiếng Việt"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_slug_vi]" class="form-control" value="<?= esc((string) ($row['new_province_slug_vi'] ?? '')) ?>" placeholder="slug vi"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_name_en]" class="form-control" value="<?= esc((string) ($row['new_province_name_en'] ?? '')) ?>" placeholder="Tên tỉnh/thành tiếng Anh"></div>
                            <div class="col-md-2"><input name="destinations[<?= $index ?>][new_province_slug_en]" class="form-control" value="<?= esc((string) ($row['new_province_slug_en'] ?? '')) ?>" placeholder="slug en"></div>
                            <div class="col-md-1"><input name="destinations[<?= $index ?>][new_province_code]" class="form-control" value="<?= esc((string) ($row['new_province_code'] ?? '')) ?>" placeholder="DN"></div>
                        </div>
                        <div class="row g-3 mt-1 new-country-fields">
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_name_vi]" class="form-control" value="<?= esc((string) ($row['new_country_name_vi'] ?? '')) ?>" placeholder="Tên quốc gia tiếng Việt"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_slug_vi]" class="form-control" value="<?= esc((string) ($row['new_country_slug_vi'] ?? '')) ?>" placeholder="slug vi"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_name_en]" class="form-control" value="<?= esc((string) ($row['new_country_name_en'] ?? '')) ?>" placeholder="Tên quốc gia tiếng Anh"></div>
                            <div class="col-md-2"><input name="destinations[<?= $index ?>][new_country_slug_en]" class="form-control" value="<?= esc((string) ($row['new_country_slug_en'] ?? '')) ?>" placeholder="slug en"></div>
                            <div class="col-md-1"><input name="destinations[<?= $index ?>][new_country_code]" class="form-control" value="<?= esc((string) ($row['new_country_code'] ?? '')) ?>" placeholder="CA"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addDestination">Thêm điểm đến</button>
            </div>
            </section>

            <section id="section-content-vi" class="form-section">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                <div>
                    <h2 class="section-title">Nội dung tour</h2>
                    <div class="section-meta mb-0">Nhập nội dung theo từng ngôn ngữ, nhưng thao tác trong cùng một block để đỡ cuộn dài.</div>
                </div>
                <div class="subtle-card small text-muted">Nên chốt nội dung tiếng Việt trước. Tiếng Anh có thể bổ sung sau, nhưng nên có tên và slug nếu cần SEO.</div>
            </div>
            <div class="lang-tabs" data-tab-group="content">
                <button type="button" class="lang-tab is-active" data-tab-target="content-vi">Tiếng Việt</button>
                <button type="button" class="lang-tab" data-tab-target="content-en">English</button>
            </div>
            <div class="lang-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copyContentViToEn">Sao chép VI sang EN</button>
            </div>
            <div class="lang-panel is-active" data-tab-panel="content-vi">
                <div class="row g-3">
                    <div class="col-md-6"><label>Tên tour tiếng Việt</label><input name="name_vi" id="name_vi" class="form-control" value="<?= esc($fv('name_vi')) ?>" required></div>
                    <div class="col-md-6"><label>Slug tiếng Việt</label><input name="slug_vi" id="slug_vi" class="form-control" value="<?= esc($fv('slug_vi')) ?>"></div>
                    <input type="hidden" name="short_description_vi" value="<?= esc($fv('short_description_vi'), 'attr') ?>" data-short-description-sync="vi">
                    <div class="col-md-6"><label>Tổng quan tiếng Việt</label><textarea name="overview_vi" class="form-control"><?= esc($fv('overview_vi')) ?></textarea></div>
                    <div class="col-md-6"><label>Mô tả chi tiết tiếng Việt</label><textarea name="description_vi" class="form-control"><?= esc($fv('description_vi')) ?></textarea></div>
                </div>
            </div>
            <div class="lang-panel" data-tab-panel="content-en">
                <div class="row g-3">
                    <div class="col-md-6"><label>Tên tour tiếng Anh</label><input name="name_en" id="name_en" class="form-control" value="<?= esc($fv('name_en')) ?>"></div>
                    <div class="col-md-6"><label>Slug tiếng Anh</label><input name="slug_en" id="slug_en" class="form-control" value="<?= esc($fv('slug_en')) ?>"></div>
                    <input type="hidden" name="short_description_en" value="<?= esc($fv('short_description_en'), 'attr') ?>" data-short-description-sync="en">
                    <div class="col-md-6"><label>Tổng quan tiếng Anh</label><textarea name="overview_en" class="form-control"><?= esc($fv('overview_en')) ?></textarea></div>
                    <div class="col-md-6"><label>Mô tả chi tiết tiếng Anh</label><textarea name="description_en" class="form-control"><?= esc($fv('description_en')) ?></textarea></div>
                </div>
            </div>
            </section>

            <section id="section-seo" class="form-section">
            <h2 class="section-title">SEO</h2>
            <div class="section-meta">Meta title và meta description nên chốt ngay sau khi nhập title, slug và mô tả ngắn.</div>
            <div class="lang-tabs" data-tab-group="seo">
                <button type="button" class="lang-tab is-active" data-tab-target="seo-vi">SEO VI</button>
                <button type="button" class="lang-tab" data-tab-target="seo-en">SEO EN</button>
            </div>
            <div class="lang-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copySeoViToEn">Sao chép SEO VI sang EN</button>
            </div>
            <div class="lang-panel is-active" data-tab-panel="seo-vi">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title VI</label><input name="meta_title_vi" class="form-control" value="<?= esc($fv('meta_title_vi')) ?>"></div>
                    <div class="col-md-12">
                        <label>Meta description VI</label>
                        <textarea name="meta_description_vi" class="form-control" data-meta-description-source="vi"><?= esc($fv('meta_description_vi')) ?></textarea>
                        <div class="form-text">Nội dung này cũng được dùng làm mô tả ngắn tiếng Việt của tour.</div>
                    </div>
                </div>
            </div>
            <div class="lang-panel" data-tab-panel="seo-en">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title EN</label><input name="meta_title_en" class="form-control" value="<?= esc($fv('meta_title_en')) ?>"></div>
                    <div class="col-md-12">
                        <label>Meta description EN</label>
                        <textarea name="meta_description_en" class="form-control" data-meta-description-source="en"><?= esc($fv('meta_description_en')) ?></textarea>
                        <div class="form-text">Nội dung này cũng được dùng làm mô tả ngắn tiếng Anh của tour.</div>
                    </div>
                </div>
            </div>
            </section>

            <section id="section-departure" class="form-section is-collapsed">
            <div class="section-title-row">
                <div>
                    <h2 class="section-title">Ngày khởi hành</h2>
                    <div class="section-meta mb-0">Mỗi ngày có thể đặt tour được lưu thành một dòng. Có thể tạo nhanh theo ngày, tuần hoặc tháng rồi chỉnh lại thủ công.</div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="section-count">Số ngày: <span id="departureCountBadge"><?= esc((string) count($departureRows)) ?></span></div>
                    <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
                </div>
            </div>
            <div class="accordion-content">
            <div class="departure-generator">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label>Kiểu lặp</label>
                        <select class="form-select" id="departureRepeatType">
                            <option value="once">Mỗi ngày trong khoảng</option>
                            <option value="weekly">Theo tuần</option>
                            <option value="monthly">Theo tháng</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label>Ngày bắt đầu</label><input type="date" class="form-control" id="departureRepeatStart"></div>
                    <div class="col-md-3"><label>Ngày kết thúc</label><input type="date" class="form-control" id="departureRepeatEnd"></div>
                    <div class="col-md-3"><label>Số chỗ</label><input type="number" min="0" class="form-control" id="departureRepeatSlots" value="<?= esc((string) $fv('max_travelers', '15')) ?>"></div>
                    <div class="col-md-12" id="departureWeeklyOptions">
                        <label>Ngày trong tuần</label>
                        <div class="weekday-list">
                            <label><input type="checkbox" value="1"> Thứ 2</label>
                            <label><input type="checkbox" value="2"> Thứ 3</label>
                            <label><input type="checkbox" value="3"> Thứ 4</label>
                            <label><input type="checkbox" value="4"> Thứ 5</label>
                            <label><input type="checkbox" value="5"> Thứ 6</label>
                            <label><input type="checkbox" value="6" checked> Thứ 7</label>
                            <label><input type="checkbox" value="0"> Chủ nhật</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-none" id="departureMonthlyOptions">
                        <label>Ngày trong tháng</label>
                        <input type="number" min="1" max="31" class="form-control" id="departureRepeatMonthDay" value="1">
                    </div>
                    <div class="col-md-3"><label>Giá bán</label><input type="number" min="0" class="form-control" id="departureRepeatPrice" value="<?= esc((string) $fv('sale_price', $fv('base_price'))) ?>"></div>
                    <div class="col-md-3"><label>Giá tăng thêm</label><input type="number" min="0" class="form-control" id="departureRepeatPriceUp"></div>
                    <div class="col-md-3"><label>Trạng thái</label><select class="form-select" id="departureRepeatStatus"><option value="open">Đang mở</option><option value="closed">Đã đóng</option></select></div>
                    <div class="col-md-3"><button type="button" class="btn btn-outline-primary w-100" id="generateDepartures">Tạo ngày</button></div>
                </div>
                <div class="help mt-2">Tạo ngày chỉ thêm các ngày còn thiếu. Dòng đã có cùng ngày sẽ được giữ nguyên.</div>
            </div>
            <div id="departureRows">
                <?php foreach (array_values($departureRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item departure-row">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                        <div class="row g-3">
                            <div class="col-md-3"><label>Ngày</label><input type="date" name="departures[<?= $index ?>][departure_date]" class="form-control js-departure-date" value="<?= esc((string) ($row['departure_date'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Số chỗ</label><input type="number" min="0" name="departures[<?= $index ?>][available_slots]" class="form-control" value="<?= esc((string) ($row['available_slots'] ?? '')) ?>"></div>
                            <div class="col-md-3"><label>Giá bán</label><input type="number" min="0" name="departures[<?= $index ?>][price]" class="form-control" value="<?= esc((string) ($row['price'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Giá tăng thêm</label><input type="number" min="0" name="departures[<?= $index ?>][price_up]" class="form-control" value="<?= esc((string) ($row['price_up'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Trạng thái</label><select name="departures[<?= $index ?>][status]" class="form-select"><option value="open" <?= ($row['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Đang mở</option><option value="closed" <?= ($row['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Đã đóng</option></select></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addDeparture">Thêm ngày khởi hành</button>
            </div>
            </section>

            <section id="section-itinerary" class="form-section is-collapsed">
            <h2 class="section-title">Lịch trình từng ngày</h2>
            <div class="section-meta">Nhập từng ngày theo thứ tự. Mô tả dùng rich text ngắn để làm nổi bật điểm chính.</div>
            <div class="section-title-row">
                <div class="section-count">Số ngày: <span id="itineraryCountBadge"><?= esc((string) count($itineraryRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
            </div>
            <div class="accordion-content">
            <div class="itinerary-importer" id="itineraryImporter">
                <div class="itinerary-importer__head">
                    <div>
                        <h3 class="itinerary-importer__title">Import lịch trình từ Word</h3>
                        <p class="itinerary-importer__hint">Copy toàn bộ lịch trình từ Word rồi dán vào ô rich text bên dưới. Bold, bullet và xuống dòng sẽ được giữ lại trong mô tả từng ngày. Import chỉ cập nhật ngôn ngữ đang chọn.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearItineraryImport">Xóa nội dung dán</button>
                </div>
                <div class="itinerary-importer__controls">
                    <label>
                        <span>Import vào ngôn ngữ</span>
                        <select class="form-select" id="itineraryImportLocale">
                            <option value="vi">Tiếng Việt</option>
                            <option value="en">English</option>
                        </select>
                    </label>
                </div>
                <div
                    class="itinerary-importer__editor"
                    id="itineraryImportContent"
                    contenteditable="true"
                    data-placeholder="Ví dụ:
Ngày 1: TPHCM - PARIS
Quý khách tập trung tại sân bay Tân Sơn Nhất...
• Nghỉ đêm trên máy bay.

Ngày 2: PARIS CITY TOUR
Tham quan tháp Eiffel, bảo tàng Louvre..."
                ></div>
                <div class="itinerary-importer__actions">
                    <button type="button" class="btn btn-outline-primary" id="previewItineraryImport">Xem trước</button>
                    <button type="button" class="btn btn-primary" id="replaceItineraryImport">Import thay thế ngôn ngữ đã chọn</button>
                    <button type="button" class="btn btn-outline-success" id="appendItineraryImport">Import thêm vào cuối</button>
                </div>
                <div class="itinerary-importer__preview" id="itineraryImportPreview" aria-live="polite"></div>
            </div>
            <div id="itineraryRows">
                <?php foreach (array_values($itineraryRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item itinerary-row is-sortable" draggable="false">
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-duplicate js-duplicate-itinerary">Nhân bản</button>
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                        <input type="hidden" name="itinerary_days[<?= $index ?>][sort_order]" class="js-sort-order" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                        <div class="row g-3">
                            <div class="col-md-2"><label>Ngày thứ</label><input type="number" min="1" name="itinerary_days[<?= $index ?>][day_number]" class="form-control" value="<?= esc((string) ($row['day_number'] ?? ($index + 1))) ?>"></div>
                            <div class="col-md-5"><label>Tiêu đề VI</label><input name="itinerary_days[<?= $index ?>][title_vi]" class="form-control" value="<?= esc((string) ($row['title_vi'] ?? '')) ?>"></div>
                            <div class="col-md-5"><label>Tiêu đề EN</label><input name="itinerary_days[<?= $index ?>][title_en]" class="form-control" value="<?= esc((string) ($row['title_en'] ?? '')) ?>"></div>
                            <div class="col-md-6">
                                <label>Mô tả VI</label>
                                <textarea name="itinerary_days[<?= $index ?>][description_vi]" class="form-control d-none js-rich-source"><?= esc((string) ($row['description_vi'] ?? '')) ?></textarea>
                                <div class="rich-editor-wrap js-rich-wrap">
                                    <div class="rich-editor-toolbar">
                                        <button type="button" data-command="bold">B</button>
                                        <button type="button" data-command="italic">I</button>
                                        <button type="button" data-command="insertUnorderedList">Danh sách</button>
                                        <button type="button" data-command="removeFormat">Xóa định dạng</button>
                                    </div>
                                    <div class="rich-editor js-rich-editor" contenteditable="true"><?= (string) ($row['description_vi'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Mô tả EN</label>
                                <textarea name="itinerary_days[<?= $index ?>][description_en]" class="form-control d-none js-rich-source"><?= esc((string) ($row['description_en'] ?? '')) ?></textarea>
                                <div class="rich-editor-wrap js-rich-wrap">
                                    <div class="rich-editor-toolbar">
                                        <button type="button" data-command="bold">B</button>
                                        <button type="button" data-command="italic">I</button>
                                        <button type="button" data-command="insertUnorderedList">Danh sách</button>
                                        <button type="button" data-command="removeFormat">Xóa định dạng</button>
                                    </div>
                                    <div class="rich-editor js-rich-editor" contenteditable="true"><?= (string) ($row['description_en'] ?? '') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addItinerary">Thêm ngày</button>
            </div>
            </section>

            <section id="section-media" class="form-section is-collapsed">
            <h2 class="section-title">Hình ảnh / Gallery</h2>
            <div class="section-meta">Ưu tiên `cover` cho tour card, `banner` cho hero, `gallery` cho phần trải nghiệm trong detail.</div>
            <div class="section-title-row">
                <div class="section-count">Số ảnh: <span id="mediaCountBadge"><?= esc((string) count($mediaRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
            </div>
            <div class="accordion-content">
            <div id="mediaRows">
                <?php foreach (array_values($mediaRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item media-row is-sortable" draggable="false">
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button>
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                        <div class="row g-3">
                            <div class="col-md-3"><label>Loại ảnh</label><select name="media[<?= $index ?>][type]" class="form-select"><option value="banner" <?= ($row['type'] ?? '') === 'banner' ? 'selected' : '' ?>>Banner</option><option value="cover" <?= ($row['type'] ?? '') === 'cover' ? 'selected' : '' ?>>Cover</option><option value="gallery" <?= ($row['type'] ?? 'gallery') === 'gallery' ? 'selected' : '' ?>>Gallery</option><option value="video" <?= ($row['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option></select></div>
                            <div class="col-md-5">
                                <label>Tải ảnh lên</label>
                                <input type="file" name="media_files[<?= $index ?>]" class="form-control js-media-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <div class="help">JPG, PNG hoặc WebP. Tối đa <?= esc((string) $mediaUploadLimitMb) ?>MB/ảnh.</div>
                            </div>
                            <div class="col-md-4"><label>Tiêu đề / Alt text</label><input name="media[<?= $index ?>][alt_text]" class="form-control" value="<?= esc((string) ($row['alt_text'] ?? '')) ?>"></div>
                            <input type="hidden" name="media[<?= $index ?>][file_path]" value="<?= esc((string) ($row['file_path'] ?? '')) ?>">
                            <input type="hidden" name="media[<?= $index ?>][sort_order]" class="js-sort-order" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                            <div class="col-12">
                                <div class="media-preview">
                                    <img
                                        class="js-media-preview-img"
                                        src="<?= ! empty($row['file_path']) ? esc(base_url((string) $row['file_path'])) : '' ?>"
                                        alt="Xem trước"
                                        style="<?= empty($row['file_path']) ? 'display:none' : '' ?>"
                                    >
                                    <div class="media-preview-meta">
                                        <div class="fw-semibold text-dark">Xem trước</div>
                                        <div class="js-media-preview-empty" style="<?= ! empty($row['file_path']) ? 'display:none' : '' ?>">Chưa có ảnh được chọn.</div>
                                        <?php if (! empty($row['file_path'])): ?><div>Ảnh hiện tại: <?= esc((string) $row['file_path']) ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addMedia">Thêm hình ảnh</button>
            </div>
            </section>

            <section id="section-inclusions" class="form-section is-collapsed">
            <div class="section-title-row">
                <h2 class="section-title mb-0">Giá bao gồm / không bao gồm</h2>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
            </div>
            <div class="section-meta">Quản lý từng dòng riêng cho phần giá bao gồm và không bao gồm. Cách này phù hợp hơn một đoạn text dài vì mỗi tour chỉ khác nhau ở vài mục.</div>
            <div class="accordion-content">
            <?php $inclusionSourceTours = array_values(array_filter((array) ($inclusionSourceTours ?? []), static fn($row): bool => is_array($row) && ! empty($row['id']))); ?>
            <div class="row g-3 align-items-end mb-4">
                <div class="col-lg-5">
                    <label class="form-label">Sao chép hạng mục giá từ tour khác</label>
                    <select id="inclusionSourceTour" class="form-select">
                        <option value="">-- Chọn tour nguồn --</option>
                        <?php foreach ($inclusionSourceTours as $sourceTour): ?>
                            <?php $includedCount = count((array) ($sourceTour['included'] ?? [])); ?>
                            <?php $excludedCount = count((array) ($sourceTour['excluded'] ?? [])); ?>
                            <option value="<?= esc((string) ($sourceTour['id'] ?? 0)) ?>"><?= esc((string) ($sourceTour['name'] ?? '')) ?><?= $includedCount + $excludedCount > 0 ? ' (' . $includedCount . '/' . $excludedCount . ')' : ' (chưa có dữ liệu)' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-copy-inclusions="included">Copy phần bao gồm</button>
                        <button type="button" class="btn btn-outline-secondary" data-copy-inclusions="excluded">Copy phần không bao gồm</button>
                        <button type="button" class="btn btn-outline-primary" data-copy-inclusions="both">Copy cả hai phần</button>
                    </div>
                    <div class="form-text mt-2">Dùng khi tour mới chỉ khác vài mục so với một tour đang có. Số trong ngoặc là `bao gồm/không bao gồm`.</div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="section-title-row">
                        <div class="section-count">Bao gồm: <span id="includedCountBadge"><?= esc((string) count($includedRows)) ?></span></div>
                    </div>
                    <div id="includedRows">
                        <?php foreach (array_values($includedRows) as $index => $row): ?>
                            <?php $row = is_array($row) ? $row : []; ?>
                            <div class="repeat-item">
                                <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                                <input type="hidden" name="included_items[<?= $index ?>][sort_order]" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                                <div class="row g-3">
                                    <div class="col-md-6"><label>Nội dung VI</label><input name="included_items[<?= $index ?>][label_vi]" class="form-control" value="<?= esc((string) ($row['label_vi'] ?? '')) ?>"></div>
                                    <div class="col-md-6"><label>Nội dung EN</label><input name="included_items[<?= $index ?>][label_en]" class="form-control" value="<?= esc((string) ($row['label_en'] ?? '')) ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-success" id="addIncludedItem">Thêm mục bao gồm</button>
                </div>
                <div class="col-lg-6">
                    <div class="section-title-row">
                        <div class="section-count">Không bao gồm: <span id="excludedCountBadge"><?= esc((string) count($excludedRows)) ?></span></div>
                    </div>
                    <div id="excludedRows">
                        <?php foreach (array_values($excludedRows) as $index => $row): ?>
                            <?php $row = is_array($row) ? $row : []; ?>
                            <div class="repeat-item">
                                <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                                <input type="hidden" name="excluded_items[<?= $index ?>][sort_order]" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                                <div class="row g-3">
                                    <div class="col-md-6"><label>Nội dung VI</label><input name="excluded_items[<?= $index ?>][label_vi]" class="form-control" value="<?= esc((string) ($row['label_vi'] ?? '')) ?>"></div>
                                    <div class="col-md-6"><label>Nội dung EN</label><input name="excluded_items[<?= $index ?>][label_en]" class="form-control" value="<?= esc((string) ($row['label_en'] ?? '')) ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-success" id="addExcludedItem">Thêm mục không bao gồm</button>
                </div>
            </div>
            </div>
            </section>

            <section id="section-faq" class="form-section is-collapsed">
            <h2 class="section-title">FAQ</h2>
            <div class="section-meta">Chỉ giữ các câu hỏi thật sự lặp lại nhiều trong tư vấn để phần detail gọn hơn.</div>
            <div class="section-title-row">
                <div class="section-count">Câu hỏi: <span id="faqCountBadge"><?= esc((string) count($faqRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">&#9662;</span><span>Thu gọn</span></button>
            </div>
            <div class="accordion-content">
            <div id="faqRows">
                <?php foreach (array_values($faqRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
                        <div class="row g-3">
                            <div class="col-md-6"><label>Câu hỏi VI</label><input name="faqs[<?= $index ?>][question_vi]" class="form-control" value="<?= esc((string) ($row['question_vi'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label>Câu hỏi EN</label><input name="faqs[<?= $index ?>][question_en]" class="form-control" value="<?= esc((string) ($row['question_en'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label>Câu trả lời VI</label><textarea name="faqs[<?= $index ?>][answer_vi]" class="form-control"><?= esc((string) ($row['answer_vi'] ?? '')) ?></textarea></div>
                            <div class="col-md-6"><label>Câu trả lời EN</label><textarea name="faqs[<?= $index ?>][answer_en]" class="form-control"><?= esc((string) ($row['answer_en'] ?? '')) ?></textarea></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addFaq">Thêm FAQ</button>
            </div>
            </section>

            <?php if (false): ?>
            <section id="section-seo" class="form-section">
            <h2 class="section-title">SEO</h2>
            <div class="section-meta">Meta riêng cho từng ngôn ngữ để không phụ thuộc vào phần content ở trên.</div>
            <div class="lang-tabs" data-tab-group="seo">
                <button type="button" class="lang-tab is-active" data-tab-target="seo-vi">SEO VI</button>
                <button type="button" class="lang-tab" data-tab-target="seo-en">SEO EN</button>
            </div>
            <div class="lang-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copySeoViToEn">Sao chép SEO VI sang EN</button>
            </div>
            <div class="lang-panel is-active" data-tab-panel="seo-vi">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title tiếng Việt</label><input name="meta_title_vi" class="form-control" value="<?= esc($fv('meta_title_vi')) ?>"></div>
                    <div class="col-md-12"><label>Meta description tiếng Việt</label><textarea name="meta_description_vi" class="form-control"><?= esc($fv('meta_description_vi')) ?></textarea></div>
                </div>
            </div>
            <div class="lang-panel" data-tab-panel="seo-en">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title tiếng Anh</label><input name="meta_title_en" class="form-control" value="<?= esc($fv('meta_title_en')) ?>"></div>
                    <div class="col-md-12"><label>Meta description tiếng Anh</label><textarea name="meta_description_en" class="form-control"><?= esc($fv('meta_description_en')) ?></textarea></div>
                </div>
            </div>
            </section>
            <?php endif; ?>

            <div class="sticky-action-bar">
                <div>
                    <div class="fw-semibold"><?= $tourId ? 'Editing tour #' . (int) $tourId : 'Creating new tour' ?></div>
                    <div class="meta">Kiểm tra lại destinations, media và slug trước khi lưu. Các block lớn đã được tách để thao tác nhanh hơn.</div>
                </div>
                <div class="d-flex gap-2 toolbar-wrap">
                    <?php if ($tourId): ?><a class="btn btn-outline-secondary btn-lg" href="<?= site_url('admin/tours/' . (int) $tourId . '/edit') ?>">Đặt lại</a><?php else: ?><a class="btn btn-outline-secondary btn-lg" href="<?= site_url('admin/tours/create') ?>">Đặt lại</a><?php endif; ?>
                    <button class="btn btn-primary btn-lg" type="submit"><?= esc($submitLabel ?? 'Lưu tour') ?></button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
const countriesByParent = <?= json_encode($countriesByParent, JSON_UNESCAPED_UNICODE) ?>;
const provincesByRegion = <?= json_encode($domesticProvincesByRegion, JSON_UNESCAPED_UNICODE) ?>;
const continents = <?= json_encode($continents, JSON_UNESCAPED_UNICODE) ?>;
const regions = <?= json_encode($domesticRegions, JSON_UNESCAPED_UNICODE) ?>;
const inclusionSourceTours = <?= json_encode($inclusionSourceTours ?? [], JSON_UNESCAPED_UNICODE) ?>;
const mediaUploadLimitBytes = <?= (int) $mediaUploadLimitBytes ?>;
const mediaUploadLimitMb = <?= (int) $mediaUploadLimitMb ?>;
const postMaxBytes = <?= (int) $postMaxBytes ?>;
const csrfTokenName = <?= json_encode(csrf_token()) ?>;
let destinationIndex = <?= count($destinationsRows) ?>;
let departureIndex = <?= count($departureRows) ?>;
let itineraryIndex = <?= count($itineraryRows) ?>;
let mediaIndex = <?= count($mediaRows) ?>;
let faqIndex = <?= count($faqRows) ?>;
let includedIndex = <?= count($includedRows) ?>;
let excludedIndex = <?= count($excludedRows) ?>;

function fillCountries(row) {
  const continentSelect = row.querySelector('.js-continent-select');
  const countrySelect = row.querySelector('.js-country-select');
  const selected = countrySelect?.dataset.selected || '';
  const countries = countriesByParent[continentSelect?.value] || [];
  if (!countrySelect) return;
  countrySelect.innerHTML = '<option value="">-- Chọn quốc gia có sẵn --</option>';
  countries.forEach(country => {
    const option = document.createElement('option');
    option.value = country.id;
    option.textContent = `#${country.id} - ${country.name}`;
    if (String(country.id) === String(selected)) option.selected = true;
    countrySelect.appendChild(option);
  });
}

function fillProvinces(row) {
  const regionSelect = row.querySelector('.js-region-select');
  const provinceSelect = row.querySelector('.js-province-select');
  const selected = provinceSelect?.dataset.selected || '';
  const provinces = provincesByRegion[regionSelect?.value] || [];
  if (!provinceSelect) return;
  provinceSelect.innerHTML = '<option value="">-- Chọn tỉnh/thành --</option>';
  provinces.forEach(province => {
    const option = document.createElement('option');
    option.value = province.id;
    option.textContent = `#${province.id} - ${province.name}`;
    if (String(province.id) === String(selected)) option.selected = true;
    provinceSelect.appendChild(option);
  });
}

function toggleDestinationMode(row) {
  const tourType = document.querySelector('[name="tour_type"]')?.value || 'outbound';
  row.querySelector('.js-outbound-fields')?.classList.toggle('d-none', tourType !== 'outbound');
  row.querySelector('.js-inbound-fields')?.classList.toggle('d-none', tourType !== 'inbound');
  if (tourType !== 'outbound') row.classList.remove('is-new-country');
  if (tourType !== 'inbound') row.classList.remove('is-new-province');
}

function bindDestinationRow(row) {
  row.querySelector('.js-continent-select')?.addEventListener('change', () => {
    const select = row.querySelector('.js-country-select');
    if (select) select.dataset.selected = '';
    fillCountries(row);
  });
  row.querySelector('.js-region-select')?.addEventListener('change', () => {
    const select = row.querySelector('.js-province-select');
    if (select) select.dataset.selected = '';
    fillProvinces(row);
  });
  row.querySelector('.js-toggle-new-country')?.addEventListener('click', () => row.classList.toggle('is-new-country'));
  row.querySelector('.js-toggle-new-province')?.addEventListener('click', () => row.classList.toggle('is-new-province'));
  row.querySelector('.js-remove-row')?.addEventListener('click', () => row.remove());
  toggleDestinationMode(row);
  fillCountries(row);
  fillProvinces(row);
}

function bindRemoveButtons(scope=document) {
  scope.querySelectorAll('.js-remove-row').forEach(btn => {
    if (btn.dataset.bound === '1') return;
    btn.dataset.bound = '1';
    btn.addEventListener('click', () => {
      btn.closest('.repeat-item')?.remove();
      refreshSummaryMetrics();
      scheduleDraftSave();
    });
  });
}

function departureRowTemplate(index, values = {}) {
  const date = values.departure_date || '';
  const slots = values.available_slots || '';
  const price = values.price || '';
  const priceUp = values.price_up || '';
  const status = values.status || 'open';

  return `<button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
    <div class="row g-3">
      <div class="col-md-3"><label>Ngày</label><input type="date" name="departures[${index}][departure_date]" class="form-control js-departure-date" value="${date}"></div>
      <div class="col-md-2"><label>Số chỗ</label><input type="number" min="0" name="departures[${index}][available_slots]" class="form-control" value="${slots}"></div>
      <div class="col-md-3"><label>Giá bán</label><input type="number" min="0" name="departures[${index}][price]" class="form-control" value="${price}"></div>
      <div class="col-md-2"><label>Giá tăng thêm</label><input type="number" min="0" name="departures[${index}][price_up]" class="form-control" value="${priceUp}"></div>
      <div class="col-md-2"><label>Trạng thái</label><select name="departures[${index}][status]" class="form-select"><option value="open" ${status === 'open' ? 'selected' : ''}>Đang mở</option><option value="closed" ${status === 'closed' ? 'selected' : ''}>Đã đóng</option></select></div>
    </div>`;
}

function appendDepartureRow(values = {}) {
  const container = document.getElementById('departureRows');
  if (!container) return null;

  const row = document.createElement('div');
  row.className = 'repeat-item departure-row';
  row.innerHTML = departureRowTemplate(departureIndex, values);
  container.appendChild(row);
  bindRemoveButtons(row);
  departureIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();

  return row;
}

function getExistingDepartureDates() {
  return new Set(Array.from(document.querySelectorAll('#departureRows .js-departure-date'))
    .map(input => input.value)
    .filter(Boolean));
}

function formatYmd(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function parseLocalDate(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;
  const [year, month, day] = value.split('-').map(Number);
  const date = new Date(year, month - 1, day);

  return formatYmd(date) === value ? date : null;
}

function getDepartureGeneratorValues() {
  return {
    available_slots: document.getElementById('departureRepeatSlots')?.value || '',
    price: document.getElementById('departureRepeatPrice')?.value || '',
    price_up: document.getElementById('departureRepeatPriceUp')?.value || '',
    status: document.getElementById('departureRepeatStatus')?.value || 'open',
  };
}

function generateDepartureDates() {
  const type = document.getElementById('departureRepeatType')?.value || 'once';
  const start = parseLocalDate(document.getElementById('departureRepeatStart')?.value || '');
  const end = parseLocalDate(document.getElementById('departureRepeatEnd')?.value || '');

  if (!start || !end || start > end) {
    window.alert('Please choose a valid start and end date.');
    return;
  }

  const existingDates = getExistingDepartureDates();
  const baseValues = getDepartureGeneratorValues();
  const weeklyDays = new Set(Array.from(document.querySelectorAll('#departureWeeklyOptions input:checked')).map(input => Number(input.value)));
  const monthlyDay = Number.parseInt(document.getElementById('departureRepeatMonthDay')?.value || '1', 10);
  const cursor = new Date(start);
  let added = 0;

  while (cursor <= end) {
    const ymd = formatYmd(cursor);
    const matchesType = type === 'once'
      || (type === 'weekly' && weeklyDays.has(cursor.getDay()))
      || (type === 'monthly' && cursor.getDate() === monthlyDay);

    if (matchesType && !existingDates.has(ymd)) {
      appendDepartureRow({ ...baseValues, departure_date: ymd });
      existingDates.add(ymd);
      added++;
    }

    cursor.setDate(cursor.getDate() + 1);
  }

  if (added === 0) {
    window.alert('No new departure dates were added.');
  }
}

function bindDepartureGenerator() {
  const repeatType = document.getElementById('departureRepeatType');
  const weeklyOptions = document.getElementById('departureWeeklyOptions');
  const monthlyOptions = document.getElementById('departureMonthlyOptions');

  repeatType?.addEventListener('change', () => {
    const type = repeatType.value;
    weeklyOptions?.classList.toggle('d-none', type !== 'weekly');
    monthlyOptions?.classList.toggle('d-none', type !== 'monthly');
  });

  repeatType?.dispatchEvent(new Event('change'));
  document.getElementById('generateDepartures')?.addEventListener('click', generateDepartureDates);
  document.getElementById('addDeparture')?.addEventListener('click', () => appendDepartureRow({
    price: document.getElementById('departureRepeatPrice')?.value || document.querySelector('[name="base_price"]')?.value || '',
    available_slots: document.getElementById('departureRepeatSlots')?.value || document.querySelector('[name="max_travelers"]')?.value || '',
    status: 'open',
  }));
}

function updatePrimaryDestinationOptions() {
  const tourType = document.querySelector('[name="tour_type"]')?.value || 'outbound';
  const primarySelect = document.querySelector('[name="primary_destination_id"]');
  if (!primarySelect) return;
  primarySelect.querySelectorAll('optgroup, option[data-tour-type]').forEach(element => {
    const elementType = element.getAttribute('data-tour-type');
    const shouldShow = !elementType || elementType === tourType;
    element.hidden = !shouldShow;
    if (element.tagName === 'OPTION') element.disabled = !shouldShow;
  });
}

function syncTourTypeUI() {
  document.querySelectorAll('.destination-row').forEach(toggleDestinationMode);
  updatePrimaryDestinationOptions();
  refreshSummaryMetrics();
}

function syncRichEditor(wrap) {
  const source = wrap.parentElement.querySelector('.js-rich-source');
  const editor = wrap.querySelector('.js-rich-editor');
  if (source && editor) source.value = editor.innerHTML.trim();
}

function bindRichEditor(scope=document) {
  scope.querySelectorAll('.js-rich-wrap').forEach(wrap => {
    if (wrap.dataset.ready === '1') return;
    wrap.dataset.ready = '1';
    const editor = wrap.querySelector('.js-rich-editor');
    wrap.querySelectorAll('[data-command]').forEach(button => {
      button.addEventListener('click', () => {
        editor.focus();
        document.execCommand(button.dataset.command, false, null);
        syncRichEditor(wrap);
      });
    });
    editor.addEventListener('input', () => syncRichEditor(wrap));
    editor.addEventListener('blur', () => syncRichEditor(wrap));
  });
}

function bindMediaPreview(scope=document) {
  scope.querySelectorAll('.js-media-file').forEach(input => {
    if (input.dataset.bound === '1') return;
    input.dataset.bound = '1';
    input.addEventListener('change', () => {
      const row = input.closest('.media-row');
      const previewImg = row?.querySelector('.js-media-preview-img');
      const emptyText = row?.querySelector('.js-media-preview-empty');
      const file = input.files?.[0];
      if (!previewImg || !emptyText) return;
      if (!file) {
        previewImg.style.display = 'none';
        previewImg.removeAttribute('src');
        emptyText.style.display = '';
        return;
      }
      previewImg.src = URL.createObjectURL(file);
      previewImg.style.display = '';
      emptyText.style.display = 'none';
    });
  });
}

function bindDuplicateButtons(scope=document) {
  scope.querySelectorAll('.js-duplicate-itinerary').forEach(button => {
    if (button.dataset.bound === '1') return;
    button.dataset.bound = '1';
    button.addEventListener('click', () => {
      const row = button.closest('.itinerary-row');
      if (!row) return;

      row.querySelectorAll('.js-rich-wrap').forEach(syncRichEditor);

      const clone = row.cloneNode(true);
      clone.querySelectorAll('[name]').forEach(field => {
        field.name = field.name.replace(/itinerary_days\[\d+\]/, `itinerary_days[${itineraryIndex}]`);
      });
      clone.querySelectorAll('[id]').forEach(field => field.removeAttribute('id'));
      clone.querySelectorAll('[data-bound]').forEach(field => field.removeAttribute('data-bound'));
      clone.querySelectorAll('.js-rich-wrap').forEach(wrap => wrap.removeAttribute('data-ready'));

      const dayInput = clone.querySelector('[name$="[day_number]"]');
      if (dayInput) dayInput.value = String(countRows('#itineraryRows .itinerary-row') + 1);

      document.getElementById('itineraryRows').appendChild(clone);
      bindRemoveButtons(clone);
      bindRichEditor(clone);
      bindDuplicateButtons(clone);
      itineraryIndex++;
      refreshSummaryMetrics();
      scheduleDraftSave();
    });
  });
}

function updateSortOrders(selector, options = {}) {
  const { renumberDays = false } = options;
  document.querySelectorAll(selector).forEach((row, index) => {
    const sortInput = row.querySelector('.js-sort-order');
    if (sortInput) sortInput.value = String(index);
    if (renumberDays) {
      const dayInput = row.querySelector('[name$="[day_number]"]');
      if (dayInput) dayInput.value = String(index + 1);
    }
  });
}

function bindSortableList(containerSelector, itemSelector, options = {}) {
  const container = document.querySelector(containerSelector);
  if (!container || container.dataset.sortableBound === '1') return;
  container.dataset.sortableBound = '1';

  let draggedItem = null;
  const setDraggableState = (value, item = null) => {
    const items = item ? [item] : Array.from(container.querySelectorAll(itemSelector));
    items.forEach(row => row.setAttribute('draggable', value ? 'true' : 'false'));
  };

  container.addEventListener('mousedown', event => {
    const element = event.target instanceof HTMLElement ? event.target : null;
    const handle = element?.closest('.js-drag-handle');
    const row = element?.closest(itemSelector);

    setDraggableState(false);

    if (handle && row) {
      setDraggableState(true, row);
    }
  });

  container.addEventListener('dragstart', event => {
    const target = event.target instanceof HTMLElement ? event.target.closest(itemSelector) : null;
    if (!target || target.getAttribute('draggable') !== 'true') {
      event.preventDefault();
      return;
    }
    draggedItem = target;
    target.classList.add('is-dragging');
    event.dataTransfer?.setData('text/plain', 'drag');
    if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
  });

  container.addEventListener('dragend', () => {
    if (draggedItem) draggedItem.classList.remove('is-dragging');
    draggedItem = null;
    setDraggableState(false);
    refreshSummaryMetrics();
    scheduleDraftSave();
  });

  container.addEventListener('drop', () => {
    setDraggableState(false);
  });

  container.addEventListener('dragover', event => {
    event.preventDefault();
    const target = event.target instanceof HTMLElement ? event.target.closest(itemSelector) : null;
    if (!draggedItem || !target || target === draggedItem) return;
    const rect = target.getBoundingClientRect();
    const insertAfter = event.clientY > rect.top + rect.height / 2;
    if (insertAfter) {
      target.after(draggedItem);
    } else {
      target.before(draggedItem);
    }
  });
}

function serializeFormDraft() {
  const data = {};
  new FormData(document.getElementById('tourForm')).forEach((value, key) => {
    if (value instanceof File || key === csrfTokenName || key.startsWith('media_files[')) return;
    data[key] = String(value);
  });
  return {
    saved_at: new Date().toISOString(),
    data
  };
}

let draftSaveTimer = null;
function saveDraft() {
  try {
    localStorage.setItem(draftStorageKey, JSON.stringify(serializeFormDraft()));
    const status = document.getElementById('draftStatusText');
    if (status) status.textContent = 'Tự lưu cục bộ: ' + new Date().toLocaleTimeString('vi-VN');
  } catch (error) {
    console.warn('Draft save failed', error);
  }
}

function scheduleDraftSave() {
  window.clearTimeout(draftSaveTimer);
  draftSaveTimer = window.setTimeout(saveDraft, 500);
}

function applyDraft(payload) {
  if (!payload?.data) return;
  Object.entries(payload.data).forEach(([name, value]) => {
    if (name === csrfTokenName || name.startsWith('media_files[')) return;
    const elements = document.querySelectorAll(`[name="${CSS.escape(name)}"]`);
    elements.forEach(element => {
      if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement)) return;
      if (element.type === 'checkbox') {
        element.checked = value === '1' || value === 'on';
      } else {
        element.value = value;
      }
    });
  });
}

function initDraftRestore() {
  const bar = document.getElementById('draftRestoreBar');
  const restoreButton = document.getElementById('restoreDraftButton');
  const clearButton = document.getElementById('clearDraftButton');
  const restoreTime = document.getElementById('draftRestoreTime');
  let payload = null;

  try {
    payload = JSON.parse(localStorage.getItem(draftStorageKey) || 'null');
  } catch (error) {
    payload = null;
  }

  if (!bar || !restoreButton || !clearButton || !restoreTime || !payload?.saved_at || !payload?.data) return;

  bar.classList.remove('d-none');
  restoreTime.textContent = 'Lần lưu gần nhất: ' + new Date(payload.saved_at).toLocaleString('vi-VN');

  restoreButton.addEventListener('click', () => {
    applyDraft(payload);
    document.querySelectorAll('.js-rich-wrap').forEach(wrap => {
      const source = wrap.parentElement.querySelector('.js-rich-source');
      const editor = wrap.querySelector('.js-rich-editor');
      if (source && editor) editor.innerHTML = source.value || '';
    });
    syncTourTypeUI();
    refreshSummaryMetrics();
    bar.classList.add('d-none');
  });

  clearButton.addEventListener('click', () => {
    localStorage.removeItem(draftStorageKey);
    bar.classList.add('d-none');
  });
}

function countRows(selector) {
  return document.querySelectorAll(selector).length;
}

function formatPrice(value) {
  const number = parseInt(String(value || '').replace(/[^\d]/g, ''), 10);
  if (Number.isNaN(number) || number <= 0) return 'Chưa có giá';
  return number.toLocaleString('vi-VN') + ' đ';
}

function refreshSummaryMetrics() {
  const tourTypeLabel = (document.querySelector('[name="tour_type"]')?.value || 'outbound') === 'inbound' ? 'Tour trong nước' : 'Tour nước ngoài';
  const statusValue = document.querySelector('[name="status"]')?.value || 'draft';
  const statusLabel = statusValue === 'published' ? 'Đã xuất bản' : 'Bản nháp';
  const nameValue = document.getElementById('name_vi')?.value.trim() || 'Chưa có tên tour';
  const codeValue = document.querySelector('[name="code"]')?.value.trim() || 'Chưa có mã';
  const dayValue = document.querySelector('[name="duration_days"]')?.value || '0';
  const nightValue = document.querySelector('[name="duration_nights"]')?.value || '0';
  const priceValue = document.querySelector('[name="base_price"]')?.value || '';

  const destinationCount = countRows('#destinationRows .destination-row');
  const departureCount = countRows('#departureRows .departure-row');
  const itineraryCount = countRows('#itineraryRows .repeat-item');
  const mediaCount = countRows('#mediaRows .repeat-item');
  const faqCount = countRows('#faqRows .repeat-item');
  const includedCount = countRows('#includedRows .repeat-item');
  const excludedCount = countRows('#excludedRows .repeat-item');

  document.getElementById('summaryName').textContent = nameValue;
  document.getElementById('summaryMeta').textContent = `${codeValue} · ${dayValue} ngày / ${nightValue} đêm`;
  document.getElementById('summaryPrice').textContent = formatPrice(priceValue);
  document.getElementById('summaryStatus').textContent = `${statusLabel} · ${tourTypeLabel}`;

  document.getElementById('metricDestinations').textContent = destinationCount;
  document.getElementById('metricDepartures').textContent = departureCount;
  document.getElementById('metricItinerary').textContent = itineraryCount;
  document.getElementById('metricMedia').textContent = mediaCount;
  document.getElementById('metricFaq').textContent = faqCount;

  document.getElementById('destinationCountBadge').textContent = destinationCount;
  document.getElementById('departureCountBadge').textContent = departureCount;
  document.getElementById('itineraryCountBadge').textContent = itineraryCount;
  document.getElementById('mediaCountBadge').textContent = mediaCount;
  document.getElementById('faqCountBadge').textContent = faqCount;
  const includedBadge = document.getElementById('includedCountBadge');
  const excludedBadge = document.getElementById('excludedCountBadge');
  if (includedBadge) includedBadge.textContent = includedCount;
  if (excludedBadge) excludedBadge.textContent = excludedCount;

  updateSortOrders('#itineraryRows .itinerary-row', { renumberDays: true });
  updateSortOrders('#mediaRows .media-row');
}

document.querySelectorAll('.destination-row').forEach(bindDestinationRow);
document.querySelector('[name="tour_type"]')?.addEventListener('change', syncTourTypeUI);
syncTourTypeUI();
bindRichEditor();
bindRemoveButtons();
bindDuplicateButtons();
bindMediaPreview();
bindDepartureGenerator();
bindSortableList('#itineraryRows', '.itinerary-row');
bindSortableList('#mediaRows', '.media-row');
['name_vi','code','duration_days','duration_nights','base_price'].forEach(idOrName => {
  const element = document.getElementById(idOrName) || document.querySelector(`[name="${idOrName}"]`);
  element?.addEventListener('input', () => { refreshSummaryMetrics(); scheduleDraftSave(); });
});
document.querySelector('[name="status"]')?.addEventListener('change', () => { refreshSummaryMetrics(); scheduleDraftSave(); });
document.getElementById('tourForm').addEventListener('input', scheduleDraftSave);
document.getElementById('tourForm').addEventListener('change', scheduleDraftSave);
function validateUploadSizesBeforeSubmit() {
  const files = Array.from(document.querySelectorAll('.js-media-file')).flatMap(input => Array.from(input.files || []));
  const errors = [];
  const totalBytes = files.reduce((sum, file) => sum + file.size, 0);

  files.forEach((file, index) => {
    if (file.size > mediaUploadLimitBytes) {
      errors.push(`Ảnh #${index + 1} vượt quá ${mediaUploadLimitMb}MB.`);
    }
  });

  if (postMaxBytes > 0 && totalBytes > postMaxBytes * 0.85) {
    errors.push('Tổng dung lượng ảnh quá lớn so với giới hạn hosting. Hãy giảm số ảnh hoặc nén ảnh trước khi lưu.');
  }

  if (errors.length) {
    window.alert(errors.join('\n'));
    return false;
  }

  return true;
}

document.getElementById('tourForm').addEventListener('submit', event => {
  document.querySelectorAll('.js-rich-wrap').forEach(syncRichEditor);
  if (!validateUploadSizesBeforeSubmit()) {
    event.preventDefault();
    return;
  }
  localStorage.removeItem(draftStorageKey);
});
refreshSummaryMetrics();
initDraftRestore();

document.getElementById('addDestination').addEventListener('click', () => {
  const wrapper = document.createElement('div');
  wrapper.className = 'repeat-item destination-row';
  wrapper.innerHTML = `
    <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
    <div class="row g-3 js-outbound-fields">
      <div class="col-md-4"><label>Châu lục</label><select name="destinations[${destinationIndex}][continent_id]" class="form-select js-continent-select"><option value="">-- Chọn châu lục --</option>${continents.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</select></div>
      <div class="col-md-4"><label>Quốc gia</label><select name="destinations[${destinationIndex}][country_id]" class="form-select js-country-select"><option value="">-- Chọn quốc gia có sẵn --</option></select></div>
      <div class="col-md-4"><label>Hoặc tạo quốc gia</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Tạo quốc gia mới</button></div>
    </div>
    <div class="row g-3 js-inbound-fields d-none">
      <div class="col-md-4"><label>Vùng miền</label><select name="destinations[${destinationIndex}][region_key]" class="form-select js-region-select"><option value="">-- Chọn vùng miền --</option>${regions.map(r => `<option value="${r.key}">${r.name}</option>`).join('')}</select></div>
      <div class="col-md-4"><label>Tỉnh / thành phố</label><select name="destinations[${destinationIndex}][province_id]" class="form-select js-province-select"><option value="">-- Chọn tỉnh/thành --</option></select></div>
      <div class="col-md-4"><label>Hoặc tạo tỉnh/thành</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-province">Tạo tỉnh/thành mới</button></div>
    </div>
    <div class="row g-3 mt-1 new-province-fields">
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_name_vi]" class="form-control" placeholder="Tên tỉnh/thành tiếng Việt"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_slug_vi]" class="form-control" placeholder="slug vi"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_name_en]" class="form-control" placeholder="Tên tỉnh/thành tiếng Anh"></div>
      <div class="col-md-2"><input name="destinations[${destinationIndex}][new_province_slug_en]" class="form-control" placeholder="slug en"></div>
      <div class="col-md-1"><input name="destinations[${destinationIndex}][new_province_code]" class="form-control" placeholder="DN"></div>
    </div>
    <div class="row g-3 mt-1 new-country-fields">
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_vi]" class="form-control" placeholder="Tên quốc gia tiếng Việt"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_slug_vi]" class="form-control" placeholder="slug vi"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_en]" class="form-control" placeholder="Tên quốc gia tiếng Anh"></div>
      <div class="col-md-2"><input name="destinations[${destinationIndex}][new_country_slug_en]" class="form-control" placeholder="slug en"></div>
      <div class="col-md-1"><input name="destinations[${destinationIndex}][new_country_code]" class="form-control" placeholder="CA"></div>
    </div>`;
  document.getElementById('destinationRows').appendChild(wrapper);
  bindDestinationRow(wrapper);
  bindRemoveButtons(wrapper);
  destinationIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

document.getElementById('addItinerary').addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'repeat-item itinerary-row is-sortable';
  div.setAttribute('draggable', 'false');
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button><button type="button" class="btn btn-sm btn-outline-secondary repeat-duplicate js-duplicate-itinerary">Nhân bản</button><button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button><input type="hidden" name="itinerary_days[${itineraryIndex}][sort_order]" class="js-sort-order" value="${itineraryIndex}">
    <div class="row g-3">
      <div class="col-md-2"><label>Ngày thứ</label><input type="number" min="1" name="itinerary_days[${itineraryIndex}][day_number]" class="form-control" value="${itineraryIndex + 1}"></div>
      <div class="col-md-5"><label>Tiêu đề VI</label><input name="itinerary_days[${itineraryIndex}][title_vi]" class="form-control"></div>
      <div class="col-md-5"><label>Tiêu đề EN</label><input name="itinerary_days[${itineraryIndex}][title_en]" class="form-control"></div>
      <div class="col-md-6"><label>Mô tả VI</label><textarea name="itinerary_days[${itineraryIndex}][description_vi]" class="form-control d-none js-rich-source"></textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">Danh sách</button><button type="button" data-command="removeFormat">Xóa định dạng</button></div><div class="rich-editor js-rich-editor" contenteditable="true"></div></div></div>
      <div class="col-md-6"><label>Mô tả EN</label><textarea name="itinerary_days[${itineraryIndex}][description_en]" class="form-control d-none js-rich-source"></textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">Danh sách</button><button type="button" data-command="removeFormat">Xóa định dạng</button></div><div class="rich-editor js-rich-editor" contenteditable="true"></div></div></div>
    </div>`;
  document.getElementById('itineraryRows').appendChild(div);
  bindRemoveButtons(div);
  bindRichEditor(div);
  bindDuplicateButtons(div);
  itineraryIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function htmlToPlainText(html) {
  const div = document.createElement('div');
  div.innerHTML = html || '';
  return (div.textContent || '').replace(/\s+/g, ' ').trim();
}

function sanitizeImportedHtml(html) {
  const template = document.createElement('template');
  template.innerHTML = html || '';
  const allowed = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'UL', 'OL', 'LI']);
  const blockTags = new Set(['DIV', 'SECTION', 'ARTICLE', 'HEADER', 'FOOTER']);

  template.content.querySelectorAll('script,style,iframe,object,embed,link,meta').forEach(node => node.remove());

  const walk = (node) => {
    Array.from(node.childNodes).forEach(child => {
      if (child.nodeType === Node.TEXT_NODE) return;
      if (child.nodeType !== Node.ELEMENT_NODE) {
        child.remove();
        return;
      }

      const tag = child.tagName;

      if (tag === 'B') {
        const strong = document.createElement('strong');
        strong.innerHTML = child.innerHTML;
        child.replaceWith(strong);
        walk(strong);
        return;
      }

      if (tag === 'I') {
        const em = document.createElement('em');
        em.innerHTML = child.innerHTML;
        child.replaceWith(em);
        walk(em);
        return;
      }

      if (blockTags.has(tag)) {
        const p = document.createElement('p');
        p.innerHTML = child.innerHTML;
        child.replaceWith(p);
        walk(p);
        return;
      }

      if (!allowed.has(tag)) {
        child.replaceWith(...Array.from(child.childNodes));
        walk(node);
        return;
      }

      Array.from(child.attributes).forEach(attribute => child.removeAttribute(attribute.name));
      walk(child);
    });
  };

  walk(template.content);

  const wrapper = document.createElement('div');
  wrapper.appendChild(template.content.cloneNode(true));

  return wrapper.innerHTML
    .replace(/<p>\s*<\/p>/gi, '')
    .replace(/(?:<br>\s*){3,}/gi, '<br><br>')
    .trim();
}

function blockHtmlFromNode(node) {
  if (node.nodeType === Node.TEXT_NODE) {
    const text = node.textContent.trim();
    return text === '' ? '' : `<p>${escapeHtml(text)}</p>`;
  }

  if (node.nodeType !== Node.ELEMENT_NODE) return '';

  const tag = node.tagName.toLowerCase();
  const html = sanitizeImportedHtml(node.outerHTML);
  const text = htmlToPlainText(html);

  if (text === '') return '';
  if (['p', 'ul', 'ol', 'li'].includes(tag)) return html;

  return `<p>${sanitizeImportedHtml(node.innerHTML)}</p>`;
}

function plainTextToImporterHtml(text) {
  return String(text || '')
    .replace(/\r/g, '')
    .split(/\n{2,}/)
    .map(paragraph => paragraph.trim())
    .filter(Boolean)
    .map(paragraph => `<p>${paragraph.split('\n').map(line => escapeHtml(line.trim())).join('<br>')}</p>`)
    .join('');
}

function splitBlocksOnLineBreaks(container) {
  container.querySelectorAll('p').forEach(block => {
    if (!block.querySelector('br')) return;

    const replacement = [];
    let current = [];

    const flush = () => {
      const p = document.createElement('p');
      current.forEach(node => p.appendChild(node));
      if ((p.textContent || '').trim() !== '' || p.querySelector('strong,em,u,ul,ol,li')) {
        replacement.push(p);
      }
      current = [];
    };

    Array.from(block.childNodes).forEach(child => {
      if (child.nodeType === Node.ELEMENT_NODE && child.tagName === 'BR') {
        flush();
        return;
      }

      current.push(child.cloneNode(true));
    });

    flush();

    if (replacement.length) {
      block.replaceWith(...replacement);
    }
  });
}

function parseItineraryImportHtml(html, locale = 'vi') {
  const headingPattern = /^\s*(?:ng[aà]y|ngay|day)\s*0*(\d{1,3})\s*(?:[:.)\-\u2013\u2014|]\s*)?(.*)$/i;
  const container = document.createElement('div');
  container.innerHTML = sanitizeImportedHtml(html || '');

  if (htmlToPlainText(container.innerHTML) !== '' && container.children.length === 0) {
    container.innerHTML = plainTextToImporterHtml(container.textContent || '');
  }

  splitBlocksOnLineBreaks(container);

  const rows = [];
  let current = null;

  const pushCurrent = () => {
    if (!current) return;

    if (current.generated_title) {
      const titleIndex = current.description_blocks.findIndex(block => htmlToPlainText(block) !== '');
      const titleCandidate = titleIndex >= 0 ? htmlToPlainText(current.description_blocks[titleIndex]) : '';
      if (titleCandidate !== '' && titleCandidate.length <= 140 && !headingPattern.test(titleCandidate)) {
        current.title = titleCandidate;
        current.description_blocks.splice(titleIndex, 1);
      }
    }

    rows.push(current);
  };

  const nodes = Array.from(container.childNodes);

  nodes.forEach(node => {
    const blockText = (node.textContent || '').replace(/\u00a0/g, ' ').trim();
    const match = blockText.match(headingPattern);

    if (match) {
      if (current) pushCurrent();
      const title = (match[2] || '').trim();
      current = {
        day_number: parseInt(match[1], 10) || (rows.length + 1),
        title: title || (locale === 'en' ? `Day ${parseInt(match[1], 10) || (rows.length + 1)}` : `Ngày ${parseInt(match[1], 10) || (rows.length + 1)}`),
        generated_title: title === '',
        description_blocks: [],
      };
      return;
    }

    if (current) {
      const blockHtml = blockHtmlFromNode(node);
      if (blockHtml !== '') current.description_blocks.push(blockHtml);
    }
  });

  if (current) pushCurrent();

  return rows.map((row, index) => {
    const descriptionHtml = row.description_blocks.join('');
    const data = {
      day_number: row.day_number || (index + 1),
      title_vi: '',
      title_en: '',
      description_vi: '',
      description_en: '',
      description_text: htmlToPlainText(descriptionHtml),
      import_locale: locale,
    };

    data[`title_${locale}`] = row.title;
    data[`description_${locale}`] = descriptionHtml;

    return data;
  });
}

function renderItineraryImportPreview(rows, locale = 'vi') {
  const preview = document.getElementById('itineraryImportPreview');
  if (!preview) return;

  preview.classList.add('is-visible');

  if (!rows.length) {
    preview.innerHTML = '<div class="itinerary-importer__preview-head">Chưa nhận diện được ngày nào. Mỗi ngày nên bắt đầu bằng "Ngày 1:" hoặc "Day 1:".</div>';
    return;
  }

  const localeLabel = locale === 'en' ? 'English' : 'Tiếng Việt';
  preview.innerHTML = `
    <div class="itinerary-importer__preview-head">Nhận diện ${rows.length} ngày lịch trình cho ${localeLabel}</div>
    <div class="itinerary-importer__preview-list">
      ${rows.map(row => `
        <div class="itinerary-importer__preview-item">
          <div class="itinerary-importer__preview-day">Ngày ${escapeHtml(row.day_number)}</div>
          <div class="itinerary-importer__preview-title">${escapeHtml(locale === 'en' ? row.title_en : row.title_vi)}</div>
          <div class="itinerary-importer__preview-desc">${locale === 'en' ? (row.description_en || '<p>Chưa có mô tả.</p>') : (row.description_vi || '<p>Chưa có mô tả.</p>')}</div>
        </div>
      `).join('')}
    </div>`;
}

function appendImportedItineraryRow(data = {}) {
  const div = document.createElement('div');
  div.className = 'repeat-item itinerary-row is-sortable';
  div.setAttribute('draggable', 'false');
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button><button type="button" class="btn btn-sm btn-outline-secondary repeat-duplicate js-duplicate-itinerary">Nhân bản</button><button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button><input type="hidden" name="itinerary_days[${itineraryIndex}][sort_order]" class="js-sort-order" value="${itineraryIndex}">
    <div class="row g-3">
      <div class="col-md-2"><label>Ngày thứ</label><input type="number" min="1" name="itinerary_days[${itineraryIndex}][day_number]" class="form-control" value="${escapeHtml(data.day_number || (itineraryIndex + 1))}"></div>
      <div class="col-md-5"><label>Tiêu đề VI</label><input name="itinerary_days[${itineraryIndex}][title_vi]" class="form-control" value="${escapeHtml(data.title_vi || '')}"></div>
      <div class="col-md-5"><label>Tiêu đề EN</label><input name="itinerary_days[${itineraryIndex}][title_en]" class="form-control" value="${escapeHtml(data.title_en || '')}"></div>
      <div class="col-md-6"><label>Mô tả VI</label><textarea name="itinerary_days[${itineraryIndex}][description_vi]" class="form-control d-none js-rich-source">${escapeHtml(data.description_vi || '')}</textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">Danh sách</button><button type="button" data-command="removeFormat">Xóa định dạng</button></div><div class="rich-editor js-rich-editor" contenteditable="true">${data.description_vi || ''}</div></div></div>
      <div class="col-md-6"><label>Mô tả EN</label><textarea name="itinerary_days[${itineraryIndex}][description_en]" class="form-control d-none js-rich-source">${escapeHtml(data.description_en || '')}</textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">Danh sách</button><button type="button" data-command="removeFormat">Xóa định dạng</button></div><div class="rich-editor js-rich-editor" contenteditable="true">${data.description_en || ''}</div></div></div>
    </div>`;
  document.getElementById('itineraryRows').appendChild(div);
  bindRemoveButtons(div);
  bindRichEditor(div);
  bindDuplicateButtons(div);
  itineraryIndex++;
}

function setItineraryRowLocaleContent(rowElement, data, locale) {
  const titleInput = rowElement.querySelector(`[name$="[title_${locale}]"]`);
  const descriptionSource = rowElement.querySelector(`[name$="[description_${locale}]"]`);
  const descriptionEditor = descriptionSource?.closest('.col-md-6')?.querySelector('.js-rich-editor');
  const title = data[`title_${locale}`] || '';
  const description = data[`description_${locale}`] || '';

  if (titleInput) titleInput.value = title;
  if (descriptionSource) descriptionSource.value = description;
  if (descriptionEditor) descriptionEditor.innerHTML = description;
}

function itineraryRowHasLocaleContent(rowElement, locale) {
  const title = rowElement.querySelector(`[name$="[title_${locale}]"]`)?.value.trim() || '';
  const descriptionSource = rowElement.querySelector(`[name$="[description_${locale}]"]`);
  const descriptionEditor = descriptionSource?.closest('.col-md-6')?.querySelector('.js-rich-editor');
  const description = htmlToPlainText(descriptionEditor?.innerHTML || descriptionSource?.value || '');

  return title !== '' || description !== '';
}

function importItineraryRows(mode) {
  const input = document.getElementById('itineraryImportContent');
  const locale = document.getElementById('itineraryImportLocale')?.value === 'en' ? 'en' : 'vi';
  const rows = parseItineraryImportHtml(input?.innerHTML || '', locale);
  renderItineraryImportPreview(rows, locale);

  if (!rows.length) return;

  if (mode === 'replace') {
    const container = document.getElementById('itineraryRows');
    const existingRows = Array.from(container.querySelectorAll('.itinerary-row'));
    const localeLabel = locale === 'en' ? 'English' : 'Tiếng Việt';
    const otherLocale = locale === 'en' ? 'vi' : 'en';

    if (existingRows.length > 0 && !window.confirm(`Thay thế nội dung ${localeLabel} hiện tại bằng ${rows.length} ngày vừa import? Nội dung ngôn ngữ còn lại sẽ được giữ nguyên.`)) {
      return;
    }

    rows.forEach((row, index) => {
      const existingRow = existingRows[index];

      if (existingRow) {
        setItineraryRowLocaleContent(existingRow, row, locale);
        return;
      }

      appendImportedItineraryRow({
        ...row,
        day_number: index + 1,
      });
    });

    existingRows.slice(rows.length).forEach(existingRow => {
      setItineraryRowLocaleContent(existingRow, {}, locale);

      if (!itineraryRowHasLocaleContent(existingRow, otherLocale)) {
        existingRow.remove();
      }
    });
  } else {
    const currentCount = countRows('#itineraryRows .itinerary-row');
    rows.forEach((row, index) => {
      appendImportedItineraryRow({
        ...row,
        day_number: currentCount + index + 1,
      });
    });
  }

  refreshSummaryMetrics();
  scheduleDraftSave();
}

function initItineraryImporter() {
  const input = document.getElementById('itineraryImportContent');
  const localeInput = document.getElementById('itineraryImportLocale');
  const previewButton = document.getElementById('previewItineraryImport');
  const replaceButton = document.getElementById('replaceItineraryImport');
  const appendButton = document.getElementById('appendItineraryImport');
  const clearButton = document.getElementById('clearItineraryImport');

  previewButton?.addEventListener('click', () => {
    const locale = localeInput?.value === 'en' ? 'en' : 'vi';
    renderItineraryImportPreview(parseItineraryImportHtml(input?.innerHTML || '', locale), locale);
  });
  replaceButton?.addEventListener('click', () => importItineraryRows('replace'));
  appendButton?.addEventListener('click', () => importItineraryRows('append'));
  clearButton?.addEventListener('click', () => {
    if (input) input.innerHTML = '';
    const preview = document.getElementById('itineraryImportPreview');
    if (preview) {
      preview.classList.remove('is-visible');
      preview.innerHTML = '';
    }
  });
}

initItineraryImporter();

document.getElementById('addMedia').addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'repeat-item media-row is-sortable';
  div.setAttribute('draggable', 'false');
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button><button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
    <div class="row g-3">
      <div class="col-md-3"><label>Loại ảnh</label><select name="media[${mediaIndex}][type]" class="form-select"><option value="banner">Banner</option><option value="cover">Cover</option><option value="gallery" selected>Gallery</option><option value="video">Video</option></select></div>
      <div class="col-md-5"><label>Tải ảnh lên</label><input type="file" name="media_files[${mediaIndex}]" class="form-control js-media-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="help">JPG, PNG hoặc WebP. Tối đa <?= esc((string) $mediaUploadLimitMb) ?>MB/ảnh.</div></div>
      <div class="col-md-4"><label>Tiêu đề / Alt text</label><input name="media[${mediaIndex}][alt_text]" class="form-control"></div>
      <input type="hidden" name="media[${mediaIndex}][file_path]" value="">
      <input type="hidden" name="media[${mediaIndex}][sort_order]" class="js-sort-order" value="${mediaIndex}">
      <div class="col-12"><div class="media-preview"><img class="js-media-preview-img" src="" alt="Xem trước" style="display:none"><div class="media-preview-meta"><div class="fw-semibold text-dark">Xem trước</div><div class="js-media-preview-empty">Chưa có ảnh được chọn.</div></div></div></div>
    </div>`;
  document.getElementById('mediaRows').appendChild(div);
  bindRemoveButtons(div);
  bindMediaPreview(div);
  mediaIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

function buildInclusionRowHtml(type, index, row = {}) {
  const itemType = type === 'excluded' ? 'excluded' : 'included';
  const labelVi = escapeHtml(row.label_vi || '');
  const labelEn = escapeHtml(row.label_en || '');
  const sortOrder = Number.isFinite(Number(row.sort_order)) ? Number(row.sort_order) : index;

  return `<button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
    <input type="hidden" name="${itemType}_items[${index}][sort_order]" value="${sortOrder}">
    <div class="row g-3">
      <div class="col-md-6"><label>Nội dung VI</label><input name="${itemType}_items[${index}][label_vi]" class="form-control" value="${labelVi}"></div>
      <div class="col-md-6"><label>Nội dung EN</label><input name="${itemType}_items[${index}][label_en]" class="form-control" value="${labelEn}"></div>
    </div>`;
}

function createInclusionRow(type, row = {}) {
  const itemType = type === 'excluded' ? 'excluded' : 'included';
  const index = itemType === 'excluded' ? excludedIndex++ : includedIndex++;
  const div = document.createElement('div');
  div.className = 'repeat-item';
  div.innerHTML = buildInclusionRowHtml(itemType, index, row);
  bindRemoveButtons(div);
  return div;
}

function replaceInclusionRows(type, rows) {
  const itemType = type === 'excluded' ? 'excluded' : 'included';
  const container = document.getElementById(itemType === 'excluded' ? 'excludedRows' : 'includedRows');
  if (!container) return;

  container.innerHTML = '';
  const safeRows = Array.isArray(rows) && rows.length ? rows : [{ label_vi: '', label_en: '' }];
  safeRows.forEach(row => {
    container.appendChild(createInclusionRow(itemType, row || {}));
  });
  refreshSummaryMetrics();
  scheduleDraftSave();
}

document.getElementById('addIncludedItem').addEventListener('click', () => {
  const div = createInclusionRow('included');
  document.getElementById('includedRows').appendChild(div);
  refreshSummaryMetrics();
  scheduleDraftSave();
});

document.getElementById('addExcludedItem').addEventListener('click', () => {
  const div = createInclusionRow('excluded');
  document.getElementById('excludedRows').appendChild(div);
  refreshSummaryMetrics();
  scheduleDraftSave();
});

document.querySelectorAll('[data-copy-inclusions]').forEach(button => {
  button.addEventListener('click', () => {
    const select = document.getElementById('inclusionSourceTour');
    const sourceId = select?.value || '';
    if (!sourceId) {
      window.alert('Chọn tour nguồn trước.');
      return;
    }

    const sourceTour = inclusionSourceTours.find(tour => String(tour.id) === String(sourceId));
    if (!sourceTour) {
      window.alert('Không đọc được dữ liệu từ tour nguồn.');
      return;
    }

    if ((sourceTour.included || []).length === 0 && (sourceTour.excluded || []).length === 0) {
      window.alert('Tour nguồn này chưa có dữ liệu bao gồm / không bao gồm theo cấu trúc mới để copy.');
      return;
    }

    const mode = button.dataset.copyInclusions || 'both';
    const shouldCopyIncluded = mode === 'included' || mode === 'both';
    const shouldCopyExcluded = mode === 'excluded' || mode === 'both';
    const confirmText = mode === 'both'
      ? `Copy cả phần bao gồm và không bao gồm từ tour "${sourceTour.name}"?`
      : `Copy phần ${mode === 'included' ? 'bao gồm' : 'không bao gồm'} từ tour "${sourceTour.name}"?`;

    if (!window.confirm(confirmText)) return;

    if (shouldCopyIncluded) {
      replaceInclusionRows('included', sourceTour.included || []);
    }

    if (shouldCopyExcluded) {
      replaceInclusionRows('excluded', sourceTour.excluded || []);
    }
  });
});

document.getElementById('addFaq').addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'repeat-item';
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Xóa</button>
    <div class="row g-3">
      <div class="col-md-6"><label>Câu hỏi VI</label><input name="faqs[${faqIndex}][question_vi]" class="form-control"></div>
      <div class="col-md-6"><label>Câu hỏi EN</label><input name="faqs[${faqIndex}][question_en]" class="form-control"></div>
      <div class="col-md-6"><label>Câu trả lời VI</label><textarea name="faqs[${faqIndex}][answer_vi]" class="form-control"></textarea></div>
      <div class="col-md-6"><label>Câu trả lời EN</label><textarea name="faqs[${faqIndex}][answer_en]" class="form-control"></textarea></div>
    </div>`;
  document.getElementById('faqRows').appendChild(div);
  bindRemoveButtons(div);
  faqIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

function slugify(value) {
  return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}

function bindAutoSlug(nameId, slugId) {
  const nameInput = document.getElementById(nameId);
  const slugInput = document.getElementById(slugId);
  if (!nameInput || !slugInput) return;
  let manuallyEdited = slugInput.value.trim() !== '';
  slugInput.addEventListener('input', () => { manuallyEdited = slugInput.value.trim() !== ''; });
  nameInput.addEventListener('input', () => { if (!manuallyEdited) slugInput.value = slugify(nameInput.value); });
}

function bindLangTabs() {
  document.querySelectorAll('[data-tab-group]').forEach(group => {
    group.querySelectorAll('[data-tab-target]').forEach(button => {
      button.addEventListener('click', () => {
        activateTab(group, button.getAttribute('data-tab-target'));
      });
    });
  });
}

function activateTab(group, target) {
  if (!group || !target) return;
  group.querySelectorAll('.lang-tab').forEach(tab => {
    tab.classList.toggle('is-active', tab.getAttribute('data-tab-target') === target);
  });
  const section = group.closest('.form-section');
  section?.querySelectorAll('[data-tab-panel]').forEach(panel => {
    panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target);
  });
}

function setAccordionState(section, collapsed) {
  if (!section) return;
  const content = section.querySelector('.accordion-content');
  const toggle = section.querySelector('.js-accordion-toggle');
  if (!content || !toggle) return;

  section.classList.toggle('is-collapsed', collapsed);
  toggle.querySelector('span:last-child').textContent = collapsed ? 'Mở rộng' : 'Thu gọn';

  if (collapsed) {
    content.style.maxHeight = content.scrollHeight + 'px';
    requestAnimationFrame(() => {
      content.style.maxHeight = '0px';
    });
    return;
  }

  content.style.maxHeight = content.scrollHeight + 'px';
  window.setTimeout(() => {
    if (!section.classList.contains('is-collapsed')) {
      content.style.maxHeight = 'none';
    }
  }, 220);
}

function bindAccordions() {
  document.querySelectorAll('.js-accordion-toggle').forEach(button => {
    if (button.dataset.bound === '1') return;
    button.dataset.bound = '1';

    const section = button.closest('.form-section');
    const content = section?.querySelector('.accordion-content');
    if (content) {
      content.style.maxHeight = 'none';
    }

    button.addEventListener('click', () => {
      if (!section) return;
      const collapsed = !section.classList.contains('is-collapsed');
      setAccordionState(section, collapsed);
    });

    if (section?.classList.contains('is-collapsed')) {
      setAccordionState(section, true);
    } else if (section) {
      setAccordionState(section, false);
    }
  });
}

function copyInputValue(fromName, toName) {
  const from = document.querySelector(`[name="${fromName}"]`);
  const to = document.querySelector(`[name="${toName}"]`);
  if (!from || !to) return;
  to.value = from.value;
  to.dispatchEvent(new Event('input', { bubbles: true }));
}

function bindCopyActions() {
  document.getElementById('copyContentViToEn')?.addEventListener('click', () => {
    copyInputValue('name_vi', 'name_en');
    copyInputValue('slug_vi', 'slug_en');
    copyInputValue('overview_vi', 'overview_en');
    copyInputValue('description_vi', 'description_en');
    activateTab(document.querySelector('[data-tab-group="content"]'), 'content-en');
  });

  document.getElementById('copySeoViToEn')?.addEventListener('click', () => {
    copyInputValue('meta_title_vi', 'meta_title_en');
    copyInputValue('meta_description_vi', 'meta_description_en');
    activateTab(document.querySelector('[data-tab-group="seo"]'), 'seo-en');
  });
}

function syncShortDescriptionsFromMeta() {
  ['vi', 'en'].forEach(locale => {
    const meta = document.querySelector(`[data-meta-description-source="${locale}"]`);
    const shortInput = document.querySelector(`[data-short-description-sync="${locale}"]`);
    if (!meta || !shortInput) return;

    if (meta.value.trim() === '' && shortInput.value.trim() !== '') {
      meta.value = shortInput.value;
    }

    shortInput.value = meta.value;
    meta.addEventListener('input', () => {
      shortInput.value = meta.value;
      shortInput.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  document.getElementById('tourForm')?.addEventListener('submit', () => {
    ['vi', 'en'].forEach(locale => {
      const meta = document.querySelector(`[data-meta-description-source="${locale}"]`);
      const shortInput = document.querySelector(`[data-short-description-sync="${locale}"]`);
      if (meta && shortInput) shortInput.value = meta.value;
    });
  });
}

bindAutoSlug('name_vi', 'slug_vi');
bindAutoSlug('name_en', 'slug_en');
bindLangTabs();
bindAccordions();
bindCopyActions();
syncShortDescriptionsFromMeta();
</script>
<?= view('admin/partials/app_end') ?>
</body>
</html>
