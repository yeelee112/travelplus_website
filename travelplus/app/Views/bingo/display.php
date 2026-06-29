<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="bingo-base-url" content="<?= esc(rtrim(site_url(), '/') . '/') ?>">
    <title>Màn hình Bingo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="display-screen">
<main class="container-fluid py-4" data-bingo-display data-room-code="<?= esc($roomCode) ?>">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <img class="bingo-logo" src="<?= esc(base_url('assets/images/logo-white.svg')) ?>" alt="TravelPlus">
            <div>
                <h1 class="h2 mb-0">TravelPlus Bingo</h1>
                <div class="text-white-50">Phòng: <strong id="roomCodeText"><?= esc($roomCode) ?></strong></div>
            </div>
        </div>
        <div class="text-end">
            <div class="text-white-50">Trạng thái</div>
            <div id="gameStatus" class="h3">-</div>
        </div>
    </div>

    <div id="displayRulesPanel" class="mb-4">
        <?= view('bingo/rules', ['variant' => 'display']) ?>
    </div>

    <section class="text-center py-3">
        <div class="text-white-50 fs-3">Số vừa xổ</div>
        <div id="currentNumber" class="display-current">-</div>
        <button id="displayDrawNumberBtn" class="btn btn-warning btn-lg px-5 py-3 fw-bold" type="button" disabled>Xổ số</button>
    </section>

    <div id="gameOver" class="d-none text-center mb-4">
        <div class="display-3 fw-bold text-warning">KẾT THÚC</div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <section class="display-panel p-3 h-100">
                <h2 class="h4">Các số đã xổ</h2>
                <div id="drawnNumbers"></div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="display-panel p-3 mb-3">
                <div class="row text-center">
                    <div class="col"><div class="text-white-50">Người chơi</div><div id="playerCount" class="display-6 fw-bold">0</div></div>
                    <div class="col"><div class="text-white-50">Người thắng</div><div id="winnerCount" class="display-6 fw-bold">0</div></div>
                </div>
            </section>
            <section class="display-panel p-3">
                <h2 class="h4">3 người thắng đầu tiên</h2>
                <div id="topWinners"></div>
            </section>
        </div>
    </div>
</main>
<div id="winnerPopup" class="winner-popup">
    <div class="winner-popup__box">
        <div class="display-5 fw-bold">🎉 <span id="winnerPopupText"></span></div>
    </div>
</div>
<script type="module" src="<?= esc(base_url('assets/bingo/js/display.js')) ?>"></script>
</body>
</html>
