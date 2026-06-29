<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="bingo-base-url" content="<?= esc(rtrim(site_url(), '/') . '/') ?>">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/bingo/css/bingo.css') . '?v=20260629-rules') ?>" rel="stylesheet">
</head>
<body class="bingo-app">
<main class="bingo-shell d-flex align-items-center py-4" data-bingo-entry data-target-path="<?= esc($targetPath) ?>">
    <div class="container">
        <section class="bingo-card p-4 mx-auto" style="max-width: 520px;">
            <div class="d-flex align-items-center gap-3 mb-4">
                    <img class="bingo-logo" src="<?= esc(base_url('assets/images/logo.svg')) ?>" alt="TravelPlus">
                <div>
                    <h1 class="h4 mb-0"><?= esc($title) ?></h1>
                    <div class="text-muted">Nhập mã phòng để tiếp tục</div>
                </div>
            </div>

            <form id="roomEntryForm">
                <label class="form-label" for="roomCodeEntry">Mã phòng</label>
                <input id="roomCodeEntry" class="form-control form-control-lg text-uppercase" placeholder="Nhập mã phòng, ví dụ: TP-BINGO-001" required autocomplete="off">
                <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">Tiếp tục</button>
            </form>

            <?php if (! empty($allowCreate)): ?>
                <div class="border-top mt-4 pt-4">
                    <label class="form-label" for="newRoomCode">Tạo phòng</label>
                    <div class="input-group input-group-lg">
                        <input id="newRoomCode" class="form-control text-uppercase" placeholder="Nhập mã phòng muốn tạo">
                        <button id="quickCreateRoomBtn" class="btn btn-outline-primary" type="button">Tạo</button>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <?php if (($mode ?? '') === 'display'): ?>
            <div class="mx-auto mt-3" style="max-width: 920px;">
                <?= view('bingo/rules') ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<script type="module" src="<?= esc(base_url('assets/bingo/js/entry.js')) ?>"></script>
</body>
</html>
