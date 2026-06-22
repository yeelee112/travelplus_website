<?php
$currentLocale = $currentLocale ?? service('request')->getLocale() ?? 'vi';
$chatUi = $currentLocale === 'en'
    ? [
        'button' => 'Travel Plus AI',
        'title' => 'Travel Plus AI',
        'subtitle' => 'Website-based answers only',
        'placeholder' => 'Ask about tours, visa, hotels, booking policy...',
        'send' => 'Send',
        'thinking' => 'Checking website data...',
        'error' => 'AI Travel Plus is temporarily unavailable.',
        'welcome' => 'Ask about tours, visa support, hotels, transport, or Travel Plus policies.',
        'online' => 'Online',
        'assistantName' => 'Travel Plus AI',
        'suggestions' => [
            'Do you support visa services?',
            'What payment methods are available?',
            'Do you have hotel booking support?',
            'How can I request a custom tour?',
        ],
      ]
    : [
        'button' => 'AI Travel Plus',
        'title' => 'AI Travel Plus',
        'subtitle' => 'Chỉ trả lời theo dữ liệu website',
        'placeholder' => 'Hỏi về tour, visa, khách sạn, chính sách đặt chỗ...',
        'send' => 'Gửi',
        'thinking' => 'Đang kiểm tra dữ liệu website...',
        'error' => 'AI Travel Plus đang tạm thời không khả dụng.',
        'welcome' => 'Bạn có thể hỏi về tour, visa, khách sạn, vận chuyển hoặc chính sách của Travel Plus.',
        'online' => 'Đang hoạt động',
        'assistantName' => 'AI Travel Plus',
        'suggestions' => [
            'Travel Plus có hỗ trợ visa không?',
            'Website đang có những phương thức thanh toán nào?',
            'Có dịch vụ đặt khách sạn không?',
            'Tôi muốn tạo tour theo yêu cầu thì làm thế nào?',
        ],
      ];
?>
<div class="tp-ai-chatbox" id="tp-ai-chatbox" data-endpoint="<?= esc(base_url('api/ai-chat')) ?>" data-locale="<?= esc($currentLocale) ?>">
    <button type="button" class="tp-ai-chatbox__toggle" aria-expanded="false" aria-controls="tp-ai-chatbox-panel">
        <span class="tp-ai-chatbox__toggle-icon"><i class="bi bi-chat-dots-fill"></i></span>
        <span class="tp-ai-chatbox__toggle-copy">
            <strong><?= esc($chatUi['button']) ?></strong>
        </span>
    </button>

    <section class="tp-ai-chatbox__panel" id="tp-ai-chatbox-panel" hidden>
        <header class="tp-ai-chatbox__header">
            <div class="tp-ai-chatbox__identity">
                <div class="tp-ai-chatbox__avatar">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="tp-ai-chatbox__identity-copy">
                    <h3><?= esc($chatUi['title']) ?></h3>
                    <p><span class="tp-ai-chatbox__online-dot"></span><?= esc($chatUi['online']) ?></p>
                </div>
            </div>
            <button type="button" class="tp-ai-chatbox__close" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="tp-ai-chatbox__messages" data-role="messages">
            <div class="tp-ai-chatbox__message tp-ai-chatbox__message--assistant">
                <div class="tp-ai-chatbox__message-avatar">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="tp-ai-chatbox__message-main">
                    <div class="tp-ai-chatbox__message-author"><?= esc($chatUi['assistantName']) ?></div>
                    <div class="tp-ai-chatbox__bubble"><?= esc($chatUi['welcome']) ?></div>
                    <div class="tp-ai-chatbox__suggestions" data-role="suggestions">
                        <?php foreach (($chatUi['suggestions'] ?? []) as $suggestion): ?>
                        <button type="button" class="tp-ai-chatbox__chip" data-suggestion="<?= esc($suggestion) ?>"><?= esc($suggestion) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <form class="tp-ai-chatbox__form" data-role="form">
            <div class="tp-ai-chatbox__composer">
                <textarea name="message" rows="1" placeholder="<?= esc($chatUi['placeholder']) ?>"></textarea>
                <button type="submit" class="tp-ai-chatbox__send" aria-label="<?= esc($chatUi['send']) ?>">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            <div class="tp-ai-chatbox__statusline">
                <small data-role="status"></small>
            </div>
        </form>
    </section>
