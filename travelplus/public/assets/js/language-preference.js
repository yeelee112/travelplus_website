(() => {
  'use strict';

  const cookieName = 'travelplus_locale';
  const allowedLanguages = ['vi', 'en'];
  const saveLanguage = (language) => {
    if (!allowedLanguages.includes(language)) return;

    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${cookieName}=${language}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
  };

  document.querySelectorAll('[data-language-choice]').forEach((link) => {
    link.addEventListener('click', (event) => {
      saveLanguage(link.dataset.languageChoice || '');

      const target = new URL(link.href, window.location.href);
      if (target.origin !== window.location.origin) {
        event.preventDefault();
        window.location.assign(`${target.pathname}${target.search}${target.hash}`);
      }
    });
  });

  const entry = document.querySelector('[data-language-entry]');
  if (!entry) return;

  document.body.classList.add('language-entry-open');
  const firstChoice = entry.querySelector('[data-language-choice]');
  window.requestAnimationFrame(() => firstChoice?.focus());
})();
