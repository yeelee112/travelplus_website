<?php
$locale = service('request')->getLocale() ?: 'vi';
$privacyUrl = \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale);
$termsUrl = \App\Data\LocalizedPathCatalog::url('legal.terms', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Privacy preferences',
        'title' => 'Travel Plus uses cookies',
        'body' => 'We use necessary cookies to run the website and optional cookies to understand traffic, improve service quality and support marketing campaigns.',
        'accept' => 'Accept all',
        'reject' => 'Reject optional',
        'customize' => 'Customize',
        'save' => 'Save choices',
        'necessary' => 'Necessary',
        'necessaryDesc' => 'Required for session, sign-in, booking, security and checkout.',
        'analytics' => 'Analytics',
        'analyticsDesc' => 'Helps Travel Plus understand page views, searches and user journeys.',
        'marketing' => 'Marketing',
        'marketingDesc' => 'Used for advertising, remarketing and campaign measurement when enabled.',
        'preferences' => 'Preferences',
        'preferencesDesc' => 'Keeps useful interface preferences for a smoother experience.',
        'alwaysOn' => 'Always on',
        'privacy' => 'Privacy Statement',
        'terms' => 'Terms of Service',
    ]
    : [
        'eyebrow' => 'Tùy chọn riêng tư',
        'title' => 'Travel Plus sử dụng cookie',
        'body' => 'Chúng tôi dùng cookie cần thiết để website hoạt động và cookie tùy chọn để phân tích lượt truy cập, cải thiện dịch vụ và hỗ trợ chiến dịch marketing.',
        'accept' => 'Chấp nhận tất cả',
        'reject' => 'Từ chối tùy chọn',
        'customize' => 'Tùy chỉnh',
        'save' => 'Lưu lựa chọn',
        'necessary' => 'Cần thiết',
        'necessaryDesc' => 'Bắt buộc cho phiên đăng nhập, booking, bảo mật và thanh toán.',
        'analytics' => 'Phân tích',
        'analyticsDesc' => 'Giúp Travel Plus hiểu lượt xem trang, nội dung tìm kiếm và hành trình khách hàng.',
        'marketing' => 'Marketing',
        'marketingDesc' => 'Dùng cho quảng cáo, remarketing và đo lường chiến dịch khi được bật.',
        'preferences' => 'Tiện ích',
        'preferencesDesc' => 'Ghi nhớ một số tùy chọn giao diện để trải nghiệm mượt hơn.',
        'alwaysOn' => 'Luôn bật',
        'privacy' => 'Chính sách bảo mật',
        'terms' => 'Điều khoản sử dụng',
    ];
