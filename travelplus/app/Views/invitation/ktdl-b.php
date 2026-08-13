<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#082c60">
    <title>Tạo thư mời kỷ niệm 30 năm lớp Kinh tế Du lịch B</title>
    <style>
        :root { color-scheme: light; --navy:#082c60; --blue:#123d78; --gold:#efa928; --ivory:#fffaf0; --red:#bd1018; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, "Helvetica Neue", sans-serif;
            color: var(--navy);
            background:
                radial-gradient(circle at 12% 0, rgba(255, 199, 77, .35), transparent 27rem),
                linear-gradient(145deg, #061e43 0, #0b356d 31%, #f8edcf 31.2%, #fffaf0 100%);
            background-attachment: fixed;
        }
        .page { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 52px; }
        .header { margin-bottom: 24px; text-align: center; }
        .eyebrow { margin: 0 0 8px; color: #ffd276; font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
        .header h1 { margin: 0 0 8px; color: #fff9e9; font: 700 clamp(26px, 4vw, 42px)/1.18 "Times New Roman", Times, serif; text-shadow: 0 2px 14px rgba(0,0,0,.25); }
        .header p { margin: 0; color: #f5e8c5; }
        .workspace { display: grid; grid-template-columns: minmax(280px, 350px) minmax(0, 1fr); gap: 28px; align-items: start; }
        .controls { position: sticky; top: 24px; padding: 25px; border: 1px solid rgba(239,169,40,.55); border-radius: 18px; background: rgba(255,251,240,.97); box-shadow: 0 18px 50px rgba(3,28,64,.22); }
        .controls h2 { margin: 0 0 20px; color: var(--navy); font-size: 22px; }
        label { display: block; margin-bottom: 8px; color: var(--navy); font-size: 14px; font-weight: 800; }
        input { width: 100%; min-height: 49px; padding: 11px 14px; border: 1px solid #c9ad69; border-radius: 10px; outline: none; color: var(--navy); background: #fff; font: 700 17px/1.3 Arial, sans-serif; }
        input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(239,169,40,.2); }
        .hint { margin: 10px 0 20px; color: #647080; font-size: 13px; line-height: 1.5; }
        button { width: 100%; min-height: 50px; border: 1px solid #efb232; border-radius: 10px; cursor: pointer; color: #fff9e9; background: linear-gradient(135deg, var(--blue), #061e43); box-shadow: 0 9px 22px rgba(3,28,64,.27); font-size: 16px; font-weight: 800; }
        button:disabled { cursor: wait; opacity: .65; }
        .preview { overflow: hidden; border: 2px solid rgba(239,169,40,.75); border-radius: 16px; background: var(--ivory); box-shadow: 0 22px 60px rgba(3,28,64,.25); }
        canvas { display: block; width: 100%; height: auto; }
        @media (max-width: 780px) {
            body { background: linear-gradient(160deg, #061e43 0 17rem, #f8efd9 17.1rem); }
            .page { width: min(100% - 20px, 680px); padding: 22px 0 36px; }
            .workspace { grid-template-columns: 1fr; gap: 18px; }
            .controls { position: static; padding: 19px; }
            .header p { font-size: 14px; }
        }
    </style>
</head>
<body>
<main class="page">
    <header class="header">
        <p class="eyebrow">Niên khóa 1992 – 1996</p>
        <h1>Thư mời kỷ niệm 30 năm</h1>
        <p>Lớp Kinh tế Du lịch B</p>
    </header>
    <section class="workspace">
        <div class="controls">
            <h2>Thông tin thư mời</h2>
            <label for="guestName">Tên khách mời</label>
            <input id="guestName" type="text" maxlength="80" autocomplete="name" required>
            <p class="hint">Tên được căn giữa trên dòng chấm và tự giảm cỡ chữ nếu quá dài.</p>
            <button id="downloadButton" type="button">Tải thư mời PNG</button>
        </div>
        <div class="preview" aria-label="Xem trước thư mời lớp Kinh tế Du lịch B">
            <canvas id="invitationCanvas"></canvas>
        </div>
    </section>
</main>
<script>
(() => {
    const IMAGE_URL = new URL(<?= json_encode($invitationImage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>, document.baseURI).href;
    const canvas = document.getElementById('invitationCanvas');
    const ctx = canvas.getContext('2d');
    const input = document.getElementById('guestName');
    const button = document.getElementById('downloadButton');
    const invitation = new Image();
    let ready = false;
    const normalizeText = value => value.replace(/\s+/g, ' ').trim();

    function draw() {
        if (!ready) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(invitation, 0, 0, canvas.width, canvas.height);
        const text = normalizeText(input.value);
        if (!text) return;

        const x = 1245;
        const y = 600;
        const maxWidth = 760;
        let fontSize = 61;
        const minFontSize = 34;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineJoin = 'round';
        ctx.font = `700 ${fontSize}px Arial, "Helvetica Neue", sans-serif`;
        while (ctx.measureText(text).width > maxWidth && fontSize > minFontSize) {
            fontSize -= 2;
            ctx.font = `700 ${fontSize}px Arial, "Helvetica Neue", sans-serif`;
        }
        ctx.lineWidth = Math.max(10, fontSize * .2);
        ctx.strokeStyle = 'rgba(255, 250, 241, .98)';
        ctx.strokeText(text, x, y, maxWidth);
        ctx.shadowColor = 'rgba(0, 35, 83, .18)';
        ctx.shadowBlur = 2;
        ctx.shadowOffsetY = 2;
        ctx.fillStyle = '#082c60';
        ctx.fillText(text, x, y, maxWidth);
        ctx.restore();
    }

    invitation.onload = () => {
        canvas.width = invitation.naturalWidth;
        canvas.height = invitation.naturalHeight;
        ready = true;
        draw();
        input.focus();
    };
    invitation.onerror = () => { button.disabled = true; button.textContent = 'Không tải được ảnh thư mời'; };
    invitation.src = IMAGE_URL;
    input.addEventListener('input', draw);

    const canvasToBlob = () => new Promise((resolve, reject) => {
        canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Không thể tạo file PNG')), 'image/png');
    });
    const safeSlug = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').toLowerCase();

    button.addEventListener('click', async () => {
        if (!ready) return;
        const guestName = normalizeText(input.value);
        if (!guestName) { input.focus(); input.reportValidity(); return; }
        draw();
        const fileName = `thu-moi-ktdl-b-${safeSlug(guestName) || 'khach-moi'}.png`;
        button.disabled = true;
        button.textContent = 'Đang tạo ảnh...';
        try {
            const blob = await canvasToBlob();
            const file = typeof File === 'function' ? new File([blob], fileName, { type: 'image/png' }) : null;
            if (file && navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({ files: [file], title: 'Thư mời kỷ niệm 30 năm lớp Kinh tế Du lịch B' });
            } else {
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = fileName;
                link.href = objectUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(() => URL.revokeObjectURL(objectUrl), 30000);
            }
        } catch (error) {
            if (!error || error.name !== 'AbortError') alert('Chưa thể lưu ảnh. Vui lòng thử lại bằng Safari hoặc Chrome phiên bản mới nhất.');
        } finally {
            button.disabled = false;
            button.textContent = 'Tải thư mời PNG';
        }
    });
})();
</script>
</body>
</html>
