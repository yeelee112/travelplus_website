<?php
$url = static fn (array $params = []) => site_url('admin/chat-history') . '?' . http_build_query(array_merge([
    'date' => $report['date'], 'q' => $report['query'], 'page' => $report['page'],
], $params));
$active = $report['conversation'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin - Hội thoại AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        .chat-history{max-width:1400px;margin:24px auto;padding:0 20px;color:#243746}
        .chat-history h1{font-size:26px}.chat-history h2{font-size:18px}
        .chat-filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin:20px 0}
        .chat-filters label{display:block;margin-bottom:5px;font-weight:600}.chat-search{flex:1;min-width:180px}
        .chat-workspace{display:grid;grid-template-columns:320px minmax(0,1fr);border-top:1px solid #d9e3e9;background:#fff}
        .chat-list{border-right:1px solid #d9e3e9}.chat-session{display:block;padding:16px;color:#243746;text-decoration:none;border-bottom:1px solid #e4ebef;overflow-wrap:anywhere}
        .chat-session:hover,.chat-session.is-active{background:#eaf8ff;color:#006c9c}.chat-session.is-active{box-shadow:inset 3px 0 #009fdb}
        .chat-preview{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:7px 0}
        .chat-session small,.chat-meta{font-size:12px;color:#566a79}.chat-detail{padding:24px;min-width:0}
        .chat-message{margin:18px 0;padding:16px;border:1px solid #dce6eb;border-radius:8px;background:#f8fafb;max-width:92%}
        .chat-message.is-user{margin-left:auto;background:#edf9ff;border-color:#bce7fa}
        .chat-message p{white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.65;margin:8px 0 0}
        .chat-meta{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;overflow-wrap:anywhere}.chat-meta strong{color:#243746}
        .chat-pagination{display:flex;gap:16px;align-items:center;padding:16px;flex-wrap:wrap}
        @media(max-width:800px){.chat-workspace{grid-template-columns:minmax(0,1fr)}.chat-list{border-right:0;max-height:300px;overflow:auto;border-bottom:1px solid #d9e3e9}.chat-detail{padding:16px}.chat-message{max-width:100%}.chat-history{padding:0 12px}}
    </style>
</head>
<body>
<?= view('admin/partials/app_start', ['adminSection' => 'chat_history']) ?>
<main class="chat-history">
    <h1>Hội thoại AI</h1>
    <p class="text-secondary">Lịch sử được lưu tối đa 14 ngày. Số điện thoại và email trong tin nhắn được ẩn.</p>
    <form method="get" action="<?= esc(site_url('admin/chat-history'), 'attr') ?>" class="chat-filters">
        <div><label for="chat-date">Ngày trò chuyện</label><select class="form-select" id="chat-date" name="date">
            <?php foreach ($report['dates'] as $date): ?>
                <option value="<?= esc($date, 'attr') ?>" <?= $date === $report['date'] ? 'selected' : '' ?>><?= esc(date('d/m/Y', strtotime($date))) ?></option>
            <?php endforeach ?>
        </select></div>
        <div class="chat-search"><label for="chat-query">Nội dung cần tìm</label><input class="form-control" id="chat-query" name="q" maxlength="200" value="<?= esc($report['query'], 'attr') ?>" placeholder="Điểm đến, nhu cầu, câu trả lời..."></div>
        <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        <a class="btn btn-outline-secondary" href="<?= esc(site_url('admin/chat-history'), 'attr') ?>">Đặt lại</a>
    </form>
    <?php if ($report['limited']): ?><p class="alert alert-warning">Dữ liệu ngày này vượt giới hạn đọc. Danh sách hiện chỉ hiển thị một phần hội thoại.</p><?php endif ?>
    <p><?= (int) $report['total'] ?> phiên trò chuyện trong ngày <?= esc(date('d/m/Y', strtotime($report['date']))) ?></p>
    <div class="chat-workspace">
        <aside class="chat-list" aria-label="Danh sách hội thoại">
            <?php foreach ($report['conversations'] as $item): ?>
                <a class="chat-session <?= ($active['id'] ?? '') === $item['id'] ? 'is-active' : '' ?>" <?= ($active['id'] ?? '') === $item['id'] ? 'aria-current="true"' : '' ?> href="<?= esc($url(['conversation' => $item['id']]), 'attr') ?>">
                    <strong>Khách · <?= esc(substr($item['id'], 0, 8)) ?></strong>
                    <span class="chat-preview"><?= esc($item['preview']) ?></span>
                    <small><?= esc(date('H:i', $item['last']['time'])) ?> · <?= count($item['messages']) ?> tin nhắn</small>
                </a>
            <?php endforeach ?>
            <?php if ($report['total'] === 0): ?><p class="p-3">Không có hội thoại phù hợp.</p><?php endif ?>
            <?php if ($report['pages'] > 1): ?><nav class="chat-pagination" aria-label="Phân trang">
                <?php if ($report['page'] > 1): ?><a href="<?= esc($url(['page' => $report['page'] - 1]), 'attr') ?>">Trước</a><?php endif ?>
                <span><?= $report['page'] ?> / <?= $report['pages'] ?></span>
                <?php if ($report['page'] < $report['pages']): ?><a href="<?= esc($url(['page' => $report['page'] + 1]), 'attr') ?>">Sau</a><?php endif ?>
            </nav><?php endif ?>
        </aside>
        <section class="chat-detail" aria-label="Nội dung hội thoại">
            <?php if ($active): ?>
                <h2>Hội thoại · <?= esc(substr($active['id'], 0, 8)) ?></h2>
                <?php foreach ($active['messages'] as $message): ?>
                    <article class="chat-message <?= $message['role'] === 'user' ? 'is-user' : '' ?>">
                        <div class="chat-meta"><strong><?= $message['role'] === 'user' ? 'Khách hàng' : 'Travel Plus AI' ?></strong><span><?= esc(date('H:i:s', $message['time'])) ?> · <?= esc($message['locale']) ?></span></div>
                        <p><?= esc($message['message']) ?></p>
                        <?php if ($message['path'] !== ''): ?><div class="chat-meta mt-2">Trang: <?= esc($message['path']) ?></div><?php endif ?>
                    </article>
                <?php endforeach ?>
            <?php else: ?><p>Chọn một hội thoại để xem nội dung trong ngày.</p><?php endif ?>
        </section>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
