<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tour Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1220px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .section-title { font-size:18px; font-weight:700; margin:0 0 14px; }
        label { font-weight:600; margin-bottom:6px; }
        textarea { min-height:105px; }
        .help { color:#6b778c; font-size:13px; }
        .repeat-item { border:1px solid #e5ebf2; border-radius:14px; padding:16px; background:#fbfcfe; margin-bottom:12px; position:relative; }
        .repeat-remove { position:absolute; top:14px; right:14px; }
        .repeat-duplicate { position:absolute; top:14px; right:94px; }
        .repeat-drag { position:absolute; top:14px; left:14px; cursor:grab; }
        .repeat-item.is-dragging { opacity:.55; border-style:dashed; }
        .repeat-item.is-sortable { padding-left:58px; }
        .new-country-fields, .new-province-fields { display:none; }
        .repeat-item.is-new-country .new-country-fields, .repeat-item.is-new-province .new-province-fields { display:flex; }
        .rich-editor-wrap { border:1px solid #d8dee6; border-radius:8px; background:#fff; overflow:hidden; }
        .rich-editor-toolbar { display:flex; gap:6px; padding:8px; border-bottom:1px solid #edf1f5; background:#f8fafc; }
        .rich-editor-toolbar button { border:1px solid #d8dee6; background:#fff; border-radius:6px; padding:4px 9px; font-weight:700; }
        .rich-editor { min-height:118px; padding:10px 12px; outline:0; }
        .form-section { border:1px solid #e6ebf0; border-radius:18px; background:#fff; padding:22px; margin-bottom:18px; scroll-margin-top:120px; }
        .section-meta { color:#6b778c; font-size:13px; margin:-6px 0 16px; }
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
        .live-summary { display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px; margin-top:14px; }
        .live-summary-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:16px; }
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
        @media (max-width: 991px) {
            .sticky-action-bar { flex-direction:column; align-items:stretch; }
            .tour-form-nav { position:static; }
            .live-summary { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
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
$faqRows = old('faqs') ?: ($formData['faqs'] ?? []);
?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= esc($pageTitle ?? 'Tour form') ?></h1>
                <p class="text-muted mb-0"><?= esc($pageDesc ?? '') ?></p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
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
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearDraftButton">Clear</button>
                    <button type="button" class="btn btn-sm btn-primary" id="restoreDraftButton">Restore draft</button>
                </div>
            </div>
        </div>

        <div class="tour-form-nav">
            <div class="nav-head">
                <div>
                    <div class="title">Quick navigation</div>
                    <div class="text-muted">Nhảy nhanh giữa các section.</div>
                </div>
                <div class="summary-pills">
                    <span class="summary-pill">Type: <?= esc(ucfirst($tourType)) ?></span>
                    <span class="summary-pill">Status: <?= esc((string) $fv('status', 'draft')) ?></span>
                    <span class="summary-pill">Max travelers: <?= esc((string) $fv('max_travelers', '15')) ?></span>
                </div>
            </div>
            <div class="nav mt-3">
                <a class="nav-link" href="#section-main">Main</a>
                <a class="nav-link" href="#section-locations">Locations</a>
                <a class="nav-link" href="#section-destinations">Destinations</a>
                <a class="nav-link" href="#section-content-vi">Content VI</a>
                <a class="nav-link" href="#section-content-en">Content EN</a>
                <a class="nav-link" href="#section-departure">Departure</a>
                <a class="nav-link" href="#section-itinerary">Itinerary</a>
                <a class="nav-link" href="#section-media">Media</a>
                <a class="nav-link" href="#section-faq">FAQ</a>
                <a class="nav-link" href="#section-seo">SEO</a>
            </div>
            <div class="draft-status mt-2" id="draftStatusText">Autosave local: chưa có cập nhật mới.</div>
        </div>

        <div class="live-summary mb-3">
            <div class="live-summary-card">
                <div class="live-summary-label">Live summary</div>
                <div class="live-summary-value" id="summaryName"><?= esc($fv('name_vi', 'Chưa có tên tour')) ?></div>
                <div class="live-summary-sub" id="summaryMeta">
                    <?= esc($fv('code', 'No code')) ?> · <?= esc((string) $fv('duration_days', '5')) ?> ngày / <?= esc((string) $fv('duration_nights', '4')) ?> đêm
                </div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Sales snapshot</div>
                <div class="live-summary-value" id="summaryPrice"><?= esc($fv('base_price') !== '' ? number_format((int) $fv('base_price'), 0, ',', '.') . ' đ' : 'Chưa có giá') ?></div>
                <div class="live-summary-sub" id="summaryStatus"><?= esc(ucfirst((string) $fv('status', 'draft'))) ?> · <?= esc(ucfirst($tourType)) ?></div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Structure</div>
                <div class="metric-list">
                    <div class="metric-box"><span class="num" id="metricDestinations"><?= esc((string) count($destinationsRows)) ?></span><span class="lbl">Destinations</span></div>
                    <div class="metric-box"><span class="num" id="metricDepartures"><?= esc((string) count($departureRows)) ?></span><span class="lbl">Departures</span></div>
                    <div class="metric-box"><span class="num" id="metricItinerary"><?= esc((string) count($itineraryRows)) ?></span><span class="lbl">Itinerary days</span></div>
                    <div class="metric-box"><span class="num" id="metricMedia"><?= esc((string) count($mediaRows)) ?></span><span class="lbl">Media items</span></div>
                    <div class="metric-box"><span class="num" id="metricFaq"><?= esc((string) count($faqRows)) ?></span><span class="lbl">FAQs</span></div>
                </div>
            </div>
        </div>

        <form method="post" action="<?= esc($formAction) ?>" enctype="multipart/form-data" id="tourForm">
            <?= csrf_field() ?>
            <section id="section-main" class="form-section">
            <h2 class="section-title">Main Info</h2>
            <div class="section-meta">Thông tin nền của tour, giá cơ bản, trạng thái hiển thị và giới hạn khách.</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Tour type</label>
                    <select name="tour_type" class="form-select" required>
                        <option value="outbound" <?= $tourType === 'outbound' ? 'selected' : '' ?>>Outbound</option>
                        <option value="inbound" <?= $tourType === 'inbound' ? 'selected' : '' ?>>Inbound</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Choose category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>" <?= (string) $fv('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                #<?= esc($category['id']) ?> - <?= esc($category['name']) ?> (<?= esc($category['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label>Code</label><input name="code" class="form-control" value="<?= esc($fv('code')) ?>"></div>
                <div class="col-md-2"><label>SKU</label><input name="sku" class="form-control" value="<?= esc($fv('sku')) ?>"></div>
                <div class="col-md-2"><label>Days</label><input type="number" min="1" name="duration_days" class="form-control" value="<?= esc($fv('duration_days', '5')) ?>" required></div>
                <div class="col-md-2"><label>Nights</label><input type="number" min="0" name="duration_nights" class="form-control" value="<?= esc($fv('duration_nights', '4')) ?>" required></div>
                <div class="col-md-2"><label>Min travelers</label><input type="number" min="0" name="min_travelers" class="form-control" value="<?= esc($fv('min_travelers')) ?>"></div>
                <div class="col-md-2"><label>Max travelers</label><input type="number" min="0" name="max_travelers" class="form-control" value="<?= esc($fv('max_travelers', '15')) ?>"></div>
                <div class="col-md-3"><label>Adult price</label><input type="number" min="0" name="base_price" class="form-control" value="<?= esc($fv('base_price')) ?>"></div>
                <div class="col-md-3"><label>Sale price</label><input type="number" min="0" name="sale_price" class="form-control" value="<?= esc($fv('sale_price')) ?>"></div>
                <div class="col-md-6"><label>Thumbnail</label><input name="thumbnail" class="form-control" value="<?= esc($fv('thumbnail')) ?>"></div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= $fv('status', 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $fv('status') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" <?= (int) $fv('is_featured') === 1 ? 'checked' : '' ?>>
                        <span class="form-check-label">Featured tour</span>
                    </label>
                </div>
            </div>
            </section>

            <section id="section-locations" class="form-section">
            <h2 class="section-title">Locations</h2>
            <div class="section-meta">Điểm khởi hành và điểm đến chính dùng cho phân loại, breadcrumb và filter.</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Departure location</label>
                    <select name="departure_location_id" class="form-select" required>
                        <option value="">-- Choose departure --</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= esc($location['id']) ?>" <?= (string) $fv('departure_location_id') === (string) $location['id'] ? 'selected' : '' ?>>
                                #<?= esc($location['id']) ?> - <?= esc($location['name']) ?> (<?= esc($location['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Primary destination</label>
                    <select name="primary_destination_id" class="form-select">
                        <option value="">-- Optional --</option>
                        <optgroup label="Outbound countries" data-tour-type="outbound">
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= esc($country['id']) ?>" data-tour-type="outbound" <?= (string) $fv('primary_destination_id') === (string) $country['id'] ? 'selected' : '' ?>>
                                    #<?= esc($country['id']) ?> - <?= esc($country['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Domestic provinces" data-tour-type="inbound">
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

            <section id="section-destinations" class="form-section">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <h2 class="section-title">Destinations</h2>
                    <div class="section-meta mb-0">Outbound: Continent - Country. Inbound: Region - Province/City.</div>
                </div>
                <div class="subtle-card">
                    <div class="fw-semibold small mb-1">Tip</div>
                    <div class="small text-muted">Tour đi nhiều điểm nên thêm đủ tất cả điểm đến ở đây trước khi nhập itinerary.</div>
                </div>
            </div>
            <div class="section-title-row">
                <div class="section-count">Rows: <span id="destinationCountBadge"><?= esc((string) count($destinationsRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">▾</span><span>Collapse</span></button>
            </div>
            <div class="accordion-content">
            <div id="destinationRows">
                <?php foreach (array_values($destinationsRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item destination-row<?= ! empty($row['new_country_name_vi']) ? ' is-new-country' : '' ?><?= ! empty($row['new_province_name_vi']) ? ' is-new-province' : '' ?>">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
                        <div class="row g-3 js-outbound-fields">
                            <div class="col-md-4">
                                <label>Continent</label>
                                <select name="destinations[<?= $index ?>][continent_id]" class="form-select js-continent-select">
                                    <option value="">-- Choose continent --</option>
                                    <?php foreach ($continents as $continent): ?>
                                        <option value="<?= esc($continent['id']) ?>" <?= (string) ($row['continent_id'] ?? '') === (string) $continent['id'] ? 'selected' : '' ?>><?= esc($continent['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Country</label>
                                <select name="destinations[<?= $index ?>][country_id]" class="form-select js-country-select" data-selected="<?= esc((string) ($row['country_id'] ?? '')) ?>">
                                    <option value="">-- Choose existing country --</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label>Or create country</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Create new country</button></div>
                        </div>
                        <div class="row g-3 js-inbound-fields d-none">
                            <div class="col-md-4">
                                <label>Region</label>
                                <select name="destinations[<?= $index ?>][region_key]" class="form-select js-region-select">
                                    <option value="">-- Choose region --</option>
                                    <?php foreach ($domesticRegions as $region): ?>
                                        <option value="<?= esc($region['key']) ?>" <?= (string) ($row['region_key'] ?? '') === (string) $region['key'] ? 'selected' : '' ?>><?= esc($region['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Province / City</label>
                                <select name="destinations[<?= $index ?>][province_id]" class="form-select js-province-select" data-selected="<?= esc((string) ($row['province_id'] ?? '')) ?>">
                                    <option value="">-- Choose province/city --</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label>Or create province/city</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-province">Create new province/city</button></div>
                        </div>
                        <div class="row g-3 mt-1 new-province-fields">
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_name_vi]" class="form-control" value="<?= esc((string) ($row['new_province_name_vi'] ?? '')) ?>" placeholder="Province/city name VI"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_slug_vi]" class="form-control" value="<?= esc((string) ($row['new_province_slug_vi'] ?? '')) ?>" placeholder="slug vi"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_province_name_en]" class="form-control" value="<?= esc((string) ($row['new_province_name_en'] ?? '')) ?>" placeholder="Province/city name EN"></div>
                            <div class="col-md-2"><input name="destinations[<?= $index ?>][new_province_slug_en]" class="form-control" value="<?= esc((string) ($row['new_province_slug_en'] ?? '')) ?>" placeholder="slug en"></div>
                            <div class="col-md-1"><input name="destinations[<?= $index ?>][new_province_code]" class="form-control" value="<?= esc((string) ($row['new_province_code'] ?? '')) ?>" placeholder="DN"></div>
                        </div>
                        <div class="row g-3 mt-1 new-country-fields">
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_name_vi]" class="form-control" value="<?= esc((string) ($row['new_country_name_vi'] ?? '')) ?>" placeholder="Country name VI"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_slug_vi]" class="form-control" value="<?= esc((string) ($row['new_country_slug_vi'] ?? '')) ?>" placeholder="slug vi"></div>
                            <div class="col-md-3"><input name="destinations[<?= $index ?>][new_country_name_en]" class="form-control" value="<?= esc((string) ($row['new_country_name_en'] ?? '')) ?>" placeholder="Country name EN"></div>
                            <div class="col-md-2"><input name="destinations[<?= $index ?>][new_country_slug_en]" class="form-control" value="<?= esc((string) ($row['new_country_slug_en'] ?? '')) ?>" placeholder="slug en"></div>
                            <div class="col-md-1"><input name="destinations[<?= $index ?>][new_country_code]" class="form-control" value="<?= esc((string) ($row['new_country_code'] ?? '')) ?>" placeholder="CA"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addDestination">Add destination</button>
            </div>
            </section>

            <section id="section-content-vi" class="form-section">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                <div>
                    <h2 class="section-title">Content</h2>
                    <div class="section-meta mb-0">Nhập nội dung theo từng ngôn ngữ, nhưng thao tác trong cùng một block để đỡ cuộn dài.</div>
                </div>
                <div class="subtle-card small text-muted">VI nên chốt trước. EN có thể bổ sung sau nhưng nên có `Name` và `Slug` nếu cần SEO.</div>
            </div>
            <div class="lang-tabs" data-tab-group="content">
                <button type="button" class="lang-tab is-active" data-tab-target="content-vi">Tiếng Việt</button>
                <button type="button" class="lang-tab" data-tab-target="content-en">English</button>
            </div>
            <div class="lang-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copyContentViToEn">Copy VI -> EN</button>
            </div>
            <div class="lang-panel is-active" data-tab-panel="content-vi">
                <div class="row g-3">
                    <div class="col-md-6"><label>Name VI</label><input name="name_vi" id="name_vi" class="form-control" value="<?= esc($fv('name_vi')) ?>" required></div>
                    <div class="col-md-6"><label>Slug VI</label><input name="slug_vi" id="slug_vi" class="form-control" value="<?= esc($fv('slug_vi')) ?>"></div>
                    <div class="col-md-12"><label>Short description VI</label><textarea name="short_description_vi" class="form-control"><?= esc($fv('short_description_vi')) ?></textarea></div>
                    <div class="col-md-6"><label>Overview VI</label><textarea name="overview_vi" class="form-control"><?= esc($fv('overview_vi')) ?></textarea></div>
                    <div class="col-md-6"><label>Description VI</label><textarea name="description_vi" class="form-control"><?= esc($fv('description_vi')) ?></textarea></div>
                </div>
            </div>
            <div class="lang-panel" data-tab-panel="content-en">
                <div class="row g-3">
                    <div class="col-md-6"><label>Name EN</label><input name="name_en" id="name_en" class="form-control" value="<?= esc($fv('name_en')) ?>"></div>
                    <div class="col-md-6"><label>Slug EN</label><input name="slug_en" id="slug_en" class="form-control" value="<?= esc($fv('slug_en')) ?>"></div>
                    <div class="col-md-12"><label>Short description EN</label><textarea name="short_description_en" class="form-control"><?= esc($fv('short_description_en')) ?></textarea></div>
                    <div class="col-md-6"><label>Overview EN</label><textarea name="overview_en" class="form-control"><?= esc($fv('overview_en')) ?></textarea></div>
                    <div class="col-md-6"><label>Description EN</label><textarea name="description_en" class="form-control"><?= esc($fv('description_en')) ?></textarea></div>
                </div>
            </div>
            </section>

            <section id="section-departure" class="form-section">
            <div class="section-title-row">
                <div>
                    <h2 class="section-title">Departure dates</h2>
                    <div class="section-meta mb-0">Each bookable date is saved as one row. Use the generator for daily, weekly or monthly schedules, then adjust rows manually.</div>
                </div>
                <div class="section-count">Dates: <span id="departureCountBadge"><?= esc((string) count($departureRows)) ?></span></div>
            </div>
            <div class="departure-generator">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label>Repeat type</label>
                        <select class="form-select" id="departureRepeatType">
                            <option value="once">Date range every day</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label>Start date</label><input type="date" class="form-control" id="departureRepeatStart"></div>
                    <div class="col-md-3"><label>End date</label><input type="date" class="form-control" id="departureRepeatEnd"></div>
                    <div class="col-md-3"><label>Slots</label><input type="number" min="0" class="form-control" id="departureRepeatSlots" value="<?= esc((string) $fv('max_travelers', '15')) ?>"></div>
                    <div class="col-md-12" id="departureWeeklyOptions">
                        <label>Weekly weekdays</label>
                        <div class="weekday-list">
                            <label><input type="checkbox" value="1"> Mon</label>
                            <label><input type="checkbox" value="2"> Tue</label>
                            <label><input type="checkbox" value="3"> Wed</label>
                            <label><input type="checkbox" value="4"> Thu</label>
                            <label><input type="checkbox" value="5"> Fri</label>
                            <label><input type="checkbox" value="6" checked> Sat</label>
                            <label><input type="checkbox" value="0"> Sun</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-none" id="departureMonthlyOptions">
                        <label>Day of month</label>
                        <input type="number" min="1" max="31" class="form-control" id="departureRepeatMonthDay" value="1">
                    </div>
                    <div class="col-md-3"><label>Price</label><input type="number" min="0" class="form-control" id="departureRepeatPrice" value="<?= esc((string) $fv('sale_price', $fv('base_price'))) ?>"></div>
                    <div class="col-md-3"><label>Price up</label><input type="number" min="0" class="form-control" id="departureRepeatPriceUp"></div>
                    <div class="col-md-3"><label>Status</label><select class="form-select" id="departureRepeatStatus"><option value="open">Open</option><option value="closed">Closed</option></select></div>
                    <div class="col-md-3"><button type="button" class="btn btn-outline-primary w-100" id="generateDepartures">Generate dates</button></div>
                </div>
                <div class="help mt-2">Generating dates will add missing rows only. Existing rows with the same date stay unchanged.</div>
            </div>
            <div id="departureRows">
                <?php foreach (array_values($departureRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item departure-row">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
                        <div class="row g-3">
                            <div class="col-md-3"><label>Date</label><input type="date" name="departures[<?= $index ?>][departure_date]" class="form-control js-departure-date" value="<?= esc((string) ($row['departure_date'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Slots</label><input type="number" min="0" name="departures[<?= $index ?>][available_slots]" class="form-control" value="<?= esc((string) ($row['available_slots'] ?? '')) ?>"></div>
                            <div class="col-md-3"><label>Price</label><input type="number" min="0" name="departures[<?= $index ?>][price]" class="form-control" value="<?= esc((string) ($row['price'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Price up</label><input type="number" min="0" name="departures[<?= $index ?>][price_up]" class="form-control" value="<?= esc((string) ($row['price_up'] ?? '')) ?>"></div>
                            <div class="col-md-2"><label>Status</label><select name="departures[<?= $index ?>][status]" class="form-select"><option value="open" <?= ($row['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Open</option><option value="closed" <?= ($row['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option></select></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addDeparture">Add departure date</button>
            </section>

            <section id="section-itinerary" class="form-section">
            <h2 class="section-title">Itinerary Days</h2>
            <div class="section-meta">Nhập từng ngày theo thứ tự. Mô tả dùng rich text ngắn để làm nổi bật điểm chính.</div>
            <div class="section-title-row">
                <div class="section-count">Days: <span id="itineraryCountBadge"><?= esc((string) count($itineraryRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">▾</span><span>Collapse</span></button>
            </div>
            <div class="accordion-content">
            <div id="itineraryRows">
                <?php foreach (array_values($itineraryRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item itinerary-row is-sortable" draggable="true">
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-duplicate js-duplicate-itinerary">Duplicate</button>
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
                        <input type="hidden" name="itinerary_days[<?= $index ?>][sort_order]" class="js-sort-order" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                        <div class="row g-3">
                            <div class="col-md-2"><label>Day</label><input type="number" min="1" name="itinerary_days[<?= $index ?>][day_number]" class="form-control" value="<?= esc((string) ($row['day_number'] ?? ($index + 1))) ?>"></div>
                            <div class="col-md-5"><label>Title VI</label><input name="itinerary_days[<?= $index ?>][title_vi]" class="form-control" value="<?= esc((string) ($row['title_vi'] ?? '')) ?>"></div>
                            <div class="col-md-5"><label>Title EN</label><input name="itinerary_days[<?= $index ?>][title_en]" class="form-control" value="<?= esc((string) ($row['title_en'] ?? '')) ?>"></div>
                            <div class="col-md-6">
                                <label>Description VI</label>
                                <textarea name="itinerary_days[<?= $index ?>][description_vi]" class="form-control d-none js-rich-source"><?= esc((string) ($row['description_vi'] ?? '')) ?></textarea>
                                <div class="rich-editor-wrap js-rich-wrap">
                                    <div class="rich-editor-toolbar">
                                        <button type="button" data-command="bold">B</button>
                                        <button type="button" data-command="italic">I</button>
                                        <button type="button" data-command="insertUnorderedList">List</button>
                                        <button type="button" data-command="removeFormat">Clear</button>
                                    </div>
                                    <div class="rich-editor js-rich-editor" contenteditable="true"><?= (string) ($row['description_vi'] ?? '') ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Description EN</label>
                                <textarea name="itinerary_days[<?= $index ?>][description_en]" class="form-control d-none js-rich-source"><?= esc((string) ($row['description_en'] ?? '')) ?></textarea>
                                <div class="rich-editor-wrap js-rich-wrap">
                                    <div class="rich-editor-toolbar">
                                        <button type="button" data-command="bold">B</button>
                                        <button type="button" data-command="italic">I</button>
                                        <button type="button" data-command="insertUnorderedList">List</button>
                                        <button type="button" data-command="removeFormat">Clear</button>
                                    </div>
                                    <div class="rich-editor js-rich-editor" contenteditable="true"><?= (string) ($row['description_en'] ?? '') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addItinerary">Add day</button>
            </div>
            </section>

            <section id="section-media" class="form-section">
            <h2 class="section-title">Media / Gallery</h2>
            <div class="section-meta">Ưu tiên `cover` cho tour card, `banner` cho hero, `gallery` cho phần trải nghiệm trong detail.</div>
            <div class="section-title-row">
                <div class="section-count">Items: <span id="mediaCountBadge"><?= esc((string) count($mediaRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">▾</span><span>Collapse</span></button>
            </div>
            <div class="accordion-content">
            <div id="mediaRows">
                <?php foreach (array_values($mediaRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item media-row is-sortable" draggable="true">
                        <button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button>
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
                        <div class="row g-3">
                            <div class="col-md-3"><label>Type</label><select name="media[<?= $index ?>][type]" class="form-select"><option value="banner" <?= ($row['type'] ?? '') === 'banner' ? 'selected' : '' ?>>Banner</option><option value="cover" <?= ($row['type'] ?? '') === 'cover' ? 'selected' : '' ?>>Cover</option><option value="gallery" <?= ($row['type'] ?? 'gallery') === 'gallery' ? 'selected' : '' ?>>Gallery</option><option value="video" <?= ($row['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option></select></div>
                            <div class="col-md-5"><label>Upload image</label><input type="file" name="media_files[]" class="form-control js-media-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
                            <div class="col-md-4"><label>Title / Alt text</label><input name="media[<?= $index ?>][alt_text]" class="form-control" value="<?= esc((string) ($row['alt_text'] ?? '')) ?>"></div>
                            <input type="hidden" name="media[<?= $index ?>][file_path]" value="<?= esc((string) ($row['file_path'] ?? '')) ?>">
                            <input type="hidden" name="media[<?= $index ?>][sort_order]" class="js-sort-order" value="<?= esc((string) ($row['sort_order'] ?? $index)) ?>">
                            <div class="col-12">
                                <div class="media-preview">
                                    <img
                                        class="js-media-preview-img"
                                        src="<?= ! empty($row['file_path']) ? esc(base_url((string) $row['file_path'])) : '' ?>"
                                        alt="Preview"
                                        style="<?= empty($row['file_path']) ? 'display:none' : '' ?>"
                                    >
                                    <div class="media-preview-meta">
                                        <div class="fw-semibold text-dark">Preview</div>
                                        <div class="js-media-preview-empty" style="<?= ! empty($row['file_path']) ? 'display:none' : '' ?>">Chưa có ảnh được chọn.</div>
                                        <?php if (! empty($row['file_path'])): ?><div>Current: <?= esc((string) $row['file_path']) ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addMedia">Add media</button>
            </div>
            </section>

            <section id="section-faq" class="form-section">
            <h2 class="section-title">FAQ</h2>
            <div class="section-meta">Chỉ giữ các câu hỏi thật sự lặp lại nhiều trong tư vấn để phần detail gọn hơn.</div>
            <div class="section-title-row">
                <div class="section-count">Questions: <span id="faqCountBadge"><?= esc((string) count($faqRows)) ?></span></div>
                <button type="button" class="accordion-toggle js-accordion-toggle"><span class="icon">▾</span><span>Collapse</span></button>
            </div>
            <div class="accordion-content">
            <div id="faqRows">
                <?php foreach (array_values($faqRows) as $index => $row): ?>
                    <?php $row = is_array($row) ? $row : []; ?>
                    <div class="repeat-item">
                        <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
                        <div class="row g-3">
                            <div class="col-md-6"><label>Question VI</label><input name="faqs[<?= $index ?>][question_vi]" class="form-control" value="<?= esc((string) ($row['question_vi'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label>Question EN</label><input name="faqs[<?= $index ?>][question_en]" class="form-control" value="<?= esc((string) ($row['question_en'] ?? '')) ?>"></div>
                            <div class="col-md-6"><label>Answer VI</label><textarea name="faqs[<?= $index ?>][answer_vi]" class="form-control"><?= esc((string) ($row['answer_vi'] ?? '')) ?></textarea></div>
                            <div class="col-md-6"><label>Answer EN</label><textarea name="faqs[<?= $index ?>][answer_en]" class="form-control"><?= esc((string) ($row['answer_en'] ?? '')) ?></textarea></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success" id="addFaq">Add FAQ</button>
            </div>
            </section>

            <section id="section-seo" class="form-section">
            <h2 class="section-title">SEO</h2>
            <div class="section-meta">Meta riêng cho từng ngôn ngữ để không phụ thuộc vào phần content ở trên.</div>
            <div class="lang-tabs" data-tab-group="seo">
                <button type="button" class="lang-tab is-active" data-tab-target="seo-vi">SEO VI</button>
                <button type="button" class="lang-tab" data-tab-target="seo-en">SEO EN</button>
            </div>
            <div class="lang-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copySeoViToEn">Copy SEO VI -> EN</button>
            </div>
            <div class="lang-panel is-active" data-tab-panel="seo-vi">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title VI</label><input name="meta_title_vi" class="form-control" value="<?= esc($fv('meta_title_vi')) ?>"></div>
                    <div class="col-md-12"><label>Meta description VI</label><textarea name="meta_description_vi" class="form-control"><?= esc($fv('meta_description_vi')) ?></textarea></div>
                </div>
            </div>
            <div class="lang-panel" data-tab-panel="seo-en">
                <div class="row g-3">
                    <div class="col-md-12"><label>Meta title EN</label><input name="meta_title_en" class="form-control" value="<?= esc($fv('meta_title_en')) ?>"></div>
                    <div class="col-md-12"><label>Meta description EN</label><textarea name="meta_description_en" class="form-control"><?= esc($fv('meta_description_en')) ?></textarea></div>
                </div>
            </div>
            </section>

            <div class="sticky-action-bar">
                <div>
                    <div class="fw-semibold"><?= $tourId ? 'Editing tour #' . (int) $tourId : 'Creating new tour' ?></div>
                    <div class="meta">Kiểm tra lại destinations, media và slug trước khi lưu. Các block lớn đã được tách để thao tác nhanh hơn.</div>
                </div>
                <div class="d-flex gap-2 toolbar-wrap">
                    <?php if ($tourId): ?><a class="btn btn-outline-secondary btn-lg" href="<?= site_url('admin/tours/' . (int) $tourId . '/edit') ?>">Reset</a><?php else: ?><a class="btn btn-outline-secondary btn-lg" href="<?= site_url('admin/tours/create') ?>">Reset</a><?php endif; ?>
                    <button class="btn btn-primary btn-lg" type="submit"><?= esc($submitLabel ?? 'Save') ?></button>
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
let destinationIndex = <?= count($destinationsRows) ?>;
let departureIndex = <?= count($departureRows) ?>;
let itineraryIndex = <?= count($itineraryRows) ?>;
let mediaIndex = <?= count($mediaRows) ?>;
let faqIndex = <?= count($faqRows) ?>;

function fillCountries(row) {
  const continentSelect = row.querySelector('.js-continent-select');
  const countrySelect = row.querySelector('.js-country-select');
  const selected = countrySelect?.dataset.selected || '';
  const countries = countriesByParent[continentSelect?.value] || [];
  if (!countrySelect) return;
  countrySelect.innerHTML = '<option value="">-- Choose existing country --</option>';
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
  provinceSelect.innerHTML = '<option value="">-- Choose province/city --</option>';
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

  return `<button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
    <div class="row g-3">
      <div class="col-md-3"><label>Date</label><input type="date" name="departures[${index}][departure_date]" class="form-control js-departure-date" value="${date}"></div>
      <div class="col-md-2"><label>Slots</label><input type="number" min="0" name="departures[${index}][available_slots]" class="form-control" value="${slots}"></div>
      <div class="col-md-3"><label>Price</label><input type="number" min="0" name="departures[${index}][price]" class="form-control" value="${price}"></div>
      <div class="col-md-2"><label>Price up</label><input type="number" min="0" name="departures[${index}][price_up]" class="form-control" value="${priceUp}"></div>
      <div class="col-md-2"><label>Status</label><select name="departures[${index}][status]" class="form-select"><option value="open" ${status === 'open' ? 'selected' : ''}>Open</option><option value="closed" ${status === 'closed' ? 'selected' : ''}>Closed</option></select></div>
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

  container.addEventListener('dragstart', event => {
    const target = event.target instanceof HTMLElement ? event.target.closest(itemSelector) : null;
    if (!target) return;
    draggedItem = target;
    target.classList.add('is-dragging');
    event.dataTransfer?.setData('text/plain', 'drag');
    if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
  });

  container.addEventListener('dragend', () => {
    if (draggedItem) draggedItem.classList.remove('is-dragging');
    draggedItem = null;
    refreshSummaryMetrics();
    scheduleDraftSave();
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
    if (value instanceof File || key === 'media_files[]') return;
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
    if (status) status.textContent = 'Autosave local: ' + new Date().toLocaleTimeString('vi-VN');
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
  const tourTypeLabel = (document.querySelector('[name="tour_type"]')?.value || 'outbound') === 'inbound' ? 'Inbound' : 'Outbound';
  const statusLabel = document.querySelector('[name="status"]')?.value || 'draft';
  const nameValue = document.getElementById('name_vi')?.value.trim() || 'Chưa có tên tour';
  const codeValue = document.querySelector('[name="code"]')?.value.trim() || 'No code';
  const dayValue = document.querySelector('[name="duration_days"]')?.value || '0';
  const nightValue = document.querySelector('[name="duration_nights"]')?.value || '0';
  const priceValue = document.querySelector('[name="base_price"]')?.value || '';

  const destinationCount = countRows('#destinationRows .destination-row');
  const departureCount = countRows('#departureRows .departure-row');
  const itineraryCount = countRows('#itineraryRows .repeat-item');
  const mediaCount = countRows('#mediaRows .repeat-item');
  const faqCount = countRows('#faqRows .repeat-item');

  document.getElementById('summaryName').textContent = nameValue;
  document.getElementById('summaryMeta').textContent = `${codeValue} · ${dayValue} ngày / ${nightValue} đêm`;
  document.getElementById('summaryPrice').textContent = formatPrice(priceValue);
  document.getElementById('summaryStatus').textContent = `${statusLabel.charAt(0).toUpperCase()}${statusLabel.slice(1)} · ${tourTypeLabel}`;

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
document.getElementById('tourForm').addEventListener('submit', () => {
  document.querySelectorAll('.js-rich-wrap').forEach(syncRichEditor);
  localStorage.removeItem(draftStorageKey);
});
refreshSummaryMetrics();
initDraftRestore();

document.getElementById('addDestination').addEventListener('click', () => {
  const wrapper = document.createElement('div');
  wrapper.className = 'repeat-item destination-row';
  wrapper.innerHTML = `
    <button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
    <div class="row g-3 js-outbound-fields">
      <div class="col-md-4"><label>Continent</label><select name="destinations[${destinationIndex}][continent_id]" class="form-select js-continent-select"><option value="">-- Choose continent --</option>${continents.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</select></div>
      <div class="col-md-4"><label>Country</label><select name="destinations[${destinationIndex}][country_id]" class="form-select js-country-select"><option value="">-- Choose existing country --</option></select></div>
      <div class="col-md-4"><label>Or create country</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Create new country</button></div>
    </div>
    <div class="row g-3 js-inbound-fields d-none">
      <div class="col-md-4"><label>Region</label><select name="destinations[${destinationIndex}][region_key]" class="form-select js-region-select"><option value="">-- Choose region --</option>${regions.map(r => `<option value="${r.key}">${r.name}</option>`).join('')}</select></div>
      <div class="col-md-4"><label>Province / City</label><select name="destinations[${destinationIndex}][province_id]" class="form-select js-province-select"><option value="">-- Choose province/city --</option></select></div>
      <div class="col-md-4"><label>Or create province/city</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-province">Create new province/city</button></div>
    </div>
    <div class="row g-3 mt-1 new-province-fields">
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_name_vi]" class="form-control" placeholder="Province/city name VI"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_slug_vi]" class="form-control" placeholder="slug vi"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_province_name_en]" class="form-control" placeholder="Province/city name EN"></div>
      <div class="col-md-2"><input name="destinations[${destinationIndex}][new_province_slug_en]" class="form-control" placeholder="slug en"></div>
      <div class="col-md-1"><input name="destinations[${destinationIndex}][new_province_code]" class="form-control" placeholder="DN"></div>
    </div>
    <div class="row g-3 mt-1 new-country-fields">
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_vi]" class="form-control" placeholder="Country name VI"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_slug_vi]" class="form-control" placeholder="slug vi"></div>
      <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_en]" class="form-control" placeholder="Country name EN"></div>
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
  div.setAttribute('draggable', 'true');
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button><button type="button" class="btn btn-sm btn-outline-secondary repeat-duplicate js-duplicate-itinerary">Duplicate</button><button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button><input type="hidden" name="itinerary_days[${itineraryIndex}][sort_order]" class="js-sort-order" value="${itineraryIndex}">
    <div class="row g-3">
      <div class="col-md-2"><label>Day</label><input type="number" min="1" name="itinerary_days[${itineraryIndex}][day_number]" class="form-control" value="${itineraryIndex + 1}"></div>
      <div class="col-md-5"><label>Title VI</label><input name="itinerary_days[${itineraryIndex}][title_vi]" class="form-control"></div>
      <div class="col-md-5"><label>Title EN</label><input name="itinerary_days[${itineraryIndex}][title_en]" class="form-control"></div>
      <div class="col-md-6"><label>Description VI</label><textarea name="itinerary_days[${itineraryIndex}][description_vi]" class="form-control d-none js-rich-source"></textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">List</button><button type="button" data-command="removeFormat">Clear</button></div><div class="rich-editor js-rich-editor" contenteditable="true"></div></div></div>
      <div class="col-md-6"><label>Description EN</label><textarea name="itinerary_days[${itineraryIndex}][description_en]" class="form-control d-none js-rich-source"></textarea><div class="rich-editor-wrap js-rich-wrap"><div class="rich-editor-toolbar"><button type="button" data-command="bold">B</button><button type="button" data-command="italic">I</button><button type="button" data-command="insertUnorderedList">List</button><button type="button" data-command="removeFormat">Clear</button></div><div class="rich-editor js-rich-editor" contenteditable="true"></div></div></div>
    </div>`;
  document.getElementById('itineraryRows').appendChild(div);
  bindRemoveButtons(div);
  bindRichEditor(div);
  bindDuplicateButtons(div);
  itineraryIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

document.getElementById('addMedia').addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'repeat-item media-row is-sortable';
  div.setAttribute('draggable', 'true');
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-secondary repeat-drag js-drag-handle" title="Kéo để sắp xếp">↕</button><button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
    <div class="row g-3">
      <div class="col-md-3"><label>Type</label><select name="media[${mediaIndex}][type]" class="form-select"><option value="banner">Banner</option><option value="cover">Cover</option><option value="gallery" selected>Gallery</option><option value="video">Video</option></select></div>
      <div class="col-md-5"><label>Upload image</label><input type="file" name="media_files[]" class="form-control js-media-file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
      <div class="col-md-4"><label>Title / Alt text</label><input name="media[${mediaIndex}][alt_text]" class="form-control"></div>
      <input type="hidden" name="media[${mediaIndex}][file_path]" value="">
      <input type="hidden" name="media[${mediaIndex}][sort_order]" class="js-sort-order" value="${mediaIndex}">
      <div class="col-12"><div class="media-preview"><img class="js-media-preview-img" src="" alt="Preview" style="display:none"><div class="media-preview-meta"><div class="fw-semibold text-dark">Preview</div><div class="js-media-preview-empty">Chưa có ảnh được chọn.</div></div></div></div>
    </div>`;
  document.getElementById('mediaRows').appendChild(div);
  bindRemoveButtons(div);
  bindMediaPreview(div);
  mediaIndex++;
  refreshSummaryMetrics();
  scheduleDraftSave();
});

document.getElementById('addFaq').addEventListener('click', () => {
  const div = document.createElement('div');
  div.className = 'repeat-item';
  div.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger repeat-remove js-remove-row">Remove</button>
    <div class="row g-3">
      <div class="col-md-6"><label>Question VI</label><input name="faqs[${faqIndex}][question_vi]" class="form-control"></div>
      <div class="col-md-6"><label>Question EN</label><input name="faqs[${faqIndex}][question_en]" class="form-control"></div>
      <div class="col-md-6"><label>Answer VI</label><textarea name="faqs[${faqIndex}][answer_vi]" class="form-control"></textarea></div>
      <div class="col-md-6"><label>Answer EN</label><textarea name="faqs[${faqIndex}][answer_en]" class="form-control"></textarea></div>
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
  toggle.querySelector('span:last-child').textContent = collapsed ? 'Expand' : 'Collapse';

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
    copyInputValue('short_description_vi', 'short_description_en');
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

bindAutoSlug('name_vi', 'slug_vi');
bindAutoSlug('name_en', 'slug_en');
bindLangTabs();
bindAccordions();
bindCopyActions();
</script>
</body>
</html>
