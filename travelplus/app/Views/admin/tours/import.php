<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tạo tour v2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'tours']) ?>
<main class="container py-4" style="max-width:960px">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <div class="d-flex justify-content-between gap-3 mb-3">
            <div><h1 class="h3">Tạo tour v2</h1><p class="text-secondary">Từ chương trình Word đến tour chỉ với một lần nhập.</p></div>
            <a href="<?= site_url('admin/tours') ?>" class="btn btn-outline-secondary align-self-start">Quay lại</a>
        </div>
        <p><strong>1. Nhập chương trình</strong> → 2. Kiểm tra nội dung → 3. Lưu tour</p>
        <?php if (! empty($error)): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" action="<?= site_url('admin/tours/create-v2') ?>">
            <?= csrf_field() ?>
            <label for="document" class="form-label fw-semibold">Tải chương trình Word (.docx)</label>
            <input id="document" name="document" type="file" accept=".docx" class="form-control" aria-describedby="document-hint">
            <p id="document-hint" class="form-text">Tối đa 20 MB, tùy giới hạn máy chủ. Tự tách lịch trình theo “Ngày 1”, “Ngày 2”… và các mục bao gồm / không bao gồm.</p>
            <label for="document-text" class="form-label fw-semibold mt-3">Hoặc dán nội dung chương trình</label>
            <textarea id="document-text" name="document_text" rows="12" maxlength="500000" class="form-control" placeholder="Tên tour&#10;Ngày 1: …&#10;Nội dung tham quan…&#10;Ngày 2: …"><?= esc(old('document_text', '')) ?></textarea>
            <p class="form-text">Nếu chọn file, hệ thống sẽ đọc file. Giá, ngày khởi hành và hình ảnh được bổ sung ở bước kiểm tra.</p>
            <button class="btn btn-primary mt-2" type="submit">Đọc chương trình và điền tour</button>
        </form>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
