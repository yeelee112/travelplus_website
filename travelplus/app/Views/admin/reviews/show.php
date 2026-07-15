<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Review Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1080px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .meta-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .meta-item { border:1px solid #edf1f5; border-radius:14px; padding:16px; background:#fbfcfe; }
        .meta-item small { color:#6b778c; display:block; margin-bottom:4px; }
        .review-body { white-space:pre-wrap; line-height:1.7; }
        .history-list { display:grid; gap:12px; }
        .history-item { border:1px solid #edf1f5; border-radius:14px; padding:14px 16px; background:#fbfcfe; }
        .history-item .top { display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'reviews'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Review #<?= esc((string) ($review['id'] ?? '')) ?></h1>
                <p class="text-muted mb-0">Chi tiết review và thao tác moderation.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Quay lại đánh giá</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="meta-grid mb-4">
            <div class="meta-item"><small>Tour</small><div class="fw-semibold"><?= esc((string) ($review['tour_name'] ?? '')) ?></div></div>
            <div class="meta-item"><small>Reviewer</small><div class="fw-semibold"><?= esc((string) ($review['reviewer_name'] ?? '')) ?></div><div><?= esc((string) ($review['reviewer_email'] ?? '')) ?></div></div>
            <div class="meta-item"><small>Trạng thái</small><div class="fw-semibold"><?= ($review['status'] ?? '') === 'approved' ? 'Đã duyệt' : (($review['status'] ?? '') === 'hidden' ? 'Đã ẩn' : 'Chờ duyệt') ?></div></div>
            <div class="meta-item"><small>Created</small><div class="fw-semibold"><?= esc(app_datetime((string) ($review['created_at'] ?? ''))) ?></div></div>
            <div class="meta-item"><small>Overall</small><div class="fw-semibold"><?= esc((string) ($review['rating_overall'] ?? '0')) ?></div></div>
            <div class="meta-item"><small>Destination / Transport / Value</small><div class="fw-semibold"><?= esc((string) ($review['rating_destination'] ?? '0')) ?> / <?= esc((string) ($review['rating_transport'] ?? '0')) ?> / <?= esc((string) ($review['rating_value'] ?? '0')) ?></div></div>
        </div>

        <div class="meta-item mb-4">
            <small>Title</small>
            <div class="fw-semibold"><?= esc((string) ($review['title'] ?? '')) ?></div>
        </div>

        <div class="meta-item mb-4">
            <small>Nội dung</small>
            <div class="review-body"><?= esc((string) ($review['content'] ?? '')) ?></div>
        </div>

        <div class="meta-item mb-4">
            <small>Cập nhật trạng thái</small>
            <div class="d-flex gap-2 flex-wrap mb-3">
                <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="approved">
                    <input type="hidden" name="redirect_to" value="show">
                    <button type="submit" class="btn btn-outline-success">Duyệt</button>
                </form>
                <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="pending">
                    <input type="hidden" name="redirect_to" value="show">
                    <button type="submit" class="btn btn-outline-warning">Chờ duyệt</button>
                </form>
                <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="hidden">
                    <input type="hidden" name="redirect_to" value="show">
                    <button type="submit" class="btn btn-outline-secondary">Ẩn</button>
                </form>
                <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/delete') ?>" onsubmit="return confirm('Xóa đánh giá này?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger">Xóa</button>
                </form>
            </div>
            <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="<?= esc((string) ($review['status'] ?? 'pending')) ?>">
                <input type="hidden" name="redirect_to" value="show">
                <textarea class="form-control mb-3" name="status_note" rows="3" placeholder="Ghi chú cho lần cập nhật này (không bắt buộc)"></textarea>
                <button type="submit" class="btn btn-primary">Lưu ghi chú</button>
            </form>
        </div>

        <div class="meta-item">
            <small>Lịch sử trạng thái</small>
            <?php if (empty($statusLogs)): ?>
                <div class="text-muted">Chưa có lịch sử thay đổi trạng thái hoặc bảng log chưa được tạo.</div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($statusLogs as $log): ?>
                        <div class="history-item">
                            <div class="top">
                                <div>
                                    <div class="fw-semibold">
                                        <?= esc((string) ($log['from_status'] ?? '')) !== '' ? esc((string) $log['from_status']) . ' -> ' : '' ?><?= esc((string) ($log['to_status'] ?? '')) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= esc((string) ($log['actor_name'] ?? 'System')) ?>
                                        <?= ! empty($log['actor_email']) ? ' - ' . esc((string) $log['actor_email']) : '' ?>
                                    </div>
                                </div>
                                <div class="text-muted small"><?= esc(app_datetime((string) ($log['created_at'] ?? ''))) ?></div>
                            </div>
                            <?php if (! empty($log['note'])): ?>
                                <div><?= nl2br(esc((string) $log['note'])) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
