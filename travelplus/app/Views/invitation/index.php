<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#064a32">
    <title>Tạo thiệp mời kỷ niệm 35 năm lớp 12E</title>
    <style>
        :root {
            color-scheme: light;
            --green-950: #032f22;
            --green-800: #075238;
            --green-700: #0b6948;
            --gold: #d6a63b;
            --gold-light: #f4d681;
            --ivory: #fffbef;
            --ink: #17392d;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 14% 0, rgba(244, 214, 129, .3), transparent 30rem),
                linear-gradient(145deg, #032f22 0, #075238 30%, #f7efd9 30.2%, #fffaf0 100%);
            background-attachment: fixed;
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 52px;
        }

        .header {
            margin-bottom: 24px;
            text-align: center;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--gold-light);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .header h1 {
            margin: 0 0 8px;
            color: #fff9e8;
            font: 700 clamp(26px, 4vw, 42px)/1.18 "Times New Roman", Times, serif;
            text-shadow: 0 2px 14px rgba(0, 0, 0, .22);
        }

        .header p { margin: 0; color: #f4e7c4; }

        .workspace {
            display: grid;
            grid-template-columns: minmax(280px, 350px) minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .controls {
            position: sticky;
            top: 24px;
            padding: 25px;
            border: 1px solid rgba(214, 166, 59, .48);
            border-radius: 18px;
            background: rgba(255, 251, 239, .97);
            box-shadow: 0 18px 50px rgba(0, 40, 27, .2);
        }

        .controls-title {
            margin: 0 0 20px;
            color: var(--green-800);
            font: 700 22px/1.3 Arial, sans-serif;
        }

        .field { margin-bottom: 18px; }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--green-800);
            font-size: 14px;
            font-weight: 800;
        }

        input {
            width: 100%;
            min-height: 49px;
            padding: 11px 14px;
            border: 1px solid #c9b57e;
            border-radius: 10px;
            outline: none;
            color: var(--green-950);
            background: #fff;
            font: 700 17px/1.3 Arial, "Helvetica Neue", sans-serif;
        }

        input::placeholder {
            color: #777;
            font-family: Arial, "Helvetica Neue", sans-serif;
            font-size: 16px;
            font-weight: 400;
            opacity: 1;
        }

        input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(214, 166, 59, .2);
        }

        .hint {
            margin: 2px 0 20px;
            color: #64766e;
            font-size: 13px;
            line-height: 1.5;
        }

        button {
            width: 100%;
            min-height: 50px;
            border: 1px solid #e4bf58;
            border-radius: 10px;
            cursor: pointer;
            color: #fff9e8;
            background: linear-gradient(135deg, var(--green-700), var(--green-950));
            box-shadow: 0 9px 22px rgba(3, 47, 34, .25), inset 0 1px rgba(255, 255, 255, .16);
            font-size: 16px;
            font-weight: 800;
        }

        button:disabled { cursor: wait; opacity: .65; }

        .preview {
            overflow: hidden;
            border: 2px solid rgba(214, 166, 59, .7);
            border-radius: 16px;
            background: var(--ivory);
            box-shadow: 0 22px 60px rgba(0, 40, 27, .24);
        }

        canvas { display: block; width: 100%; height: auto; }

        @media (max-width: 780px) {
            body { background: linear-gradient(160deg, var(--green-950) 0 17rem, #f8f0dc 17.1rem); }
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
        <p class="eyebrow">Niên khóa 1989 – 1991</p>
        <h1>Thiệp mời kỷ niệm 35 năm lớp 12E</h1>
        <p>Trường THPT Hai Bà Trưng – Huế</p>
    </header>

    <section class="workspace">
        <div class="controls">
            <h2 class="controls-title">Thông tin thiệp mời</h2>

            <div class="field">
                <label for="teacherName">Tên Thầy</label>
                <input id="teacherName" type="text" maxlength="80" autocomplete="name" required>
            </div>

            <div class="field">
                <label for="restaurantName">Tên nhà hàng</label>
                <input id="restaurantName" type="text" maxlength="60" required>
            </div>

            <p class="hint">Nội dung được căn giữa và tự giảm cỡ chữ khi dài.</p>
            <button id="downloadButton" type="button">Tải thiệp PNG</button>
        </div>

        <div class="preview" aria-label="Xem trước thiệp mời lớp 12E">
            <canvas id="invitationCanvas"></canvas>
        </div>
    </section>
</main>

<script>
(() => {
    const IMAGE_URL = new URL(
        <?= json_encode($invitationImage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
        document.baseURI
    ).href;
    const canvas = document.getElementById('invitationCanvas');
    const ctx = canvas.getContext('2d');
    const teacherInput = document.getElementById('teacherName');
    const restaurantInput = document.getElementById('restaurantName');
    const downloadButton = document.getElementById('downloadButton');
    const invitation = new Image();
    let imageReady = false;

    const normalizeText = value => value.replace(/\s+/g, ' ').trim();

    function drawCenteredText(text, options) {
        if (!text) return;

        let fontSize = options.fontSize;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineJoin = 'round';
        const fontFamily = options.fontFamily || '"Times New Roman", Times, serif';
        ctx.font = `${options.weight || 700} ${fontSize}px ${fontFamily}`;

        while (ctx.measureText(text).width > options.maxWidth && fontSize > options.minFontSize) {
            fontSize -= 2;
            ctx.font = `${options.weight || 700} ${fontSize}px ${fontFamily}`;
        }

        ctx.lineWidth = Math.max(9, fontSize * .2);
        ctx.strokeStyle = 'rgba(255, 251, 240, .98)';
        ctx.strokeText(text, options.x, options.y, options.maxWidth);
        ctx.shadowColor = 'rgba(1, 45, 29, .2)';
        ctx.shadowBlur = 2;
        ctx.shadowOffsetY = 2;
        ctx.fillStyle = '#064a32';
        ctx.fillText(text, options.x, options.y, options.maxWidth);
        ctx.restore();
    }

    function drawInvitation() {
        if (!imageReady) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(invitation, 0, 0, canvas.width, canvas.height);

        drawCenteredText(normalizeText(teacherInput.value), {
            x: 1054,
            y: 684,
            maxWidth: 900,
            fontSize: 72,
            minFontSize: 38,
        });

        drawCenteredText(normalizeText(restaurantInput.value), {
            x: 1622,
            y: 2190,
            maxWidth: 445,
            fontSize: 46,
            minFontSize: 28,
            fontFamily: 'Arial, "Helvetica Neue", sans-serif',
        });
    }

    invitation.onload = () => {
        canvas.width = invitation.naturalWidth;
        canvas.height = invitation.naturalHeight;
        imageReady = true;
        drawInvitation();
        teacherInput.focus();
    };

    invitation.onerror = () => {
        downloadButton.disabled = true;
        downloadButton.textContent = 'Không tải được ảnh thiệp';
    };

    invitation.src = IMAGE_URL;
    teacherInput.addEventListener('input', drawInvitation);
    restaurantInput.addEventListener('input', drawInvitation);

    function canvasToBlob() {
        return new Promise((resolve, reject) => {
            canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Không thể tạo file PNG')), 'image/png');
        });
    }

    function safeSlug(value) {
        return value.normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .replace(/[^a-zA-Z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
            .toLowerCase();
    }

    downloadButton.addEventListener('click', async () => {
        if (!imageReady) return;

        const teacherName = normalizeText(teacherInput.value);
        const restaurantName = normalizeText(restaurantInput.value);
        if (!teacherName) {
            teacherInput.focus();
            teacherInput.reportValidity();
            return;
        }
        if (!restaurantName) {
            restaurantInput.focus();
            restaurantInput.reportValidity();
            return;
        }

        drawInvitation();
        const fileName = `thiep-moi-12e-${safeSlug(teacherName) || 'quy-thay'}.png`;
        downloadButton.disabled = true;
        downloadButton.textContent = 'Đang tạo ảnh...';

        try {
            const blob = await canvasToBlob();
            const canCreateFile = typeof File === 'function';
            const file = canCreateFile ? new File([blob], fileName, { type: 'image/png' }) : null;

            if (file && navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({ files: [file], title: 'Thiệp mời kỷ niệm 35 năm lớp 12E' });
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
            if (!error || error.name !== 'AbortError') {
                alert('Chưa thể lưu ảnh. Vui lòng thử lại bằng Safari hoặc Chrome phiên bản mới nhất.');
            }
        } finally {
            downloadButton.disabled = false;
            downloadButton.textContent = 'Tải thiệp PNG';
        }
    });
})();
</script>
</body>
</html>
