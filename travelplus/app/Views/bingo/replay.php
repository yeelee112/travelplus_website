<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xem lại Bingo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="bingo-app">
<main class="container py-4">
    <?php
    $eventLabels = [
        'GAME_CREATED' => 'Tạo game',
        'ROOM_OPENED' => 'Mở phòng',
        'GAME_STARTED' => 'Bắt đầu game',
        'PLAYER_JOINED' => 'Người chơi tham gia',
        'PLAYER_LEFT' => 'Người chơi rời phòng',
        'BOARD_REGENERATED' => 'Đổi bảng số',
        'NUMBER_DRAWN' => 'Xổ số',
        'NUMBER_MARKED' => 'Đánh dấu số',
        'READY_BINGO' => 'Có thể Bingo',
        'PLAYER_BINGO' => 'Người chơi bấm Bingo',
        'WINNER_CONFIRMED' => 'Xác nhận người thắng',
        'GAME_FINISHED' => 'Kết thúc game',
        'GAME_RESET' => 'Chơi lại',
    ];
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Xem lại #<?= esc((string) $gameId) ?></h1>
        <a class="btn btn-outline-secondary" href="<?= esc(site_url('history')) ?>">Lịch sử</a>
    </div>
    <form class="bingo-card p-3 mb-3" method="get">
        <label class="form-label" for="filter">Bộ lọc</label>
        <select id="filter" name="filter" class="form-select" onchange="this.form.submit()">
            <?php foreach (['' => 'Tất cả', 'draw' => 'Xổ số', 'player' => 'Người chơi', 'ready' => 'Có thể Bingo', 'winner' => 'Người thắng'] as $value => $label): ?>
                <option value="<?= esc($value) ?>" <?= $filter === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <section class="bingo-card p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($events as $event): ?>
                <?php $data = json_decode((string) ($event['event_data'] ?? '{}'), true) ?: []; ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between gap-3">
                        <strong><?= esc($eventLabels[$event['event_type']] ?? $event['event_type']) ?></strong>
                        <span class="text-muted"><?= esc(date('H:i:s', strtotime($event['created_at']))) ?></span>
                    </div>
                    <div><?= esc($event['player_name'] ?? '') ?></div>
                    <?php if ($data !== []): ?>
                        <pre class="small bg-light p-2 mt-2 mb-0"><?= esc(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
