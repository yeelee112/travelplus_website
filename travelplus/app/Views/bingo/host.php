<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="bingo-base-url" content="<?= esc(rtrim(site_url(), '/') . '/') ?>">
    <title>Quản trị Bingo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="bingo-app">
<main class="bingo-shell py-3" data-bingo-host data-room-code="<?= esc($roomCode) ?>">
    <div class="container">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img class="bingo-logo" src="<?= esc(base_url('assets/images/logo.svg')) ?>" alt="TravelPlus">
            <div>
                <h1 class="h4 mb-0">Quản trị Bingo</h1>
                <div class="text-muted">Phòng: <strong id="roomCodeText"><?= esc($roomCode ?: '-') ?></strong></div>
            </div>
        </div>

        <section class="bingo-card p-3 mb-3">
            <label class="form-label" for="roomCodeInput">Mã phòng</label>
            <input id="roomCodeInput" class="form-control form-control-lg text-uppercase" placeholder="Nhập mã phòng, ví dụ: TP-BINGO-001">
            <div class="row g-2 mt-3">
                <div class="col-6 col-md-2"><button id="createRoomBtn" class="btn btn-primary w-100">Tạo phòng</button></div>
                <div class="col-6 col-md-2"><button id="openRoomBtn" class="btn btn-outline-primary w-100">Mở phòng</button></div>
                <div class="col-6 col-md-2"><button id="startGameBtn" class="btn btn-success w-100">Bắt đầu</button></div>
                <div class="col-6 col-md-2"><button id="drawNumberBtn" class="btn btn-warning w-100">Xổ số</button></div>
                <div class="col-6 col-md-2"><button id="resetGameBtn" class="btn btn-outline-danger w-100">Chơi lại</button></div>
                <div class="col-6 col-md-2"><button id="endGameBtn" class="btn btn-danger w-100">Kết thúc</button></div>
            </div>
        </section>

        <div class="row g-3">
            <div class="col-lg-4">
                <section class="bingo-card p-3 h-100">
                    <div class="text-muted">Trạng thái</div>
                    <div id="gameStatus" class="h3">-</div>
                    <div class="text-muted">Số vừa xổ</div>
                    <div id="currentNumber" class="display-1 fw-bold">-</div>
                </section>
            </div>
            <div class="col-lg-8">
                <section class="bingo-card p-3 h-100">
                    <h2 class="h5">Các số đã xổ</h2>
                    <div id="drawnNumbers"></div>
                </section>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-6 col-md-3"><div class="bingo-card p-3"><div class="text-muted">Người chơi</div><div id="playerCount" class="h2">0</div></div></div>
            <div class="col-6 col-md-3"><div class="bingo-card p-3"><div class="text-muted">Đang online</div><div id="onlineCount" class="h2">0</div></div></div>
            <div class="col-6 col-md-3"><div class="bingo-card p-3"><div class="text-muted">Đã offline</div><div id="offlineCount" class="h2">0</div></div></div>
            <div class="col-6 col-md-3"><div class="bingo-card p-3"><div class="text-muted">Sẵn sàng / Thắng</div><div><span id="readyCount" class="h2">0</span> / <span id="winnerCount" class="h2">0</span></div></div></div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4"><section class="bingo-card p-3"><h2 class="h5">Có thể Bingo</h2><ul id="readyPlayers" class="list-group list-group-flush"></ul></section></div>
            <div class="col-md-4"><section class="bingo-card p-3"><h2 class="h5">Người thắng</h2><ul id="winners" class="list-group list-group-flush"></ul></section></div>
            <div class="col-md-4"><section class="bingo-card p-3"><h2 class="h5">Người chơi</h2><ul id="players" class="list-group list-group-flush"></ul></section></div>
        </div>
    </div>
</main>
<script type="module" src="<?= esc(base_url('assets/bingo/js/host.js')) ?>"></script>
</body>
</html>