?>
<div class="tp-cookie-consent" data-cookie-consent hidden>
    <div class="tp-cookie-consent__panel" role="dialog" aria-live="polite" aria-label="<?= esc($copy['title'], 'attr') ?>">
        <div class="tp-cookie-consent__main">
            <span class="tp-cookie-consent__eyebrow"><?= esc($copy['eyebrow']) ?></span>
            <h2><?= esc($copy['title']) ?></h2>
            <p><?= esc($copy['body']) ?></p>
            <div class="tp-cookie-consent__links">
                <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($copy['privacy']) ?></a>
                <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($copy['terms']) ?></a>
            </div>
        </div>

        <div class="tp-cookie-consent__choices" data-cookie-choices hidden>
            <div class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['necessary']) ?></strong>
                    <span><?= esc($copy['necessaryDesc']) ?></span>
                </div>
                <em><?= esc($copy['alwaysOn']) ?></em>
            </div>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['analytics']) ?></strong>
                    <span><?= esc($copy['analyticsDesc']) ?></span>
                </div>
                <input type="checkbox" value="analytics" data-cookie-category>
            </label>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['marketing']) ?></strong>
                    <span><?= esc($copy['marketingDesc']) ?></span>
                </div>
                <input type="checkbox" value="marketing" data-cookie-category>
            </label>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['preferences']) ?></strong>
                    <span><?= esc($copy['preferencesDesc']) ?></span>
                </div>
                <input type="checkbox" value="preferences" data-cookie-category>
            </label>
        </div>

        <div class="tp-cookie-consent__actions">
            <button type="button" class="tp-cookie-btn tp-cookie-btn--primary" data-cookie-accept><?= esc($copy['accept']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--ghost" data-cookie-reject><?= esc($copy['reject']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--link" data-cookie-customize><?= esc($copy['customize']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--primary" data-cookie-save hidden><?= esc($copy['save']) ?></button>
        </div>
    </div>
</div>

<style>
    .tp-cookie-consent{position:fixed;left:0;right:0;bottom:0;z-index:1040;padding:18px;pointer-events:none}
    .tp-cookie-consent[hidden]{display:none}
    .tp-cookie-consent__panel{width:min(1120px,100%);margin:0 auto;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:center;padding:20px;border:1px solid rgba(15,39,64,.12);border-radius:22px;background:rgba(255,255,255,.97);box-shadow:0 28px 70px rgba(15,39,64,.18);backdrop-filter:blur(16px);pointer-events:auto}
    .tp-cookie-consent__eyebrow{display:inline-flex;margin-bottom:7px;color:#009cde;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .tp-cookie-consent h2{margin:0 0 6px;color:#0f172a;font-size:22px;font-weight:900;line-height:1.2}
    .tp-cookie-consent p{max-width:760px;margin:0;color:#4b5f73;font-size:14px;line-height:1.65}
    .tp-cookie-consent__links{display:flex;flex-wrap:wrap;gap:12px;margin-top:10px}
    .tp-cookie-consent__links a{color:#0b3d91;font-size:13px;font-weight:800;text-decoration:none}
    .tp-cookie-consent__links a:hover{text-decoration:underline}
    .tp-cookie-consent__actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
    .tp-cookie-btn{min-height:42px;padding:10px 16px;border:1px solid rgba(15,39,64,.14);border-radius:999px;background:#fff;color:#0f2740;font-weight:850;font-size:13px;line-height:1.2;white-space:nowrap;transition:transform .18s ease,box-shadow .18s ease,background .18s ease,border-color .18s ease}
    .tp-cookie-btn:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(15,39,64,.1)}
    .tp-cookie-btn--primary{border-color:#0f2740;background:#0f2740;color:#fff}
    .tp-cookie-btn--ghost{background:#f6fbff}
    .tp-cookie-btn--link{border-color:transparent;background:transparent;color:#0b3d91}
    .tp-cookie-consent__choices{grid-column:1 / -1;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding-top:14px;border-top:1px solid rgba(15,39,64,.08)}
    .tp-cookie-consent__choices[hidden]{display:none}
    .tp-cookie-choice{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;min-height:112px;padding:14px;border:1px solid rgba(15,39,64,.1);border-radius:16px;background:#f8fbff;color:#0f2740}
    .tp-cookie-choice div{display:grid;gap:5px}
    .tp-cookie-choice strong{font-size:14px;font-weight:900;line-height:1.2}
    .tp-cookie-choice span{color:#5d6c7b;font-size:12px;line-height:1.45}
    .tp-cookie-choice em{display:inline-flex;align-items:center;min-height:24px;padding:4px 9px;border-radius:999px;background:#e9f7ff;color:#0b77aa;font-size:11px;font-style:normal;font-weight:900;white-space:nowrap}
    .tp-cookie-choice input{width:20px;height:20px;flex:0 0 20px;accent-color:#0b3d91}
    .travelplus-footer__cookie-button{display:inline-flex;border:0;background:transparent;padding:0;color:rgba(var(--title-color-opc),.72);font:inherit;font-size:15px;line-height:1.45;text-align:left;cursor:pointer;transition:color .2s ease}
    .travelplus-footer__cookie-button:hover{color:var(--primary-color1)}
    body.tp-cookie-panel-open .tp-ai-chatbox{opacity:0;visibility:hidden;pointer-events:none;transform:translateY(12px)}
    @media (max-width:991px){.tp-cookie-consent__panel{grid-template-columns:1fr}.tp-cookie-consent__actions{justify-content:flex-start}.tp-cookie-consent__choices{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:767px){.tp-cookie-consent{padding:10px}.tp-cookie-consent__panel{gap:14px;padding:16px;border-radius:18px}.tp-cookie-consent h2{font-size:19px}.tp-cookie-consent p{font-size:13px;line-height:1.55}.tp-cookie-consent__actions{display:grid;grid-template-columns:1fr 1fr;width:100%;gap:8px}.tp-cookie-btn{width:100%;min-height:40px;padding:9px 12px;font-size:12px}.tp-cookie-btn--link,.tp-cookie-consent__actions [data-cookie-save]{grid-column:1 / -1}.tp-cookie-consent__choices{grid-template-columns:1fr;max-height:44vh;overflow:auto;padding-right:2px}.tp-cookie-choice{min-height:0;padding:12px}}
</style>

<script>
(function () {
    const cookieName = 'tp_cookie_consent';
    const root = document.querySelector('[data-cookie-consent]');

    if (!root) {
        return;
    }

    const choices = root.querySelector('[data-cookie-choices]');
    const acceptButton = root.querySelector('[data-cookie-accept]');
    const rejectButton = root.querySelector('[data-cookie-reject]');
    const customizeButton = root.querySelector('[data-cookie-customize]');
    const saveButton = root.querySelector('[data-cookie-save]');
    const categoryInputs = Array.from(root.querySelectorAll('[data-cookie-category]'));

    const getCookie = function (name) {
        return document.cookie
            .split('; ')
            .find(function (row) { return row.startsWith(name + '='); })
            ?.split('=')[1] || '';
    };

    const parseConsent = function () {
        const value = decodeURIComponent(getCookie(cookieName));
        return value ? value.split(',').filter(Boolean) : [];
    };

    const writeConsent = function (categories) {
        const value = encodeURIComponent(categories.join(','));
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = cookieName + '=' + value + '; Path=/; Max-Age=15552000; SameSite=Lax' + secure;
        window.localStorage.setItem(cookieName, categories.join(','));
    };

    const closePanel = function () {
        root.hidden = true;
        document.body.classList.remove('tp-cookie-panel-open');
    };

    const enableDeferredScripts = function (categories) {
        const allowed = new Set(categories);

        document.querySelectorAll('script[type="text/plain"][data-cookie-category]').forEach(function (script) {
            const category = script.dataset.cookieCategory || '';

            if (!allowed.has('all') && !allowed.has(category)) {
                return;
            }

            const executable = document.createElement('script');
            Array.from(script.attributes).forEach(function (attr) {
                if (attr.name !== 'type' && attr.name !== 'data-cookie-category') {
                    executable.setAttribute(attr.name, attr.value);
                }
            });
            executable.text = script.textContent || '';
            script.replaceWith(executable);
        });
    };

    const applyConsent = function (categories) {
        writeConsent(categories);
        enableDeferredScripts(categories);
        window.dispatchEvent(new CustomEvent('travelplus:cookie-consent', {
            detail: { categories: categories }
        }));
        closePanel();
    };

    const existingConsent = parseConsent();
    if (existingConsent.length > 0) {
        enableDeferredScripts(existingConsent);
    } else {
        root.hidden = false;
        document.body.classList.add('tp-cookie-panel-open');
    }

    document.querySelectorAll('[data-cookie-settings]').forEach(function (button) {
        button.addEventListener('click', function () {
            const currentConsent = parseConsent();
            categoryInputs.forEach(function (input) {
                input.checked = currentConsent.includes('all') || currentConsent.includes(input.value);
            });

            if (choices && saveButton && customizeButton) {
                choices.hidden = false;
                saveButton.hidden = false;
                customizeButton.hidden = true;
            }

            root.hidden = false;
            document.body.classList.add('tp-cookie-panel-open');
        });
    });

    acceptButton?.addEventListener('click', function () {
        applyConsent(['necessary', 'analytics', 'marketing', 'preferences']);
    });

    rejectButton?.addEventListener('click', function () {
        applyConsent(['necessary']);
    });

    customizeButton?.addEventListener('click', function () {
        if (!choices || !saveButton || !customizeButton) {
            return;
        }

        choices.hidden = false;
        saveButton.hidden = false;
        customizeButton.hidden = true;
    });

    saveButton?.addEventListener('click', function () {
        const selected = ['necessary'];
        categoryInputs.forEach(function (input) {
            if (input.checked) {
                selected.push(input.value);
            }
        });
        applyConsent(selected);
    });
})();
</script>
