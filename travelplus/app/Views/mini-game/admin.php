<!doctype html>
<html lang="vi" data-base="<?= site_url('mini-game/') ?>" data-role="admin" data-csrf="<?= esc($csrf) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bảng điều khiển MC · Đoán Biển Số</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/mini-game.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/mini-game-admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/mini-game-font-fix.css') ?>">
</head>
<body class="admin-body">
<main class="admin-shell">
    <header class="admin-header">
        <div>
            <div class="admin-eyebrow">TRAVEL PLUS · MINI GAME</div>
            <h1>Bảng điều khiển MC</h1>
        </div>
        <nav class="admin-links" aria-label="Liên kết nhanh">
            <a href="<?= site_url('mini-game/screen') ?>" target="_blank">▣ Mở màn hình chiếu</a>
            <a href="<?= site_url('mini-game/player') ?>" target="_blank">♟ Mở trang Player</a>
            <a href="<?= site_url('mini-game/questions') ?>">☰ Quản lý câu hỏi</a>
        </nav>
    </header>

    <section class="admin-statusbar" aria-label="Trạng thái game">
        <div class="status-block"><span>Trạng thái</span><strong class="game-status" data-status>CHỜ BẮT ĐẦU</strong></div>
        <div class="status-block"><span>Câu hiện tại</span><strong data-round>—</strong></div>
        <div class="status-block"><span>Đã giành quyền</span><strong><span data-buzz-count>0</span> người</strong></div>
        <div class="status-block timer-block"><span>Thời gian còn lại</span><strong><span data-timer>0</span> giây</strong></div>
    </section>

    <section class="admin-layout">
        <div class="admin-main">
            <article class="admin-panel question-panel">
                <div class="panel-heading">
                    <div><span class="step-number">1</span><div><small>CÂU HỎI ĐANG TRÌNH CHIẾU</small><h2 data-prompt>Chờ MC bắt đầu game</h2></div></div>
                    <span class="question-type" data-question-type>Chưa chọn dạng câu</span>
                </div>
                <div class="question-display" data-question>—</div>
                <div class="mc-answer"><span>Đáp án dành cho MC</span><strong data-admin-answer>—</strong></div>
            </article>

            <article class="admin-panel answering-panel">
                <div class="panel-heading compact">
                    <div><span class="step-number">2</span><div><small>NGƯỜI ĐƯỢC QUYỀN TRẢ LỜI</small><h2 data-answering>Chưa có người giành quyền</h2></div></div>
                </div>
                <div class="judge-help" data-judge-help>Chờ người chơi bấm “GIÀNH QUYỀN”.</div>
                <div class="judge-actions">
                    <button class="action-button correct" data-command="correct" data-admin-action="judge">✓ Trả lời đúng <small>Cộng 2 điểm</small></button>
                    <button class="action-button wrong" data-command="wrong" data-admin-action="judge">✕ Trả lời sai <small>Chuyển người kế tiếp</small></button>
                </div>
            </article>

            <article class="admin-panel flow-panel">
                <div class="panel-heading compact">
                    <div><span class="step-number">3</span><div><small>ĐIỀU KHIỂN VÒNG CHƠI</small><h2>Thao tác tiếp theo</h2></div></div>
                </div>
                <div class="flow-actions">
                    <button class="action-button start" data-command="start" data-admin-action="start">▶ Bắt đầu game</button>
                    <button class="action-button next" data-command="next" data-admin-action="next">Câu tiếp theo →</button>
                    <button class="secondary-action" data-command="reveal" data-admin-action="reveal">👁 Hiện đáp án trên màn chiếu</button>
                    <button class="secondary-action" data-command="reset_buzz" data-admin-action="reset-buzz">↻ Cho mọi người bấm lại</button>
                </div>
            </article>

            <article class="admin-panel settings-panel">
                <div class="settings-row">
                    <div><strong>Thời gian trả lời</strong><p>Chọn thời gian và bắt đầu đếm lại ngay.</p></div>
                    <div class="time-options" role="group" aria-label="Chọn thời gian đếm ngược">
                        <?php foreach ([5, 10, 15, 20, 30] as $n): ?>
                            <button data-command="countdown" data-seconds="<?= $n ?>"><?= $n ?>s</button>
                        <?php endforeach ?>
                    </div>
                </div>
            </article>
        </div>

        <aside class="admin-sidebar">
            <section class="admin-panel queue-panel">
                <div class="sidebar-heading"><div><small>THỨ TỰ GIÀNH QUYỀN</small><h2>Hàng chờ trả lời</h2></div><span class="live-dot">LIVE</span></div>
                <p class="sidebar-note">Người bấm sớm nhất được ưu tiên. Khi sai, quyền tự chuyển xuống người kế tiếp.</p>
                <div class="buzz-list admin-buzz-list" data-buzzes><p class="empty-state">Chưa có lượt bấm.</p></div>
            </section>

            <section class="admin-panel score-panel">
                <div class="sidebar-heading"><div><small>KẾT QUẢ</small><h2>Bảng điểm nhóm</h2></div></div>
                <div class="scores" data-scores><p class="empty-state">Chưa có điểm.</p></div>
            </section>

            <details class="admin-panel danger-panel">
                <summary>⚙ Công cụ reset</summary>
                <p>Chỉ dùng khi cần làm lại. Các thao tác này không thể hoàn tác.</p>
                <div class="danger-actions">
                    <button data-command="reset_scores" onclick="return confirm('Bạn chắc chắn muốn đưa toàn bộ điểm về 0?')">Reset toàn bộ điểm</button>
                    <button data-command="reset_game" onclick="return confirm('Bạn chắc chắn muốn kết thúc và reset toàn bộ game?')">Reset toàn bộ game</button>
                </div>
            </details>
        </aside>
    </section>
</main>
<script src="<?= base_url('assets/js/mini-game.js') ?>"></script>
</body>
</html>
