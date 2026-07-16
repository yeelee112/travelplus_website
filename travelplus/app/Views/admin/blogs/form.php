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
        .admin-shell { max-width:1240px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .form-section { border:1px solid #e6ebf0; border-radius:18px; background:#fff; padding:22px; margin-bottom:18px; scroll-margin-top:120px; }
        .section-title { font-size:18px; font-weight:700; margin:0 0 14px; }
        .section-meta { color:#6b778c; font-size:13px; margin:-6px 0 16px; }
        label { font-weight:600; margin-bottom:6px; }
        .help { color:#6b778c; font-size:13px; }
        .blog-form-nav { position:sticky; top:12px; z-index:10; background:rgba(244,246,248,.92); backdrop-filter:blur(8px); padding:10px 12px; border:1px solid #e6ebf0; border-radius:16px; margin-bottom:16px; }
        .blog-form-nav .nav { gap:8px; flex-wrap:wrap; }
        .blog-form-nav .nav-link { border:1px solid #d9e2ec; border-radius:999px; padding:7px 12px; color:#334155; font-weight:600; background:#fff; font-size:13px; }
        .blog-form-nav .nav-link:hover { background:#f8fafc; color:#0f172a; }
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
        .editor-shell { border:1px solid #d8e0ea; border-radius:14px; background:#fff; overflow:hidden; }
        .editor-toolbar { display:flex; gap:8px; flex-wrap:wrap; padding:12px; border-bottom:1px solid #e8edf3; background:#f8fafc; }
        .editor-toolbar button { border:1px solid #ccd7e3; background:#fff; border-radius:10px; padding:8px 12px; font-size:14px; }
        .editor-toolbar button:hover { background:#f3f7fb; }
        .editor-toolbar-spacer { flex:1 1 auto; }
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
        @media (max-width: 991px) {
            .blog-form-nav { position:static; }
            .live-summary { grid-template-columns:1fr; }
            .preview-grid { grid-template-columns:1fr; }
            .sticky-action-bar { flex-direction:column; align-items:stretch; }
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
            <div class="alert alert-danger">
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

        <div class="blog-form-nav">
            <div class="nav-head">
                <div class="title">Điều hướng nhanh</div>
                <div class="summary-pills">
                    <span class="summary-pill">Trạng thái: <?= esc($fv('status', 'published') === 'published' ? 'Đã xuất bản' : 'Bản nháp') ?></span>
                    <span class="summary-pill">Nổi bật: <?= (int) $fv('is_featured') === 1 ? 'Có' : 'Không' ?></span>
                    <span class="summary-pill">Tác giả: <?= esc($fv('author_name', 'Travel Plus')) ?></span>
                </div>
            </div>
            <div class="nav">
                <a class="nav-link" href="#section-main">Thông tin chính</a>
                <a class="nav-link" href="#section-media">Hình ảnh</a>
                <a class="nav-link" href="#section-content">Nội dung</a>
                <a class="nav-link" href="#section-actions">Lưu</a>
            </div>
            <div class="draft-status" id="draftStatusText">Tự lưu cục bộ: chưa có cập nhật mới.</div>
        </div>

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

        <form method="post" action="<?= esc($formAction ?? site_url('admin/blogs')) ?>" enctype="multipart/form-data" id="blogForm">
            <?= csrf_field() ?>

            <section id="section-main" class="form-section">
                <h2 class="section-title">Thông tin chính</h2>
                <div class="section-meta">Thông tin nền của bài viết: category, author, trạng thái và thời điểm xuất bản.</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Danh mục</label>
                        <input name="category" list="blogCategoryOptions" class="form-control" value="<?= esc($fv('category', 'Cảm hứng du lịch')) ?>" placeholder="Chọn hoặc nhập danh mục" required>
                        <datalist id="blogCategoryOptions">
                            <?php foreach ($categoryOptions as $categoryOption): ?>
                                <option value="<?= esc($categoryOption) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <div class="help mt-1">Gõ để lọc danh mục có sẵn hoặc nhập danh mục mới.</div>
                    </div>
                    <div class="col-md-4">
                        <label>Tác giả</label>
                        <input name="author_name" class="form-control" value="<?= esc($fv('author_name', 'Travel Plus')) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label>Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $fv('status') === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                            <option value="published" <?= $fv('status', 'published') === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" <?= (int) $fv('is_featured') === 1 ? 'checked' : '' ?>>
                            <span class="form-check-label">Bài nổi bật</span>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label>Thời điểm xuất bản</label>
                        <input type="datetime-local" name="published_at" class="form-control" value="<?= esc($fv('published_at')) ?>">
                    </div>
                </div>
            </section>

            <section id="section-media" class="form-section">
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

            <section id="section-content" class="form-section">
                <h2 class="section-title">Nội dung</h2>
                <div class="section-meta">Biên tập nội dung theo 2 ngôn ngữ trong cùng một khối để thao tác ngắn và rõ hơn.</div>
                <div class="lang-tabs" data-tab-group="content">
                    <button type="button" class="lang-tab is-active" data-tab-target="content-vi">Tiếng Việt</button>
                    <button type="button" class="lang-tab" data-tab-target="content-en">English</button>
                </div>
                <div class="lang-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyMetaViToEn">Sao chép VI sang EN</button>
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
                        <div class="col-md-6">
                            <label>Meta title VI</label>
                            <input name="meta_title_vi" class="form-control" value="<?= esc($fv('meta_title_vi')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Meta description VI</label>
                            <textarea name="meta_description_vi" class="form-control"><?= esc($fv('meta_description_vi')) ?></textarea>
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
                                <button type="button" data-command="bold">B</button>
                                <button type="button" data-command="italic">I</button>
                                <button type="button" data-command="formatBlock" data-value="h2">H2</button>
                                <button type="button" data-command="formatBlock" data-value="h3">H3</button>
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
                        <div class="editor-actions">
                            <button type="button" class="btn btn-outline-primary btn-sm js-editor-upload">Chèn ảnh</button>
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
                        <div class="col-md-6">
                            <label>Meta title EN</label>
                            <input name="meta_title_en" class="form-control" value="<?= esc($fv('meta_title_en')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Meta description EN</label>
                            <textarea name="meta_description_en" class="form-control"><?= esc($fv('meta_description_en')) ?></textarea>
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
                                <button type="button" data-command="bold">B</button>
                                <button type="button" data-command="italic">I</button>
                                <button type="button" data-command="formatBlock" data-value="h2">H2</button>
                                <button type="button" data-command="formatBlock" data-value="h3">H3</button>
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
                            <button type="button" class="btn btn-outline-primary btn-sm js-editor-upload">Chèn ảnh</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="copyViToEn">Copy toàn bộ từ VI sang EN</button>
                        </div>
                    </div>
                </div>
            </section>

            <div id="section-actions" class="sticky-action-bar">
                <div>
                    <div class="fw-semibold"><?= $blogId ? 'Đang sửa blog #' . (int) $blogId : 'Tạo blog mới' ?></div>
                    <div class="meta">Kiểm tra lại title, slug, ảnh đại diện và content song ngữ trước khi lưu.</div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary" href="<?= $blogId ? site_url('admin/blogs/' . (int) $blogId . '/edit') : site_url('admin/blogs/create') ?>">Đặt lại</a>
                    <button type="submit" class="btn btn-primary"><?= esc($submitLabel ?? 'Lưu blog') ?></button>
                </div>
            </div>
        </form>
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
    let activeEditor = null;
    let savedRange = null;
    let draftSaveTimer = null;

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
        const imageCount = document.querySelectorAll('.preview-card img[src]').length;

        document.getElementById('summaryTitle').textContent = title;
        document.getElementById('summarySlug').textContent = slug;
        document.getElementById('summaryStatus').textContent = status === 'published' ? 'Đã xuất bản' : 'Bản nháp';
        document.getElementById('summaryPublishedAt').textContent = publishedAt;
        document.getElementById('metricViLength').textContent = String(contentVi.replace(/<[^>]+>/g, '').trim().length);
        document.getElementById('metricEnLength').textContent = String(contentEn.replace(/<[^>]+>/g, '').trim().length);
        document.getElementById('metricImages').textContent = String(imageCount);
        document.getElementById('metricSeo').textContent = String(seoFilled);
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

    document.getElementById('copyMetaViToEn')?.addEventListener('click', () => {
        copyInputValue('title_vi', 'title_en');
        copyInputValue('slug_vi', 'slug_en');
        copyInputValue('excerpt_vi', 'excerpt_en');
        copyInputValue('meta_title_vi', 'meta_title_en');
        copyInputValue('meta_description_vi', 'meta_description_en');
        activateTab(document.querySelector('[data-tab-group="content"]'), 'content-en');
    });

    form.addEventListener('input', () => {
        refreshSummary();
        scheduleDraftSave();
    });

    form.addEventListener('change', () => {
        refreshSummary();
        scheduleDraftSave();
    });

    form.addEventListener('submit', () => {
        document.querySelectorAll('.js-editor-shell').forEach(syncEditor);
        localStorage.removeItem(draftStorageKey);
    });

    refreshSummary();
    initDraftRestore();
})();
</script>
<?= view('admin/partials/app_end') ?>
</body>
</html>
