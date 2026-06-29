<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử Bingo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="bingo-app">
<main class="container py-4">
    <h1 class="h3 mb-3">Lịch sử Bingo</h1>
    <section class="bingo-card table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Mã phòng</th>
                <th>Ngày tạo</th>
                <th>Người chơi</th>
                <th>Thời lượng</th>
                <th>Người thắng</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($games as $game): ?>
                <?php
                $duration = '-';
                if (! empty($game['started_at']) && ! empty($game['ended_at'])) {
                    $duration = gmdate('H:i:s', max(0, strtotime($game['ended_at']) - strtotime($game['started_at'])));
                }
                ?>
                <tr>
                    <td><?= esc($game['room_code']) ?></td>
                    <td><?= esc($game['created_at']) ?></td>
                    <td><?= esc((string) $game['player_count']) ?></td>
                    <td><?= esc($duration) ?></td>
                    <td><?= esc($game['winners'] ?: '-') ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= esc(site_url('replay/' . $game['id'])) ?>">Xem replay</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