</div>

<style>
    .tp-ai-chatbox{position:fixed;left:20px;bottom:20px;z-index:1030;font-family:inherit;transition:opacity .18s ease,visibility .18s ease,transform .18s ease}
    body.modal-open .tp-ai-chatbox{opacity:0;visibility:hidden;pointer-events:none;transform:translateY(12px)}
    .tp-ai-chatbox__toggle{display:flex;align-items:center;gap:12px;min-width:0;border:1px solid #d8e6f4;padding:14px 18px;border-radius:999px;background:linear-gradient(180deg,#ffffff 0%,#eef7ff 100%);color:#12324a;box-shadow:0 18px 40px rgba(15,23,42,.14)}
    .tp-ai-chatbox__toggle-icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#18a0dc,#246bff);font-size:18px;flex:0 0 40px}
    .tp-ai-chatbox__toggle-copy{display:flex;align-items:center;text-align:left;line-height:1.15}
    .tp-ai-chatbox__toggle-copy strong{font-size:15px;font-weight:700;color:#12324a}

    .tp-ai-chatbox__panel{position:absolute;left:0;bottom:78px;width:min(400px,calc(100vw - 32px));height:min(620px,calc(100vh - 120px));display:flex;flex-direction:column;background:#fff;border:1px solid #dbe6f1;border-radius:26px;overflow:hidden;box-shadow:0 26px 70px rgba(15,23,42,.2)}
    .tp-ai-chatbox__header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 18px 16px;background:linear-gradient(180deg,#f5fbff 0%,#fff 100%);border-bottom:1px solid #e7eff7}
    .tp-ai-chatbox__identity{display:flex;align-items:center;gap:12px;min-width:0}
    .tp-ai-chatbox__avatar{display:flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:16px;background:linear-gradient(135deg,#18a0dc,#246bff);color:#fff;font-size:18px;flex:0 0 46px}
    .tp-ai-chatbox__identity-copy h3{margin:0;font-size:18px;font-weight:700;color:#0f172a}
    .tp-ai-chatbox__identity-copy p{display:flex;align-items:center;gap:8px;margin:4px 0 0;color:#5c7389;font-size:13px}
    .tp-ai-chatbox__online-dot{display:inline-block;width:8px;height:8px;border-radius:999px;background:#17c964;box-shadow:0 0 0 4px rgba(23,201,100,.14)}
    .tp-ai-chatbox__close{border:0;background:#f1f5f9;color:#556b80;width:36px;height:36px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:0 0 36px}

    .tp-ai-chatbox__messages{flex:1;overflow:auto;padding:16px 16px 10px;background:linear-gradient(180deg,#f8fbfe 0%,#f4f8fc 100%)}
    .tp-ai-chatbox__message{display:flex;align-items:flex-end;gap:10px;margin-bottom:14px}
    .tp-ai-chatbox__message--user{justify-content:flex-end}
    .tp-ai-chatbox__message--user .tp-ai-chatbox__message-main{align-items:flex-end}
    .tp-ai-chatbox__message-avatar{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:12px;background:#dff1ff;color:#1595d3;flex:0 0 32px}
    .tp-ai-chatbox__message-main{display:flex;flex-direction:column;gap:4px;max-width:82%}
    .tp-ai-chatbox__message-author{font-size:11px;font-weight:600;color:#6b7f92;padding:0 6px}
    .tp-ai-chatbox__bubble{padding:12px 14px;border-radius:18px;background:#fff;border:1px solid #dce7f2;color:#17324a;line-height:1.52;white-space:pre-wrap;word-break:break-word}
    .tp-ai-chatbox__message--user .tp-ai-chatbox__bubble{background:linear-gradient(135deg,#1595d3,#246bff);border-color:transparent;color:#fff;border-bottom-right-radius:6px}
    .tp-ai-chatbox__message--assistant .tp-ai-chatbox__bubble{border-top-left-radius:6px}
    .tp-ai-chatbox__sources{max-width:100%;margin-top:5px;font-size:12px}
    .tp-ai-chatbox__sources summary{display:inline-flex;align-items:center;gap:6px;max-width:100%;min-height:28px;padding:5px 9px;border:1px solid #d8e8f4;border-radius:999px;background:#fff;color:#4e6b82;font-weight:700;line-height:1;cursor:pointer;list-style:none}
    .tp-ai-chatbox__sources summary::-webkit-details-marker{display:none}
    .tp-ai-chatbox__sources summary::after{content:"";width:7px;height:7px;border-right:1.5px solid currentColor;border-bottom:1.5px solid currentColor;transform:translateY(-2px) rotate(45deg);transition:transform .16s ease}
    .tp-ai-chatbox__sources[open] summary::after{transform:translateY(1px) rotate(225deg)}
    .tp-ai-chatbox__source-list{display:grid;gap:6px;max-height:112px;overflow:auto;margin-top:7px;padding:8px;border:1px solid #dce7f2;border-radius:12px;background:#fff}
    .tp-ai-chatbox__source-list a{display:flex;align-items:center;gap:6px;min-width:0;color:#1595d3;text-decoration:none;font-weight:700;line-height:1.35}
    .tp-ai-chatbox__source-list a span{display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .tp-ai-chatbox__source-list small{color:#6b7f92;line-height:1.35}
    .tp-ai-chatbox__suggestions{display:flex;flex-wrap:wrap;gap:8px;padding:2px 0 2px 2px}
    .tp-ai-chatbox__chip{border:1px solid #d8e5f1;background:#fff;color:#28506f;border-radius:999px;padding:8px 12px;font-size:12px;line-height:1.2;text-align:left;transition:all .18s ease}
    .tp-ai-chatbox__chip:hover{border-color:#99c9e8;background:#f4fbff;color:#1595d3}
    .tp-ai-chatbox__typing{display:inline-flex;align-items:center;gap:5px;min-height:20px}
    .tp-ai-chatbox__typing span{width:7px;height:7px;border-radius:999px;background:#8eb6d6;display:block;animation:tpAiTyping 1.2s infinite ease-in-out}
    .tp-ai-chatbox__typing span:nth-child(2){animation-delay:.15s}
    .tp-ai-chatbox__typing span:nth-child(3){animation-delay:.3s}
    @keyframes tpAiTyping{
        0%,80%,100%{transform:translateY(0);opacity:.45}
        40%{transform:translateY(-3px);opacity:1}
    }

    .tp-ai-chatbox__form{padding:14px 16px 16px;border-top:1px solid #e6eef6;background:#fff}
    .tp-ai-chatbox__composer{display:flex;align-items:flex-end;gap:10px;padding:10px;border:1px solid #d6e1ec;border-radius:18px;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.04)}
    .tp-ai-chatbox__composer textarea{width:100%;min-height:24px;max-height:120px;resize:none;border:0;outline:none;background:transparent;padding:4px 2px;color:#10273e;line-height:1.45}
    .tp-ai-chatbox__send{border:0;width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1595d3,#246bff);color:#fff;flex:0 0 42px}
    .tp-ai-chatbox__send:disabled{opacity:.55}
    .tp-ai-chatbox__statusline{min-height:18px;padding:8px 4px 0}
    .tp-ai-chatbox__statusline small{color:#6a8095}

    @media (max-width: 767px){
        .tp-ai-chatbox{left:12px;right:auto;bottom:calc(16px + env(safe-area-inset-bottom))}
        .tp-ai-chatbox__toggle{width:48px;height:48px;min-width:48px;gap:0;padding:0;justify-content:center;border-radius:999px;box-shadow:0 14px 30px rgba(15,23,42,.16)}
        .tp-ai-chatbox__toggle-icon{width:48px;height:48px;border-radius:999px;font-size:17px;flex:0 0 48px}
        .tp-ai-chatbox__toggle-copy{display:none}
        .tp-ai-chatbox__panel{position:fixed;left:12px;right:12px;bottom:76px;width:auto;height:min(72vh,620px)}
        .tp-ai-chatbox.is-tour-page{bottom:calc(112px + env(safe-area-inset-bottom))}
        .tp-ai-chatbox.is-tour-page .tp-ai-chatbox__panel{bottom:calc(176px + env(safe-area-inset-bottom));height:min(60vh,560px)}
        .progress-wrap{right:12px;bottom:calc(16px + env(safe-area-inset-bottom))}
    }
</style>

<script>
(() => {
    const root = document.getElementById('tp-ai-chatbox');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const locale = root.dataset.locale || 'vi';
    const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '';
    const ui = {
        thinking: <?= json_encode($chatUi['thinking'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        error: <?= json_encode($chatUi['error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        assistantName: <?= json_encode($chatUi['assistantName'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };

    const toggle = root.querySelector('.tp-ai-chatbox__toggle');
    const panel = root.querySelector('.tp-ai-chatbox__panel');
    const closeButton = root.querySelector('.tp-ai-chatbox__close');
    const form = root.querySelector('[data-role="form"]');
    const textarea = form.querySelector('textarea');
    const messages = root.querySelector('[data-role="messages"]');
    const status = root.querySelector('[data-role="status"]');
    const submitButton = form.querySelector('button[type="submit"]');
    const suggestionButtons = Array.from(root.querySelectorAll('[data-suggestion]'));
    const progressWrap = document.getElementById('progressWrap');
    const isTourPage = !!document.querySelector('.package-details-page');
    const history = [];
    let typingNode = null;

    if (isTourPage) {
        root.classList.add('is-tour-page');
    }

    function autosizeTextarea() {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        root.classList.toggle('is-open', open);
        if (progressWrap && window.innerWidth <= 767) {
            progressWrap.style.opacity = open ? '0' : '';
            progressWrap.style.pointerEvents = open ? 'none' : '';
            progressWrap.style.visibility = open ? 'hidden' : '';
        }
        if (open) {
            setTimeout(() => {
                textarea.focus();
                autosizeTextarea();
            }, 30);
        }
    }

    function sourceLabel(count) {
        return locale === 'en' ? `Sources (${count})` : `Nguồn tham khảo (${count})`;
    }

    function appendSources(main, sources = []) {
        const validSources = Array.isArray(sources)
            ? sources.filter((source) => source && source.url).slice(0, 2)
            : [];

        if (validSources.length === 0) return;

        const sourceWrap = document.createElement('details');
        sourceWrap.className = 'tp-ai-chatbox__sources';

        const summary = document.createElement('summary');
        summary.textContent = sourceLabel(sources.length);
        sourceWrap.appendChild(summary);

        const list = document.createElement('div');
        list.className = 'tp-ai-chatbox__source-list';

        validSources.forEach((source, index) => {
            const link = document.createElement('a');
            link.href = source.url;
            link.target = '_blank';
            link.rel = 'noopener';

            const title = document.createElement('span');
            title.textContent = `${index + 1}. ${source.title || source.url}`;
            link.appendChild(title);
            list.appendChild(link);
        });

        if (sources.length > validSources.length) {
            const more = document.createElement('small');
            more.textContent = locale === 'en'
                ? `+${sources.length - validSources.length} more source${sources.length - validSources.length > 1 ? 's' : ''}`
                : `+${sources.length - validSources.length} nguồn khác`;
            list.appendChild(more);
        }

        sourceWrap.appendChild(list);
        main.appendChild(sourceWrap);
    }

    function revealText(bubble, text) {
        const chars = Array.from(text || '');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduceMotion || chars.length === 0) {
            bubble.textContent = text || '';
            return Promise.resolve();
        }

        const step = chars.length > 900 ? 4 : chars.length > 520 ? 3 : chars.length > 260 ? 2 : 1;
        const delay = chars.length > 900 ? 8 : chars.length > 520 ? 10 : 13;
        let index = 0;

        return new Promise((resolve) => {
            const tick = () => {
                index = Math.min(index + step, chars.length);
                bubble.textContent = chars.slice(0, index).join('');

                if (index < chars.length) {
                    window.setTimeout(tick, delay);
                } else {
                    resolve();
                }
            };

            tick();
        });
    }

    function focusMessageStart(item) {
        const offset = Math.max(0, item.offsetTop - 8);
        messages.scrollTop = offset;
    }

    async function appendMessage(role, text, sources = [], options = {}) {
        const item = document.createElement('div');
        item.className = `tp-ai-chatbox__message tp-ai-chatbox__message--${role}`;

        if (role === 'assistant') {
            const avatar = document.createElement('div');
            avatar.className = 'tp-ai-chatbox__message-avatar';
            avatar.innerHTML = '<i class="bi bi-stars"></i>';
            item.appendChild(avatar);
        }

        const main = document.createElement('div');
        main.className = 'tp-ai-chatbox__message-main';

        if (role === 'assistant') {
            const author = document.createElement('div');
            author.className = 'tp-ai-chatbox__message-author';
            author.textContent = ui.assistantName;
            main.appendChild(author);
        }

        const bubble = document.createElement('div');
        bubble.className = 'tp-ai-chatbox__bubble';
        bubble.textContent = options.animate ? '' : text;

        main.appendChild(bubble);
        item.appendChild(main);
        messages.appendChild(item);

        if (role === 'assistant' && options.animate) {
            focusMessageStart(item);
            await revealText(bubble, text);
            appendSources(main, sources);
        } else {
            appendSources(main, role === 'assistant' ? sources : []);
            messages.scrollTop = messages.scrollHeight;
        }

        return item;
    }

    function removeSuggestions() {
        root.querySelectorAll('[data-role="suggestions"]').forEach((node) => node.remove());
    }

    function showTyping() {
        hideTyping();
        typingNode = document.createElement('div');
        typingNode.className = 'tp-ai-chatbox__message tp-ai-chatbox__message--assistant';
        typingNode.innerHTML = `
            <div class="tp-ai-chatbox__message-avatar"><i class="bi bi-stars"></i></div>
            <div class="tp-ai-chatbox__message-main">
                <div class="tp-ai-chatbox__message-author">${ui.assistantName}</div>
                <div class="tp-ai-chatbox__bubble" aria-hidden="true"><span class="tp-ai-chatbox__typing"><span></span><span></span><span></span></span></div>
            </div>
        `;
        messages.appendChild(typingNode);
        messages.scrollTop = messages.scrollHeight;
    }

    function hideTyping() {
        if (typingNode && typingNode.parentNode) {
            typingNode.parentNode.removeChild(typingNode);
        }
        typingNode = null;
    }

    function submitMessage(message) {
        textarea.value = message;
        autosizeTextarea();
        form.requestSubmit();
    }

    async function handleSubmit(event) {
        event.preventDefault();
        const message = textarea.value.trim();
        if (!message) return;

        removeSuggestions();
        appendMessage('user', message);
        history.push({ role: 'user', text: message });
        textarea.value = '';
        autosizeTextarea();
        status.textContent = ui.thinking;
        submitButton.disabled = true;
        textarea.disabled = true;
        messages.setAttribute('aria-busy', 'true');
        showTyping();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    locale,
                    message,
                    page_url: window.location.href,
                    history
                })
            });

            const data = await response.json();

            if (!response.ok || !data.message) {
                throw new Error(data.message || ui.error);
            }

            if (data.debug) {
                console.debug('[TravelPlus AI debug]', data.debug);
            }

            hideTyping();
            status.textContent = '';
            await appendMessage('assistant', data.message, data.sources || [], { animate: true });
            history.push({ role: 'assistant', text: data.message });
            history.splice(0, Math.max(0, history.length - 8));
        } catch (error) {
            hideTyping();
            await appendMessage('assistant', error.message || ui.error, [], { animate: true });
            history.push({ role: 'assistant', text: error.message || ui.error });
            status.textContent = ui.error;
        } finally {
            submitButton.disabled = false;
            textarea.disabled = false;
            messages.removeAttribute('aria-busy');
            textarea.focus();
            autosizeTextarea();
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    closeButton.addEventListener('click', () => setOpen(false));
    form.addEventListener('submit', handleSubmit);
    suggestionButtons.forEach((button) => {
        button.addEventListener('click', () => submitMessage(button.dataset.suggestion || ''));
    });
    textarea.addEventListener('input', autosizeTextarea);
    textarea.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    autosizeTextarea();
})();
</script>
