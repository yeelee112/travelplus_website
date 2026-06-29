<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="bingo-base-url" content="<?= esc(rtrim(site_url(), '/') . '/') ?>">
    <title>Người chơi Bingo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="bingo-app">
<main class="bingo-shell py-3" data-bingo-player data-room-code="<?= esc($roomCode) ?>">
    <div class="container">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img class="bingo-logo" src="<?= esc(base_url('assets/images/logo.svg')) ?>" alt="TravelPlus">
            <div>
                <h1 class="h4 mb-0">TravelPlus Bingo</h1>
                <div class="text-muted">Phòng: <strong><?= esc($roomCode) ?></strong></div>
            </div>
        </div>

        <section id="joinPanel" class="bingo-card p-3">
            <form id="joinForm">
                <label class="form-label" for="playerName">Tên người chơi</label>
                <input id="playerName" class="form-control form-control-lg" required maxlength="120" autocomplete="name">
                <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">Vào chơi</button>
            </form>
        </section>

        <section id="gamePanel" class="d-none">
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="bingo-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted">Người chơi</div>
                                <strong id="playerNameText"></strong>
                            </div>
                            <div class="text-end">
                                <div class="text-muted">Trạng thái</div>
                                <strong id="gameStatus">-</strong>
                            </div>
                        </div>
                        <div id="board" class="bingo-board"></div>
                        <div id="playerNotice" class="text-success fw-bold text-center mt-3"></div>
                        <button id="regenerateBoardBtn" class="btn btn-outline-primary w-100 mt-3" type="button" disabled>Đổi bảng số</button>
                        <button id="bingoBtn" class="btn btn-success btn-lg w-100 mt-3" type="button" disabled>Bingo</button>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="bingo-card p-3 mb-3 text-center">
                        <div class="text-muted">Số vừa xổ</div>
                        <div id="currentNumber" class="display-2 fw-bold">-</div>
                    </div>
                    <div class="bingo-card p-3">
                        <h2 class="h5">Các số đã xổ</h2>
                        <div id="drawnNumbers"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
<script type="module" src="<?= esc(base_url('assets/bingo/js/player.js')) ?>"></script>
</body>
</html>
