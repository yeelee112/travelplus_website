(() => {
  'use strict';

  const setCookie = (name, value) => {
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${name}=${value}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
  };
  const hasCookie = (name, values) => document.cookie.split(';').some((part) => {
    const [key, value] = part.trim().split('=');
    return key === name && values.includes(value);
  });
  const cookieName = 'travelplus_locale';
  const allowedLanguages = ['vi', 'en'];
  const saveLanguage = (language) => {
    if (!allowedLanguages.includes(language)) return;

    setCookie(cookieName, language);
    setCookie('travelplus_language_seen', '1');
  };

  document.querySelectorAll('[data-language-choice]').forEach((link) => {
    link.addEventListener('click', (event) => {
      saveLanguage(link.dataset.languageChoice || '');
      document.querySelector('[data-language-entry]')?.close();

      const target = new URL(link.href, window.location.href);
      if (target.origin !== window.location.origin) {
        event.preventDefault();
        window.location.assign(`${target.pathname}${target.search}${target.hash}`);
      }
    });
  });

  const switchers = document.querySelectorAll('details.site-language-switcher');
  document.addEventListener('click', (event) => {
    switchers.forEach((switcher) => {
      if (!switcher.contains(event.target)) switcher.open = false;
    });
  });
  switchers.forEach((switcher) => {
    switcher.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && switcher.open) {
        switcher.open = false;
        switcher.querySelector('summary').focus();
      }
    });
    switcher.addEventListener('focusout', (event) => {
      if (!switcher.contains(event.relatedTarget)) switcher.open = false;
    });
  });

  const entry = document.querySelector('[data-language-entry]');
  if (!entry || typeof entry.showModal !== 'function') return;
  document.querySelectorAll('[data-language-open]').forEach((button) => {
    button.hidden = false;
    button.addEventListener('click', () => entry.showModal());
  });

  const dismiss = () => entry.close();
  entry.querySelectorAll('[data-language-dismiss]').forEach((button) => button.addEventListener('click', dismiss));
  entry.addEventListener('click', (event) => {
    const rect = entry.getBoundingClientRect();
    if (event.target === entry && (event.clientX < rect.left || event.clientX > rect.right
        || event.clientY < rect.top || event.clientY > rect.bottom)) dismiss();
  });
  entry.addEventListener('close', () => setCookie('travelplus_language_seen', '1'));
  if (hasCookie(cookieName, allowedLanguages) || hasCookie('travelplus_language_seen', ['1'])) return;
  entry.showModal();
  setCookie('travelplus_language_seen', '1');
})();
