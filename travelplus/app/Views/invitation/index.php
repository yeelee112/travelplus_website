<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Tạo thiệp mời An Cựu Residence</title>
    <style>
        :root {
            color-scheme: light;
            --wine: #7f1017;
            --wine-bright: #a80e1c;
            --gold: #c8922e;
            --cream: #fffaf0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #3f1818;
            background:
                radial-gradient(circle at top, rgba(216, 174, 81, .18), transparent 35rem),
                #f5eee4;
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { margin: 0 0 8px; color: var(--wine); font: 700 clamp(26px, 4vw, 40px)/1.2 Georgia, serif; }
        .header p { margin: 0; color: #765b55; }

        .workspace {
            display: grid;
            grid-template-columns: minmax(260px, 340px) minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .controls {
            position: sticky;
            top: 24px;
            padding: 24px;
            border: 1px solid rgba(127, 16, 23, .14);
            border-radius: 18px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 16px 42px rgba(76, 31, 20, .1);
        }

        label { display: block; margin-bottom: 9px; font-weight: 700; color: var(--wine); }

        input {
            width: 100%;
            min-height: 48px;
            padding: 11px 14px;
            border: 1px solid #d7b7ad;
            border-radius: 10px;
            outline: none;
            font: 600 17px/1.3 Georgia, serif;
            color: var(--wine);
            background: #fff;
        }

        input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(200, 146, 46, .18); }
        .hint { margin: 9px 0 20px; font-size: 13px; line-height: 1.5; color: #7a6863; }

        button {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--wine-bright), var(--wine));
            box-shadow: 0 8px 20px rgba(127, 16, 23, .2);
        }

        button:disabled { cursor: wait; opacity: .6; }

        .preview {
            overflow: hidden;
            border: 1px solid rgba(127, 16, 23, .14);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(76, 31, 20, .12);
        }

        canvas { display: block; width: 100%; height: auto; }

        @media (max-width: 780px) {
            .page { width: min(100% - 20px, 680px); padding-top: 20px; }
            .workspace { grid-template-columns: 1fr; gap: 18px; }
            .controls { position: static; padding: 18px; }
        }
    </style>
</head>
<body>
<main class="page">
    <header class="header">
        <h1>Thiệp mời An Cựu Residence</h1>
        <p>Nhập tên khách mời, xem trước và tải thiệp hoàn chỉnh.</p>
    </header>

    <section class="workspace">
        <div class="controls">
            <label for="guestName">Tên khách mời</label>
            <input id="guestName" type="text" maxlength="80" autocomplete="name" placeholder="Ví dụ: Anh Nguyễn Văn An" required>
            <p class="hint">Tên được tự động căn giữa. Với tên dài, cỡ chữ sẽ tự giảm để luôn nằm gọn trên dòng.</p>
            <button id="downloadButton" type="button">Tải thiệp PNG</button>
        </div>

        <div class="preview" aria-label="Xem trước thiệp mời">
            <canvas id="invitationCanvas"></canvas>
        </div>
    </section>
</main>

<script>
(() => {
    const IMAGE_URL = <?= json_encode($invitationImage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const canvas = document.getElementById('invitationCanvas');
    const ctx = canvas.getContext('2d');
    const input = document.getElementById('guestName');
    const downloadButton = document.getElementById('downloadButton');
    const invitation = new Image();
    let imageReady = false;

    const normalizeName = value => value.replace(/\s+/g, ' ').trim();

    function drawInvitation() {
        if (!imageReady) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(invitation, 0, 0, canvas.width, canvas.height);

        const guestName = normalizeName(input.value);
        if (!guestName) return;

        // Vùng dòng chấm trên ảnh gốc 2118 x 3000.
        const centerX = 1059;
        const baselineY = 525;
        const maxWidth = 1050;
        let fontSize = 68;
        const minFontSize = 40;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = `700 ${fontSize}px Georgia, "Times New Roman", serif`;

        while (ctx.measureText(guestName).width > maxWidth && fontSize > minFontSize) {
            fontSize -= 2;
            ctx.font = `700 ${fontSize}px Georgia, "Times New Roman", serif`;
        }

        // Viền kem che các chấm nằm ngay dưới ký tự, giúp tên nổi và vẫn giữ hai đầu dòng chấm.
        ctx.lineJoin = 'round';
        ctx.miterLimit = 2;
        ctx.lineWidth = Math.max(12, fontSize * .22);
        ctx.strokeStyle = 'rgba(255, 250, 239, .98)';
        ctx.strokeText(guestName, centerX, baselineY, maxWidth);

        ctx.shadowColor = 'rgba(103, 41, 14, .24)';
        ctx.shadowBlur = 3;
        ctx.shadowOffsetY = 2;
        ctx.fillStyle = '#a80e1c';
        ctx.fillText(guestName, centerX, baselineY, maxWidth);
        ctx.restore();
    }

    invitation.onload = () => {
        canvas.width = invitation.naturalWidth;
        canvas.height = invitation.naturalHeight;
        imageReady = true;
        drawInvitation();
        input.focus();
    };

    invitation.onerror = () => {
        downloadButton.disabled = true;
        downloadButton.textContent = 'Không tải được ảnh thiệp';
    };

    invitation.src = IMAGE_URL;
    input.addEventListener('input', drawInvitation);

    downloadButton.addEventListener('click', () => {
        if (!imageReady) return;
        const guestName = normalizeName(input.value);
        if (!guestName) {
            input.focus();
            input.reportValidity();
            return;
        }

        drawInvitation();
        const safeName = guestName
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .replace(/[^a-zA-Z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
            .toLowerCase();
        const link = document.createElement('a');
        link.download = `thiep-moi-an-cuu-${safeName || 'khach-moi'}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
})();
</script>
</body>
</html>
