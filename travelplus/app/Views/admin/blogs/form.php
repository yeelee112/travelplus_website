<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Blog Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1380px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:22px; box-shadow:0 18px 48px rgba(23,32,51,.07); padding:24px; }
        .form-section { border:1px solid #e2e8f0; border-radius:18px; background:#fff; padding:24px; margin-bottom:16px; scroll-margin-top:120px; box-shadow:0 7px 22px rgba(15,23,42,.035); }
        .form-section.is-step-hidden { display:none; }
        .section-title { font-size:18px; font-weight:700; margin:0 0 14px; }
        .section-meta { color:#6b778c; font-size:13px; margin:-6px 0 16px; }
        label { font-weight:600; margin-bottom:6px; }
        .help { color:#6b778c; font-size:13px; }
        .blog-form-workspace { display:grid; grid-template-columns:248px minmax(0,1fr); gap:20px; align-items:start; }
        .blog-form-main { min-width:0; }
        .blog-form-nav { position:sticky; top:var(--blog-sticky-top,92px); z-index:10; background:#f8fafc; padding:16px; border:1px solid #e2e8f0; border-radius:18px; }
        .blog-form-nav .nav { display:flex; flex-direction:column; gap:8px; }
        .blog-step-button { width:100%; display:grid; grid-template-columns:34px minmax(0,1fr) auto; gap:10px; align-items:center; border:1px solid transparent; border-radius:13px; padding:11px; color:#475569; background:transparent; text-align:left; transition:.16s ease; }
        .blog-step-button:hover { background:#fff; border-color:#dce6ee; color:#0f172a; }
        .blog-step-button.is-active { color:#075985; background:#fff; border-color:#7dd3fc; box-shadow:0 7px 18px rgba(14,165,233,.10); }
        .blog-step-button.is-complete .blog-step-number { background:#dcfce7; color:#15803d; }
        .blog-step-number { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; background:#e2e8f0; color:#475569; font-size:13px; font-weight:800; }
        .blog-step-button.is-active .blog-step-number { background:#0ea5e9; color:#fff; }
        .blog-step-copy { min-width:0; }
        .blog-step-title { display:block; color:inherit; font-size:13px; font-weight:800; }
        .blog-step-desc { display:block; margin-top:2px; color:#94a3b8; font-size:11px; line-height:1.3; }
        .blog-step-state { color:#16a34a; font-size:13px; font-weight:900; opacity:0; }
        .blog-step-button.is-complete .blog-step-state { opacity:1; }
        .blog-progress { height:6px; overflow:hidden; border-radius:999px; background:#e2e8f0; margin:12px 0 6px; }
        .blog-progress-bar { height:100%; width:25%; border-radius:inherit; background:linear-gradient(90deg,#0ea5e9,#0284c7); transition:width .2s ease; }
        .blog-progress-label { color:#64748b; font-size:11px; }
        .nav-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; }
        .nav-head .title { font-size:13px; font-weight:700; color:#334155; }
        .summary-pills { display:flex; gap:8px; flex-wrap:wrap; }
        .summary-pill { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; font-size:12px; font-weight:600; }
        .draft-status { font-size:12px; color:#64748b; margin-top:8px; }
        .live-summary { display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px; margin-bottom:18px; }
        .live-summary-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:16px; }
        .live-summary-label { color:#64748b; font-size:12px; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }
        .live-summary-value { font-size:15px; font-weight:700; color:#0f172a; }
        .live-summary-sub { color:#64748b; font-size:13px; margin-top:4px; }
        .metric-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
        .metric-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px; }
        .metric-box .num { display:block; font-size:22px; font-weight:700; line-height:1.1; color:#0f172a; }
        .metric-box .lbl { color:#64748b; font-size:12px; margin-top:4px; }
        .lang-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .lang-tab { border:1px solid #d9e2ec; border-radius:999px; padding:9px 16px; font-weight:700; color:#334155; background:#fff; cursor:pointer; }
        .lang-tab.is-active { background:#172033; color:#fff; border-color:#172033; }
        .lang-panel { display:none; }
        .lang-panel.is-active { display:block; }
        .lang-actions { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
        .lang-card { border:1px solid #edf1f5; border-radius:14px; padding:18px; background:#fcfdff; }
        .editor-shell { border:1px solid #d8e0ea; border-radius:14px; background:#fff; overflow:visible; }
        .editor-toolbar { position:sticky; top:var(--blog-sticky-top,92px); z-index:16; display:flex; gap:8px; flex-wrap:nowrap; overflow-x:auto; padding:12px; border-bottom:1px solid #d8e0ea; border-radius:13px 13px 0 0; background:rgba(248,250,252,.97); box-shadow:0 8px 18px rgba(15,23,42,.07); backdrop-filter:blur(10px); scrollbar-width:thin; }
        .editor-toolbar button { display:inline-flex; align-items:center; gap:6px; border:1px solid #ccd7e3; background:#fff; border-radius:10px; padding:8px 12px; font-size:14px; white-space:nowrap; }
        .editor-toolbar button:hover { background:#f3f7fb; }
        .editor-toolbar-spacer { flex:0 0 2px; }
        .editor-upload-button { color:#0369a1; border-color:#7dd3fc !important; background:#f0f9ff !important; font-weight:800; }
        .editor-upload-button:hover { background:#e0f2fe !important; }
        .editor-shortcut { padding:2px 5px; border-radius:5px; color:#64748b; background:#f1f5f9; font-size:10px; font-weight:700; }
        .editor-mode-button.is-active { background:#172033; border-color:#172033; color:#fff; }
        .editor-area { min-height:380px; padding:18px; outline:none; line-height:1.75; }
        .editor-area:empty:before { content:attr(data-placeholder); color:#94a3b8; }
        .editor-area img { max-width:100%; height:auto; border-radius:14px; margin:10px 0; display:block; }
        .editor-area figure { margin:18px 0; }
        .editor-area figcaption { font-size:13px; color:#64748b; margin-top:8px; text-align:center; }
        .editor-area blockquote { border-left:4px solid #0ea5e9; padding-left:14px; color:#334155; margin:18px 0; }
        .editor-code { display:none; width:100%; min-height:380px; padding:18px; border:0; outline:none; resize:vertical; color:#0f172a; background:#fbfdff; font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; font-size:13px; line-height:1.65; }
        .editor-shell.is-code-mode .editor-area { display:none; }
        .editor-shell.is-code-mode .editor-code { display:block; }
        .editor-shell.is-code-mode [data-command] { opacity:.45; pointer-events:none; }
        .editor-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
        .editor-note { margin-top:10px; font-size:13px; color:#64748b; }
        .preview-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
        .preview-card { border:1px dashed #d9e2ec; border-radius:14px; padding:12px; background:#fff; min-height:180px; }
        .preview-card img { width:100%; max-height:140px; object-fit:cover; border-radius:10px; border:1px solid #d8e0ea; background:#f8fafc; }
        .sticky-action-bar { position:sticky; bottom:14px; z-index:20; display:flex; justify-content:space-between; align-items:center; gap:16px; margin-top:20px; padding:14px 18px; background:rgba(255,255,255,.96); border:1px solid #dce4ec; border-radius:18px; box-shadow:0 16px 36px rgba(15,23,42,.10); }
        .sticky-action-bar .meta { color:#64748b; font-size:13px; }
        .blog-step-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:18px 20px; margin-bottom:14px; border:1px solid #bae6fd; border-radius:18px; background:linear-gradient(135deg,#f0f9ff 0%,#fff 72%); }
        .blog-step-kicker { color:#0284c7; font-size:11px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
        .blog-step-heading h2 { margin:3px 0; color:#0f172a; font-size:21px; font-weight:800; }
        .blog-step-heading p { margin:0; color:#64748b; font-size:13px; }
        .blog-step-badge { flex:0 0 auto; padding:7px 10px; border-radius:999px; color:#0369a1; background:#e0f2fe; font-size:11px; font-weight:800; }
        .step-nav-actions { display:flex; gap:8px; }
        .publish-panel { padding:16px; margin-bottom:18px; border:1px solid #cfe8f4; border-radius:14px; background:#f7fcff; }
        @media (max-width: 991px) {
            .blog-form-workspace { grid-template-columns:1fr; }
            .blog-form-nav { position:static; padding:10px; margin:0 -4px; border-radius:14px; }
            .blog-form-nav .nav { flex-direction:row; overflow-x:auto; padding-bottom:3px; scrollbar-width:thin; }
            .blog-form-nav .nav-head, .blog-form-nav .draft-status, .blog-progress-label { display:none; }
            .blog-step-button { flex:0 0 160px; grid-template-columns:30px minmax(0,1fr); padding:8px; }
            .blog-step-number { width:30px; height:30px; }
            .blog-step-desc, .blog-step-state, .summary-pills { display:none; }
            .editor-toolbar { top:var(--blog-sticky-top,84px); }
            .live-summary { grid-template-columns:1fr; }
            .preview-grid { grid-template-columns:1fr; }
            .sticky-action-bar { flex-direction:column; align-items:stretch; }
        }
        @media (max-width: 575px) {
            .admin-shell { margin:16px auto; padding:0 10px; }
            .admin-card { padding:14px; border-radius:18px; }
            .form-section { padding:18px 16px; }
            .live-summary-card:last-child { display:none; }
            .sticky-action-bar .meta { display:none; }
            .step-nav-actions { width:100%; }
            .step-nav-actions .btn { flex:1; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'blogs'; ?>
<?php
$formData = $formData ?? [];
$fv = static fn(string $key, $default = '') => old($key, $formData[$key] ?? $default);
$blogId = $blogId ?? null;
$thumbnail = $fv('thumbnail');
$coverImage = $fv('cover_image');
$featuredImage = $fv('featured_image');
$categoryOptions = array_values(array_unique(array_filter(array_map(
    static fn($value): string => trim((string) $value),
    $categoryOptions ?? []
))));
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= esc($pageTitle ?? 'Blog form') ?></h1>
                <p class="text-muted mb-0"><?= esc($pageDesc ?? '') ?></p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
            </div>
        </div>

        <?php if (! empty($success)): ?>
            <div class="alert alert-success"><?= esc($success) ?></div>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger" role="alert" tabindex="-1" data-blog-form-errors>
                <?php foreach ($errors as $error): ?>
                    <div><?= esc(is_string($error) ? $error : (string) $error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="alert alert-warning d-none" id="draftRestoreBar">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div>
                    <div class="fw-semibold">Có bản nháp cục bộ chưa được khôi phục.</div>
                    <div class="small text-muted" id="draftRestoreTime"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearDraftButton">Xóa nháp</button>
                    <button type="button" class="btn btn-sm btn-primary" id="restoreDraftButton">Khôi phục nháp</button>
                </div>
            </div>
        </div>

        <div class="blog-form-workspace">
        <aside class="blog-form-nav" aria-label="Các bước tạo blog">
            <div class="nav-head">
                <div>
                    <div class="title">Tiến độ bài viết</div>
                    <div class="help">Làm lần lượt hoặc chọn bước cần sửa.</div>
                </div>
            </div>
            <div class="nav" role="tablist">
                <button type="button" class="blog-step-button is-active" data-blog-step="1" data-step-title="Thông tin bài viết" data-step-description="Chọn danh mục và tác giả cho bài viết." data-step-badge="Bắt buộc" aria-selected="true">
                    <span class="blog-step-number">1</span><span class="blog-step-copy"><span class="blog-step-title">Thông tin bài viết</span><span class="blog-step-desc">Danh mục, tác giả</span></span><span class="blog-step-state">✓</span>
                </button>
                <button type="button" class="blog-step-button" data-blog-step="2" data-step-title="Viết nội dung" data-step-description="Nhập tiêu đề, mô tả ngắn và nội dung bài viết theo từng ngôn ngữ." data-step-badge="Bắt buộc" aria-selected="false">
                    <span class="blog-step-number">2</span><span class="blog-step-copy"><span class="blog-step-title">Viết nội dung</span><span class="blog-step-desc">Tiêu đề, mô tả, bài viết</span></span><span class="blog-step-state">✓</span>
                </button>
                <button type="button" class="blog-step-button" data-blog-step="3" data-step-title="Hình ảnh" data-step-description="Thêm thumbnail, ảnh cover và ảnh nổi bật cho các vị trí hiển thị." data-step-badge="Nên có" aria-selected="false">
                    <span class="blog-step-number">3</span><span class="blog-step-copy"><span class="blog-step-title">Hình ảnh</span><span class="blog-step-desc">Thumbnail, cover, featured</span></span><span class="blog-step-state">✓</span>
                </button>
                <button type="button" class="blog-step-button" data-blog-step="4" data-step-title="SEO & xuất bản" data-step-description="Hoàn thiện thông tin tìm kiếm, trạng thái và lịch xuất bản." data-step-badge="Bước cuối" aria-selected="false">
                    <span class="blog-step-number">4</span><span class="blog-step-copy"><span class="blog-step-title">SEO & xuất bản</span><span class="blog-step-desc">Google, trạng thái, lịch đăng</span></span><span class="blog-step-state">✓</span>
                </button>
            </div>
            <div class="blog-progress" aria-hidden="true"><div class="blog-progress-bar" id="blogProgressBar"></div></div>
            <div class="blog-progress-label" id="blogProgressLabel">Bước 1/4</div>
            <div class="draft-status" id="draftStatusText">Tự lưu cục bộ: chưa có cập nhật mới.</div>
        </aside>

        <div class="blog-form-main">

        <div class="live-summary">
            <div class="live-summary-card">
                <div class="live-summary-label">Tóm tắt nhanh</div>
                <div class="live-summary-value" id="summaryTitle"><?= esc($fv('title_vi', 'Chưa có tiêu đề')) ?></div>
                <div class="live-summary-sub" id="summarySlug"><?= esc($fv('slug_vi', 'chua-co-slug')) ?></div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Xuất bản</div>
                <div class="live-summary-value" id="summaryStatus"><?= esc($fv('status', 'published') === 'published' ? 'Đã xuất bản' : 'Bản nháp') ?></div>
                <div class="live-summary-sub" id="summaryPublishedAt"><?= esc($fv('published_at', 'Chưa có lịch')) ?></div>
            </div>
            <div class="live-summary-card">
                <div class="live-summary-label">Tài nguyên</div>
                <div class="metric-list">
                    <div class="metric-box"><span class="num" id="metricViLength">0</span><span class="lbl">Ký tự VI</span></div>
                    <div class="metric-box"><span class="num" id="metricEnLength">0</span><span class="lbl">Ký tự EN</span></div>
                    <div class="metric-box"><span class="num" id="metricImages"><?= ($thumbnail !== '' ? 1 : 0) + ($coverImage !== '' ? 1 : 0) + ($featuredImage !== '' ? 1 : 0) ?></span><span class="lbl">Ảnh đầu trang</span></div>
                    <div class="metric-box"><span class="num" id="metricSeo">0</span><span class="lbl">SEO đã nhập</span></div>
                </div>
            </div>
        </div>

        <div class="blog-step-heading" aria-live="polite">
            <div>
                <div class="blog-step-kicker" id="activeBlogStepKicker">Bước 1 / 4</div>
                <h2 id="activeBlogStepTitle">Thông tin bài viết</h2>
                <p id="activeBlogStepDescription">Chọn danh mục và tác giả cho bài viết.</p>
            </div>
            <span class="blog-step-badge" id="activeBlogStepBadge">Bắt buộc</span>
        </div>

        <form method="post" action="<?= esc($formAction ?? site_url('admin/blogs')) ?>" enctype="multipart/form-data" id="blogForm">
            <?= csrf_field() ?>

            <section id="section-main" class="form-section" data-blog-step-panel="1">
                <h2 class="section-title">Thông tin chính</h2>
                <div class="section-meta">Chọn nơi bài viết xuất hiện và tên tác giả. Trạng thái đăng được đặt ở bước cuối.</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Danh mục</label>
                        <input name="category" list="blogCategoryOptions" class="form-control" value="<?= esc($fv('category', 'Cảm hứng du lịch')) ?>" placeholder="Chọn hoặc nhập danh mục" required>
                        <datalist id="blogCategoryOptions">
                            <?php foreach ($categoryOptions as $categoryOption): ?>
                                <option value="<?= esc($categoryOption) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="help mt-1">Gõ để lọc danh mục có sẵn hoặc nhập danh mục mới.</div>
                    </div>
                    <div class="col-md-6">
                        <label>Tác giả</label>
                        <input name="author_name" class="form-control" value="<?= esc($fv('author_name', 'Travel Plus')) ?>" required>
                    </div>
                </div>
            </section>

            <section id="section-media" class="form-section is-step-hidden" data-blog-step-panel="3">
                <h2 class="section-title">Hình ảnh</h2>
                <div class="section-meta">Ảnh chính dùng cho card, banner và các điểm nhấn featured.</div>
                <div class="preview-grid">
                    <div>
                        <label>Thumbnail</label>
                        <input type="file" name="thumbnail_file" class="form-control js-image-input" data-preview="#previewThumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <input type="hidden" name="current_thumbnail" value="<?= esc($thumbnail) ?>">
                        <div class="preview-card mt-2">
                            <img id="previewThumbnail" src="<?= $thumbnail !== '' ? esc(base_url($thumbnail)) : '' ?>" alt="Thumbnail preview" style="<?= $thumbnail !== '' ? '' : 'display:none' ?>">
                            <div class="help mt-2 js-preview-empty" style="<?= $thumbnail !== '' ? 'display:none' : '' ?>">Chưa có thumbnail.</div>
                        </div>
                    </div>
                    <div>
                        <label>Ảnh cover</label>
                        <input type="file" name="cover_file" class="form-control js-image-input" data-preview="#previewCover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <input type="hidden" name="current_cover_image" value="<?= esc($coverImage) ?>">
                        <div class="preview-card mt-2">
                            <img id="previewCover" src="<?= $coverImage !== '' ? esc(base_url($coverImage)) : '' ?>" alt="Xem trước ảnh cover" style="<?= $coverImage !== '' ? '' : 'display:none' ?>">
                            <div class="help mt-2 js-preview-empty" style="<?= $coverImage !== '' ? 'display:none' : '' ?>">Chưa có ảnh cover.</div>
                        </div>
                    </div>
                    <div>
                        <label>Ảnh nổi bật</label>
                        <input type="file" name="featured_file" class="form-control js-image-input" data-preview="#previewFeatured" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <input type="hidden" name="current_featured_image" value="<?= esc($featuredImage) ?>">
                        <div class="preview-card mt-2">
                            <img id="previewFeatured" src="<?= $featuredImage !== '' ? esc(base_url($featuredImage)) : '' ?>" alt="Xem trước ảnh nổi bật" style="<?= $featuredImage !== '' ? '' : 'display:none' ?>">
                            <div class="help mt-2 js-preview-empty" style="<?= $featuredImage !== '' ? 'display:none' : '' ?>">Chưa có ảnh nổi bật.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="section-content" class="form-section is-step-hidden" data-blog-step-panel="2">
                <h2 class="section-title">Nội dung</h2>
                <div class="section-meta">Biên tập nội dung theo 2 ngôn ngữ trong cùng một khối để thao tác ngắn và rõ hơn.</div>
                <div class="lang-tabs" data-tab-group="content">
                    <button type="button" class="lang-tab is-active" data-tab-target="content-vi">Tiếng Việt</button>
                    <button type="button" class="lang-tab" data-tab-target="content-en">English</button>
                </div>
                <div class="lang-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyContentViToEn">Sao chép thông tin VI sang EN</button>
                </div>

                <div class="lang-card lang-panel is-active" data-tab-panel="content-vi">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Tiêu đề VI</label>
                            <input name="title_vi" id="title_vi" class="form-control" value="<?= esc($fv('title_vi')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Slug VI</label>
                            <input name="slug_vi" id="slug_vi" class="form-control" value="<?= esc($fv('slug_vi')) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label>Mô tả ngắn VI</label>
                            <textarea name="excerpt_vi" class="form-control"><?= esc($fv('excerpt_vi')) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2">
                            <strong>Nội dung VI</strong>
                            <div class="help">Viết bài trực tiếp tại đây. Có thể chèn ảnh xen giữa các đoạn chữ trong cùng một nội dung.</div>
                        </div>
                        <textarea name="content_vi" class="d-none js-editor-source"><?= esc($fv('content_vi')) ?></textarea>
                        <div class="editor-shell js-editor-shell">
                            <div class="editor-toolbar">
                                <button type="button" class="editor-upload-button js-editor-upload">▣ Chèn ảnh</button>
                                <button type="button" data-command="bold">B</button>
                                <button type="button" data-command="italic">I</button>
                                <button type="button" data-command="formatBlock" data-value="p" title="Đoạn văn thường · Ctrl+Alt+0">P <span class="editor-shortcut">Ctrl Alt 0</span></button>
                                <button type="button" data-command="formatBlock" data-value="h2" title="Heading 2 · Ctrl+Alt+2">H2 <span class="editor-shortcut">Ctrl Alt 2</span></button>
                                <button type="button" data-command="formatBlock" data-value="h3" title="Heading 3 · Ctrl+Alt+3">H3 <span class="editor-shortcut">Ctrl Alt 3</span></button>
                                <button type="button" data-command="insertUnorderedList">Danh sách</button>
                                <button type="button" data-command="insertOrderedList">1. 2. 3.</button>
                                <button type="button" data-command="formatBlock" data-value="blockquote">Trích dẫn</button>
                                <button type="button" data-command="createLink">Link</button>
                                <button type="button" data-command="removeFormat">Xóa định dạng</button>
                                <span class="editor-toolbar-spacer"></span>
                                <button type="button" class="editor-mode-button is-active" data-editor-mode="preview">Preview</button>
                                <button type="button" class="editor-mode-button" data-editor-mode="code">Code</button>
                            </div>
                            <div class="editor-area js-editor" contenteditable="true" data-placeholder="Viết nội dung blog tiếng Việt ở đây..."></div>
                            <textarea class="editor-code js-editor-code" spellcheck="false" aria-label="HTML code VI"></textarea>
                        </div>
                        <div class="editor-note">Ảnh chèn trong bài sẽ giữ nguyên cho cả VI và EN nếu copy toàn bộ nội dung sang bản EN.</div>
                    </div>
                </div>

                <div class="lang-card lang-panel" data-tab-panel="content-en">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Tiêu đề EN</label>
                            <input name="title_en" id="title_en" class="form-control" value="<?= esc($fv('title_en')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Slug EN</label>
                            <input name="slug_en" id="slug_en" class="form-control" value="<?= esc($fv('slug_en')) ?>">
                        </div>
                        <div class="col-md-12">
                            <label>Mô tả ngắn EN</label>
                            <textarea name="excerpt_en" class="form-control"><?= esc($fv('excerpt_en')) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2">
                            <strong>Nội dung EN</strong>
                            <div class="help">Có thể copy toàn bộ từ VI rồi sửa text sang tiếng Anh. Cấu trúc ảnh sẽ giữ nguyên.</div>
                        </div>
                        <textarea name="content_en" class="d-none js-editor-source"><?= esc($fv('content_en')) ?></textarea>
                        <div class="editor-shell js-editor-shell">
                            <div class="editor-toolbar">
                                <button type="button" class="editor-upload-button js-editor-upload">▣ Chèn ảnh</button>
                                <button type="button" data-command="bold">B</button>
                                <button type="button" data-command="italic">I</button>
                                <button type="button" data-command="formatBlock" data-value="p" title="Đoạn văn thường · Ctrl+Alt+0">P <span class="editor-shortcut">Ctrl Alt 0</span></button>
                                <button type="button" data-command="formatBlock" data-value="h2" title="Heading 2 · Ctrl+Alt+2">H2 <span class="editor-shortcut">Ctrl Alt 2</span></button>
                                <button type="button" data-command="formatBlock" data-value="h3" title="Heading 3 · Ctrl+Alt+3">H3 <span class="editor-shortcut">Ctrl Alt 3</span></button>
                                <button type="button" data-command="insertUnorderedList">Danh sách</button>
                                <button type="button" data-command="insertOrderedList">1. 2. 3.</button>
                                <button type="button" data-command="formatBlock" data-value="blockquote">Trích dẫn</button>
                                <button type="button" data-command="createLink">Link</button>
                                <button type="button" data-command="removeFormat">Xóa định dạng</button>
                                <span class="editor-toolbar-spacer"></span>
                                <button type="button" class="editor-mode-button is-active" data-editor-mode="preview">Preview</button>
                                <button type="button" class="editor-mode-button" data-editor-mode="code">Code</button>
                            </div>
                            <div class="editor-area js-editor" contenteditable="true" data-placeholder="Write the English blog content here..."></div>
                            <textarea class="editor-code js-editor-code" spellcheck="false" aria-label="HTML code EN"></textarea>
                        </div>
                        <div class="editor-actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="copyViToEn">Copy toàn bộ từ VI sang EN</button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="section-seo" class="form-section is-step-hidden" data-blog-step-panel="4">
                <h2 class="section-title">SEO & xuất bản</h2>
                <div class="section-meta">Kiểm tra nội dung hiển thị trên Google rồi chọn trạng thái bài viết.</div>

                <div class="publish-panel">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label>Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?= $fv('status') === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                <option value="published" <?= $fv('status', 'published') === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>Thời điểm xuất bản</label>
                            <input type="datetime-local" name="published_at" class="form-control" value="<?= esc($fv('published_at')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?= (int) $fv('is_featured') === 1 ? 'checked' : '' ?>>
                                <span class="form-check-label">Bài nổi bật</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="lang-tabs" data-tab-group="seo">
                    <button type="button" class="lang-tab is-active" data-tab-target="seo-vi">SEO tiếng Việt</button>
                    <button type="button" class="lang-tab" data-tab-target="seo-en">SEO English</button>
                </div>
                <div class="lang-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="fillSeoFromContent">Điền SEO từ nội dung</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copySeoViToEn">Sao chép SEO VI sang EN</button>
                </div>
                <div class="lang-card lang-panel is-active" data-tab-panel="seo-vi">
                    <div class="row g-3">
                        <div class="col-md-6"><label>Meta title VI</label><input name="meta_title_vi" class="form-control" value="<?= esc($fv('meta_title_vi')) ?>"></div>
                        <div class="col-md-6"><label>Meta description VI</label><textarea name="meta_description_vi" class="form-control"><?= esc($fv('meta_description_vi')) ?></textarea></div>
                    </div>
                </div>
                <div class="lang-card lang-panel" data-tab-panel="seo-en">
                    <div class="row g-3">
                        <div class="col-md-6"><label>Meta title EN</label><input name="meta_title_en" class="form-control" value="<?= esc($fv('meta_title_en')) ?>"></div>
                        <div class="col-md-6"><label>Meta description EN</label><textarea name="meta_description_en" class="form-control"><?= esc($fv('meta_description_en')) ?></textarea></div>
                    </div>
                </div>
            </section>

            <div id="section-actions" class="sticky-action-bar">
                <div>
                    <div class="fw-semibold" id="stickyBlogStepTitle">Bước 1/4 · Thông tin bài viết</div>
                    <div class="meta">Bài viết được tự lưu tạm trên trình duyệt trong lúc nhập.</div>
                </div>
                <div class="d-flex justify-content-end gap-2 flex-wrap">
                    <div class="step-nav-actions">
                        <button type="button" class="btn btn-outline-secondary" id="previousBlogStep">Quay lại</button>
                        <button type="button" class="btn btn-outline-primary" id="nextBlogStep">Tiếp theo</button>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= esc($submitLabel ?? 'Lưu blog') ?></button>
                </div>
            </div>
        </form>
        </div>
        </div>
    </div>
</main>

<input type="file" id="editorImagePicker" class="d-none" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

<script>
(() => {
    const uploadUrl = <?= json_encode(site_url('admin/blogs/upload-image'), JSON_UNESCAPED_UNICODE) ?>;
    const csrfName = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;
    const csrfHash = <?= json_encode(csrf_hash(), JSON_UNESCAPED_UNICODE) ?>;
    const imagePicker = document.getElementById('editorImagePicker');
    const form = document.getElementById('blogForm');
    const draftStorageKey = 'blog-form-draft-<?= $blogId ? 'edit-' . (int) $blogId : 'create' ?>';
    const blogStepStorageKey = `${draftStorageKey}-active-step`;
    const blogStepCount = 4;
    let activeEditor = null;
    let savedRange = null;
    let draftSaveTimer = null;
    let activeBlogStep = 1;

    function syncStickyOffset() {
        const topbar = document.querySelector('.admin-topbar');
        const topbarHeight = topbar ? Math.ceil(topbar.getBoundingClientRect().height) : 72;
        document.documentElement.style.setProperty('--blog-sticky-top', `${topbarHeight + 12}px`);
    }

    syncStickyOffset();
    window.addEventListener('resize', syncStickyOffset, { passive: true });
    const adminTopbar = document.querySelector('.admin-topbar');
    if (adminTopbar && 'ResizeObserver' in window) {
        new ResizeObserver(syncStickyOffset).observe(adminTopbar);
    }

    function saveRange() {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;
        savedRange = selection.getRangeAt(0).cloneRange();
    }

    function restoreRange(editor) {
        editor.focus();
        const selection = window.getSelection();
        if (savedRange && selection) {
            selection.removeAllRanges();
            selection.addRange(savedRange);
            return;
        }

        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function syncEditor(shell) {
        const source = shell.parentElement.querySelector('.js-editor-source');
        const editor = shell.querySelector('.js-editor');
        const code = shell.querySelector('.js-editor-code');
        if (!source || !editor) return;

        if (shell.classList.contains('is-code-mode') && code) {
            source.value = code.value.trim();
            return;
        }

        source.value = editor.innerHTML.trim();
        if (code) code.value = source.value;
    }

    function setEditorMode(shell, mode) {
        const source = shell.parentElement.querySelector('.js-editor-source');
        const editor = shell.querySelector('.js-editor');
        const code = shell.querySelector('.js-editor-code');
        if (!source || !editor || !code) return;

        if (mode === 'code') {
            syncEditor(shell);
            code.value = source.value;
            shell.classList.add('is-code-mode');
            code.focus();
        } else {
            source.value = code.value.trim();
            editor.innerHTML = source.value;
            shell.classList.remove('is-code-mode');
            editor.focus();
        }

        shell.querySelectorAll('[data-editor-mode]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.editorMode === mode);
        });

        refreshSummary();
        scheduleDraftSave();
    }

    function bindEditor(shell) {
        const source = shell.parentElement.querySelector('.js-editor-source');
        const editor = shell.querySelector('.js-editor');
        const code = shell.querySelector('.js-editor-code');

        editor.innerHTML = source.value || '';
        if (code) code.value = source.value || '';
        editor.addEventListener('input', () => {
            syncEditor(shell);
            refreshSummary();
            scheduleDraftSave();
        });
        editor.addEventListener('blur', () => {
            saveRange();
            syncEditor(shell);
        });
        editor.addEventListener('keyup', saveRange);
        editor.addEventListener('mouseup', saveRange);
        editor.addEventListener('keydown', (event) => {
            const usesBlockShortcut = (event.ctrlKey || event.metaKey)
                && event.altKey
                && (event.key === '0' || event.key === '2' || event.key === '3');
            if (!usesBlockShortcut) return;

            event.preventDefault();
            const blockType = event.key === '0' ? 'p' : (event.key === '2' ? 'h2' : 'h3');
            document.execCommand('formatBlock', false, blockType);
            syncEditor(shell);
            saveRange();
            refreshSummary();
            scheduleDraftSave();
        });

        shell.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('click', () => {
                if (shell.classList.contains('is-code-mode')) return;
                restoreRange(editor);

                if (button.dataset.command === 'createLink') {
                    const url = window.prompt('Nhập link');
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                } else if (button.dataset.command === 'formatBlock') {
                    document.execCommand('formatBlock', false, button.dataset.value);
                } else {
                    document.execCommand(button.dataset.command, false, null);
                }

                syncEditor(shell);
                saveRange();
                refreshSummary();
                scheduleDraftSave();
            });
        });

        code?.addEventListener('input', () => {
            syncEditor(shell);
            refreshSummary();
            scheduleDraftSave();
        });

        shell.querySelectorAll('[data-editor-mode]').forEach((button) => {
            button.addEventListener('click', () => setEditorMode(shell, button.dataset.editorMode || 'preview'));
        });

        shell.parentElement.querySelector('.js-editor-upload').addEventListener('click', () => {
            if (shell.classList.contains('is-code-mode')) {
                setEditorMode(shell, 'preview');
            }
            activeEditor = editor;
            restoreRange(editor);
            imagePicker.click();
        });
    }

    function insertImage(editor, url, altText = '') {
        restoreRange(editor);
        const safeAlt = altText.replace(/"/g, '&quot;');
        const figureHtml = `<figure><img src="${url}" alt="${safeAlt}" loading="lazy">${altText ? `<figcaption>${altText}</figcaption>` : ''}</figure><p><br></p>`;
        document.execCommand('insertHTML', false, figureHtml);
        const shell = editor.closest('.js-editor-shell');
        const html = editor.innerHTML.trim();
        shell.parentElement.querySelector('.js-editor-source').value = html;
        const code = shell.querySelector('.js-editor-code');
        if (code) code.value = html;
        refreshSummary();
        scheduleDraftSave();
    }

    function bindLangTabs() {
        document.querySelectorAll('[data-tab-group]').forEach(group => {
            group.querySelectorAll('[data-tab-target]').forEach(button => {
                button.addEventListener('click', () => activateTab(group, button.getAttribute('data-tab-target')));
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

    function slugify(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function bindAutoSlug(nameId, slugId) {
        const nameInput = document.getElementById(nameId);
        const slugInput = document.getElementById(slugId);
        if (!nameInput || !slugInput) return;
        let manuallyEdited = slugInput.value.trim() !== '';
        slugInput.addEventListener('input', () => {
            manuallyEdited = slugInput.value.trim() !== '';
            scheduleDraftSave();
            refreshSummary();
        });
        nameInput.addEventListener('input', () => {
            if (!manuallyEdited) slugInput.value = slugify(nameInput.value);
            scheduleDraftSave();
            refreshSummary();
        });
    }

    function copyInputValue(fromName, toName) {
        const from = document.querySelector(`[name="${fromName}"]`);
        const to = document.querySelector(`[name="${toName}"]`);
        if (!from || !to) return;
        to.value = from.value;
        to.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function bindImageInputs() {
        document.querySelectorAll('.js-image-input').forEach(input => {
            input.addEventListener('change', () => {
                const previewSelector = input.getAttribute('data-preview');
                const preview = previewSelector ? document.querySelector(previewSelector) : null;
                const empty = input.parentElement?.querySelector('.js-preview-empty');
                const file = input.files?.[0];
                if (!preview || !(preview instanceof HTMLImageElement)) return;
                if (!file) {
                    preview.removeAttribute('src');
                    preview.style.display = 'none';
                    if (empty) empty.style.display = '';
                    refreshSummary();
                    return;
                }
                preview.src = URL.createObjectURL(file);
                preview.style.display = '';
                if (empty) empty.style.display = 'none';
                refreshSummary();
                scheduleDraftSave();
            });
        });
    }

    function refreshSummary() {
        const title = document.getElementById('title_vi')?.value.trim() || 'Chưa có tiêu đề';
        const slug = document.getElementById('slug_vi')?.value.trim() || 'chua-co-slug';
        const status = document.querySelector('[name="status"]')?.value || 'published';
        const publishedAt = document.querySelector('[name="published_at"]')?.value || 'Chưa có lịch';
        const contentVi = document.querySelector('[name="content_vi"]')?.value || '';
        const contentEn = document.querySelector('[name="content_en"]')?.value || '';
        const seoFilled = [
            document.querySelector('[name="meta_title_vi"]')?.value,
            document.querySelector('[name="meta_description_vi"]')?.value,
            document.querySelector('[name="meta_title_en"]')?.value,
            document.querySelector('[name="meta_description_en"]')?.value
        ].filter(Boolean).length;
        const imageCount = Array.from(document.querySelectorAll('.preview-card img'))
            .filter(image => String(image.getAttribute('src') || '').trim() !== '').length;

        document.getElementById('summaryTitle').textContent = title;
        document.getElementById('summarySlug').textContent = slug;
        document.getElementById('summaryStatus').textContent = status === 'published' ? 'Đã xuất bản' : 'Bản nháp';
        document.getElementById('summaryPublishedAt').textContent = publishedAt;
        document.getElementById('metricViLength').textContent = String(contentVi.replace(/<[^>]+>/g, '').trim().length);
        document.getElementById('metricEnLength').textContent = String(contentEn.replace(/<[^>]+>/g, '').trim().length);
        document.getElementById('metricImages').textContent = String(imageCount);
        document.getElementById('metricSeo').textContent = String(seoFilled);
    }

    function stepHasRequiredValues(step) {
        const panels = Array.from(document.querySelectorAll(`[data-blog-step-panel="${step}"]`));
        const requiredFields = panels.flatMap(panel => Array.from(panel.querySelectorAll('[required]')));
        if (!requiredFields.length) return false;
        return requiredFields.every(field => String(field.value || '').trim() !== '');
    }

    function stepHasArticleContent() {
        const content = document.querySelector('[name="content_vi"]')?.value || '';
        return stepHasRequiredValues(2) && content.replace(/<[^>]+>/g, '').trim().length > 0;
    }

    function stepHasImage() {
        const currentImages = Array.from(form.querySelectorAll('[name^="current_"]'))
            .some(field => String(field.value || '').trim() !== '');
        const newImages = Array.from(form.querySelectorAll('.js-image-input'))
            .some(field => (field.files?.length || 0) > 0);
        return currentImages || newImages;
    }

    function stepHasSeo() {
        return ['meta_title_vi', 'meta_description_vi'].every(name => {
            const field = form.querySelector(`[name="${name}"]`);
            return String(field?.value || '').trim() !== '';
        });
    }

    function updateBlogStepCompletion() {
        document.querySelectorAll('[data-blog-step]').forEach(button => {
            const step = Number(button.dataset.blogStep || 1);
            let complete = stepHasRequiredValues(step);
            if (step === 2) complete = stepHasArticleContent();
            if (step === 3) complete = stepHasImage();
            if (step === 4) complete = stepHasSeo();
            button.classList.toggle('is-complete', complete);
        });
    }

    function activateBlogStep(step, options = {}) {
        const normalizedStep = Math.min(blogStepCount, Math.max(1, Number(step) || 1));
        const button = document.querySelector(`[data-blog-step="${normalizedStep}"]`);
        if (!button) return;

        activeBlogStep = normalizedStep;
        document.querySelectorAll('[data-blog-step-panel]').forEach(panel => {
            const isActive = Number(panel.dataset.blogStepPanel) === normalizedStep;
            panel.classList.toggle('is-step-hidden', !isActive);
            panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });
        document.querySelectorAll('[data-blog-step]').forEach(stepButton => {
            const isActive = stepButton === button;
            stepButton.classList.toggle('is-active', isActive);
            stepButton.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        const title = button.dataset.stepTitle || '';
        document.getElementById('activeBlogStepKicker').textContent = `Bước ${normalizedStep} / ${blogStepCount}`;
        document.getElementById('activeBlogStepTitle').textContent = title;
        document.getElementById('activeBlogStepDescription').textContent = button.dataset.stepDescription || '';
        document.getElementById('activeBlogStepBadge').textContent = button.dataset.stepBadge || '';
        document.getElementById('stickyBlogStepTitle').textContent = `Bước ${normalizedStep}/${blogStepCount} · ${title}`;
        document.getElementById('blogProgressBar').style.width = `${(normalizedStep / blogStepCount) * 100}%`;
        document.getElementById('blogProgressLabel').textContent = `Bước ${normalizedStep}/${blogStepCount}`;

        const previousButton = document.getElementById('previousBlogStep');
        const nextButton = document.getElementById('nextBlogStep');
        previousButton.disabled = normalizedStep === 1;
        nextButton.disabled = normalizedStep === blogStepCount;
        nextButton.textContent = normalizedStep === blogStepCount ? 'Đã tới bước cuối' : 'Tiếp theo';
        updateBlogStepCompletion();

        try { sessionStorage.setItem(blogStepStorageKey, String(normalizedStep)); } catch (error) {}
        if (options.scroll !== false) {
            document.querySelector('.blog-step-heading')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function initBlogSteps() {
        document.querySelectorAll('[data-blog-step]').forEach(button => {
            button.addEventListener('click', () => activateBlogStep(button.dataset.blogStep));
        });
        document.getElementById('previousBlogStep')?.addEventListener('click', () => activateBlogStep(activeBlogStep - 1));
        document.getElementById('nextBlogStep')?.addEventListener('click', () => activateBlogStep(activeBlogStep + 1));
        form.addEventListener('invalid', event => {
            const panel = event.target.closest('[data-blog-step-panel]');
            if (!panel) return;
            activateBlogStep(panel.dataset.blogStepPanel, { scroll: false });
            window.setTimeout(() => event.target.focus({ preventScroll: false }), 0);
        }, true);

        let initialStep = 1;
        const errorAlert = document.querySelector('[data-blog-form-errors]');
        if (errorAlert) {
            const firstMissingRequired = Array.from(form.querySelectorAll('[required]'))
                .find(field => String(field.value || '').trim() === '');
            initialStep = Number(firstMissingRequired?.closest('[data-blog-step-panel]')?.dataset.blogStepPanel || 1);
        } else {
            try { initialStep = Number(sessionStorage.getItem(blogStepStorageKey) || 1); } catch (error) {}
        }
        activateBlogStep(initialStep, { scroll: false });
    }

    function serializeFormDraft() {
        const data = {};
        new FormData(form).forEach((value, key) => {
            if (value instanceof File) return;
            data[key] = String(value);
        });
        return { saved_at: new Date().toISOString(), data };
    }

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
            document.querySelectorAll('.js-editor-shell').forEach(shell => {
                const source = shell.parentElement.querySelector('.js-editor-source');
                const editor = shell.querySelector('.js-editor');
                const code = shell.querySelector('.js-editor-code');
                if (source && editor) editor.innerHTML = source.value || '';
                if (source && code) code.value = source.value || '';
                shell.classList.remove('is-code-mode');
                shell.querySelectorAll('[data-editor-mode]').forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.editorMode === 'preview');
                });
            });
            refreshSummary();
            updateBlogStepCompletion();
            bar.classList.add('d-none');
        });

        clearButton.addEventListener('click', () => {
            localStorage.removeItem(draftStorageKey);
            bar.classList.add('d-none');
        });
    }

    document.querySelectorAll('.js-editor-shell').forEach(bindEditor);
    bindLangTabs();
    bindImageInputs();
    bindAutoSlug('title_vi', 'slug_vi');
    bindAutoSlug('title_en', 'slug_en');

    imagePicker.addEventListener('change', async () => {
        if (!activeEditor || !imagePicker.files || imagePicker.files.length === 0) return;

        const file = imagePicker.files[0];
        const formData = new FormData();
        formData.append('editor_image', file);
        formData.append(csrfName, csrfHash);

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Upload image failed.');
            }

            const caption = window.prompt('Caption / alt text cho ảnh', '') || '';
            insertImage(activeEditor, data.url, caption);
        } catch (error) {
            window.alert(error.message || 'Upload ảnh thất bại.');
        } finally {
            imagePicker.value = '';
        }
    });

    document.getElementById('copyViToEn')?.addEventListener('click', () => {
        const editors = document.querySelectorAll('.js-editor');
        const sources = document.querySelectorAll('.js-editor-source');
        if (editors.length < 2 || sources.length < 2) return;
        editors[1].innerHTML = editors[0].innerHTML;
        sources[1].value = editors[0].innerHTML.trim();
        const enShell = editors[1].closest('.js-editor-shell');
        const enCode = enShell?.querySelector('.js-editor-code');
        if (enCode) enCode.value = sources[1].value;
        activateTab(document.querySelector('[data-tab-group="content"]'), 'content-en');
        refreshSummary();
        scheduleDraftSave();
    });

    document.getElementById('copyContentViToEn')?.addEventListener('click', () => {
        copyInputValue('title_vi', 'title_en');
        copyInputValue('slug_vi', 'slug_en');
        copyInputValue('excerpt_vi', 'excerpt_en');
        activateTab(document.querySelector('[data-tab-group="content"]'), 'content-en');
    });

    document.getElementById('copySeoViToEn')?.addEventListener('click', () => {
        copyInputValue('meta_title_vi', 'meta_title_en');
        copyInputValue('meta_description_vi', 'meta_description_en');
        activateTab(document.querySelector('[data-tab-group="seo"]'), 'seo-en');
    });

    document.getElementById('fillSeoFromContent')?.addEventListener('click', () => {
        [
            ['title_vi', 'meta_title_vi'],
            ['excerpt_vi', 'meta_description_vi'],
            ['title_en', 'meta_title_en'],
            ['excerpt_en', 'meta_description_en']
        ].forEach(([sourceName, targetName]) => {
            const source = form.querySelector(`[name="${sourceName}"]`);
            const target = form.querySelector(`[name="${targetName}"]`);
            if (!source || !target || String(target.value || '').trim() !== '') return;
            target.value = source.value;
            target.dispatchEvent(new Event('input', { bubbles: true }));
        });
        activateTab(document.querySelector('[data-tab-group="seo"]'), 'seo-vi');
    });

    form.addEventListener('input', () => {
        refreshSummary();
        updateBlogStepCompletion();
        scheduleDraftSave();
    });

    form.addEventListener('change', () => {
        refreshSummary();
        updateBlogStepCompletion();
        scheduleDraftSave();
    });

    form.addEventListener('submit', () => {
        document.querySelectorAll('.js-editor-shell').forEach(syncEditor);
        localStorage.removeItem(draftStorageKey);
    });

    refreshSummary();
    initDraftRestore();
    initBlogSteps();
})();
</script>
<?= view('admin/partials/app_end') ?>
</body>
</html>
