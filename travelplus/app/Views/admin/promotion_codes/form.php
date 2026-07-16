<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Mã khuyến mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1180px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:20px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .promo-hero__eyebrow { font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#0ea5e9; margin-bottom:8px; }
        .form-label { font-weight:600; }
        .promo-section { border:1px solid #edf1f5; border-radius:18px; padding:20px; background:#fbfcfe; }
        .promo-scope-option { border:1px solid #dbe5ef; border-radius:16px; padding:16px; background:#fff; height:100%; }
        .promo-tour-list { border:1px solid #dbe5ef; border-radius:16px; background:#fff; max-height:320px; overflow:auto; padding:14px; }
        .promo-tour-list.is-disabled { opacity:.5; pointer-events:none; }
        .promo-tour-item { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #f0f3f7; }
        .promo-tour-item:last-child { border-bottom:0; }
        .promo-tour-status { font-size:12px; border-radius:999px; padding:4px 8px; background:#eef6ff; color:#0b79d0; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'promotion_codes'; ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
            <div>
                <div class="promo-hero__eyebrow">Mã khuyến mãi</div>
                <h1 class="h3 mb-2"><?= esc((string) ($pageTitle ?? 'Promotion code')) ?></h1>
                <p class="text-muted mb-0">Thiết lập mức giảm, thời gian hiệu lực và phạm vi áp dụng cho toàn site hoặc một nhóm tour cụ thể.</p>
            </div>
            <a class="btn btn-outline-secondary" href="<?= site_url('admin/promotion-codes') ?>">Quay lại danh sách</a>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <form method="post" action="<?= esc((string) ($formAction ?? site_url('admin/promotion-codes'))) ?>">
            <?= csrf_field() ?>
            <?php
            $fv = static fn(string $key, $default = '') => old($key, $formData[$key] ?? $default);
            $selectedTourIds = array_map('intval', (array) ($selectedTourIds ?? []));
            ?>

            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="promo-section h-100">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" maxlength="50" value="<?= esc((string) $fv('code')) ?>" required>
                                <?php if (! empty($errors['code'])): ?><div class="text-danger small mt-1"><?= esc((string) $errors['code']) ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Tên hiển thị</label>
                                <input type="text" name="name" class="form-control" maxlength="150" value="<?= esc((string) $fv('name')) ?>" required>
                                <?php if (! empty($errors['name'])): ?><div class="text-danger small mt-1"><?= esc((string) $errors['name']) ?></div><?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"><?= esc((string) $fv('description')) ?></textarea>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Kiểu giảm</label>
                                <select name="discount_type" class="form-select">
                                    <option value="fixed"<?= (string) $fv('discount_type', 'fixed') === 'fixed' ? ' selected' : '' ?>>Số tiền cố định</option>
                                    <option value="percent"<?= (string) $fv('discount_type', 'fixed') === 'percent' ? ' selected' : '' ?>>Phần trăm</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Giá trị giảm</label>
                                <input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="<?= esc((string) $fv('discount_value')) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Giảm tối đa</label>
                                <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control" value="<?= esc((string) $fv('max_discount_amount')) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Đơn tối thiểu</label>
                                <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" value="<?= esc((string) $fv('min_order_amount', '0')) ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Giới hạn lượt dùng</label>
                                <input type="number" min="0" name="usage_limit" class="form-control" value="<?= esc((string) $fv('usage_limit', '0')) ?>">
                                <div class="form-text">Để 0 nếu không giới hạn.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bắt đầu</label>
                                <input type="datetime-local" name="starts_at" class="form-control" value="<?= esc((string) $fv('starts_at')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kết thúc</label>
                                <input type="datetime-local" name="ends_at" class="form-control" value="<?= esc((string) $fv('ends_at')) ?>">
                            </div>

                            <div class="col-md-4">
                                <div class="form-check mt-4 pt-2">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="promoCodeActive" <?= (int) $fv('is_active', 1) === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="promoCodeActive">Đang hoạt động</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Đã dùng</label>
                                <input type="text" class="form-control" value="<?= esc((string) ($formData['used_count'] ?? 0)) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="promo-section h-100">
                        <h2 class="h5 mb-3">Phạm vi áp dụng</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="promo-scope-option d-block">
                                    <input class="form-check-input me-2" type="radio" name="scope" value="all" <?= (string) $fv('scope', 'all') === 'all' ? 'checked' : '' ?>>
                                    <strong>Toàn site</strong>
                                    <div class="text-muted small mt-2">Mã áp dụng cho mọi tour hợp lệ.</div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="promo-scope-option d-block">
                                    <input class="form-check-input me-2" type="radio" name="scope" value="specific" <?= (string) $fv('scope', 'all') === 'specific' ? 'checked' : '' ?>>
                                    <strong>Theo tour</strong>
                                    <div class="text-muted small mt-2">Chỉ áp dụng cho các tour được tick bên dưới.</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lọc tour</label>
                            <input type="text" class="form-control" placeholder="Tìm theo tên tour..." data-tour-filter>
                        </div>

                        <div class="promo-tour-list" data-tour-list>
                            <?php if (empty($tourOptions)): ?>
                                <div class="text-muted">Chưa có danh sách tour để gán mã.</div>
                            <?php endif; ?>
                            <?php foreach (($tourOptions ?? []) as $tour): ?>
                                <label class="promo-tour-item" data-tour-item data-tour-name="<?= esc(strtolower((string) ($tour['name'] ?? '')), 'attr') ?>">
                                    <span class="me-3">
                                        <input class="form-check-input me-2" type="checkbox" name="tour_ids[]" value="<?= (int) ($tour['id'] ?? 0) ?>" <?= in_array((int) ($tour['id'] ?? 0), $selectedTourIds, true) ? 'checked' : '' ?>>
                                        <?= esc((string) ($tour['name'] ?? '')) ?>
                                    </span>
                                    <span class="promo-tour-status"><?= esc((string) ($tour['status'] ?? 'draft')) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-4">
                <button type="submit" class="btn btn-primary"><?= esc((string) ($submitLabel ?? 'Save')) ?></button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/promotion-codes') ?>">Quay lại</a>
            </div>
        </form>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterInput = document.querySelector('[data-tour-filter]');
    const items = Array.from(document.querySelectorAll('[data-tour-item]'));
    const scopeInputs = Array.from(document.querySelectorAll('input[name="scope"]'));
    const tourList = document.querySelector('[data-tour-list]');

    const syncScopeState = function () {
        const current = scopeInputs.find(function (input) {
            return input.checked;
        });
        const isSpecific = current && current.value === 'specific';

        if (tourList) {
            tourList.classList.toggle('is-disabled', !isSpecific);
        }
    };

    scopeInputs.forEach(function (input) {
        input.addEventListener('change', syncScopeState);
    });

    syncScopeState();

    if (!filterInput || items.length === 0) {
        return;
    }

    filterInput.addEventListener('input', function () {
        const keyword = filterInput.value.trim().toLowerCase();

        items.forEach(function (item) {
            const name = item.getAttribute('data-tour-name') || '';
            item.hidden = keyword !== '' && !name.includes(keyword);
        });
    });
});
</script>
<?= view('admin/partials/app_end') ?>
</body>
</html>
