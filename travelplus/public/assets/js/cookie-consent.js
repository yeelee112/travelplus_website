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

