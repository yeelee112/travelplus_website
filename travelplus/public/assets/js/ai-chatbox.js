(() => {
    const root = document.getElementById('tp-ai-chatbox');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const locale = root.dataset.locale || 'vi';
    const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '';
    const uiNode = root.querySelector('[data-ai-chatbox-i18n]');
    const ui = (() => {
        try {
            return JSON.parse(uiNode?.textContent || '{}');
        } catch (error) {
            return {thinking: '', error: 'Travel Plus AI is temporarily unavailable.', assistantName: 'Travel Plus AI'};
        }
    })();

    const toggle = root.querySelector('.tp-ai-chatbox__toggle');
    const panel = root.querySelector('.tp-ai-chatbox__panel');
    const closeButton = root.querySelector('.tp-ai-chatbox__close');
    const contactToggle = root.querySelector('.tp-ai-chatbox__contact-toggle');
    const contactPanel = root.querySelector('.tp-ai-chatbox__contact-panel');
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
        if (open) {
            setContactOpen(false);
        }
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

    function setContactOpen(open) {
        if (!contactToggle || !contactPanel) return;
        contactPanel.hidden = !open;
        contactToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        root.classList.toggle('is-contact-open', open);
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
    if (contactToggle && contactPanel) {
        contactToggle.addEventListener('click', () => {
            setOpen(false);
            setContactOpen(contactPanel.hidden);
        });
        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                setContactOpen(false);
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setContactOpen(false);
            }
        });
    }
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

