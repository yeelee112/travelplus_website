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
        <form id="tourDocumentImport" method="post" enctype="multipart/form-data" action="<?= esc(parse_url(site_url('admin/tours/create-v2'), PHP_URL_PATH), 'attr') ?>">
            <?= csrf_field() ?>
            <label for="document" class="form-label fw-semibold">Tải chương trình Word (.docx)</label>
            <input id="document" name="document" type="file" accept=".docx" class="form-control" aria-describedby="document-hint">
            <p id="document-hint" class="form-text">Tối đa 20 MB, tùy giới hạn máy chủ. Tự tách lịch trình theo “Ngày 1”, “Ngày 2”… và các mục bao gồm / không bao gồm.</p>
            <label for="document-text" class="form-label fw-semibold mt-3">Hoặc dán nội dung chương trình</label>
            <textarea id="document-text" name="document_text" rows="12" maxlength="500000" class="form-control" placeholder="Tên tour&#10;Ngày 1: …&#10;Nội dung tham quan…&#10;Ngày 2: …"><?= esc(old('document_text', '')) ?></textarea>
            <p class="form-text">Nếu chọn file, hệ thống sẽ đọc file. Giá, ngày khởi hành và hình ảnh được bổ sung ở bước kiểm tra.</p>
            <div id="importError" class="alert alert-danger mt-3" role="alert" hidden></div>
            <button class="btn btn-primary mt-2" type="submit">Đọc chương trình và điền tour</button>
        </form>
    </div>
</main>
<script>
(() => {
    const form = document.getElementById('tourDocumentImport');
    const button = form.querySelector('[type="submit"]');
    const error = document.getElementById('importError');
    const originalLabel = button.textContent;
    let sending = false;
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (sending) return;
        sending = true;
        button.disabled = true;
        button.textContent = 'Đang chuẩn bị đọc chương trình…';
        error.hidden = true;
        try {
            const response = await fetch(form.action.replace(/\/$/, '') + '/token', {
                credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json'},
                signal: AbortSignal.timeout(15000),
            });
            if (response.redirected || !response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Phiên đăng nhập không còn hợp lệ. Mở trang đăng nhập trong tab khác, đăng nhập rồi quay lại bấm Đọc chương trình.');
            }
            const token = await response.json();
            const field = form.elements.namedItem(token.name);
            if (!field || typeof token.hash !== 'string' || !token.hash) throw new Error('Không lấy được mã bảo vệ. Vui lòng thử lại.');
            field.value = token.hash;
            const file = document.getElementById('document').files[0];
            if (file && file.size > token.uploadLimit) throw new Error('File vượt giới hạn tải lên của máy chủ (' + Math.floor(token.uploadLimit / 1024 / 1024) + ' MB). Hãy giảm dung lượng hoặc dán nội dung.');
            const bytes = (file?.size || 0) + new Blob([document.getElementById('document-text').value]).size + 16384;
            if (token.postLimit > 0 && bytes > token.postLimit) throw new Error('Tổng nội dung vượt giới hạn gửi của máy chủ. Hãy giảm dung lượng file hoặc nội dung dán.');
            button.textContent = 'Đang đọc chương trình…';
            HTMLFormElement.prototype.submit.call(form);
        } catch (exception) {
            error.textContent = exception instanceof TypeError || exception.name === 'TimeoutError'
                ? 'Không kết nối được máy chủ. Nội dung vẫn được giữ lại, vui lòng thử lại.' : exception.message;
            error.hidden = false;
            button.disabled = false;
            button.textContent = originalLabel;
            sending = false;
        }
    });
    window.addEventListener('pageshow', () => { sending = false; button.disabled = false; button.textContent = originalLabel; });
})();
</script>
<?= view('admin/partials/app_end') ?>
</body>
</html>
