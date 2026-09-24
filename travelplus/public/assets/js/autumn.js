/* Progressive enhancement: native selects remain usable without JavaScript. */
(() => {
  const root = document.querySelector('.autumn-landing');
  if (!root) return;
  const menus = [];
  root.querySelectorAll('.at-search select').forEach((select, index) => {
    const label = select.closest('label');
    const field = document.createElement('div');
    field.className = 'at-search-field';
    const caption = label.querySelector('span').firstChild.textContent.trim();
    while (label.firstChild) field.append(label.firstChild);
    label.replaceWith(field);
    const holder = select.parentElement;
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'at-select-trigger';
    trigger.id = `at-select-${index}`;
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', `at-options-${index}`);
    const text = document.createElement('span');
    const chevron = document.createElement('i');
    chevron.className = 'bi bi-chevron-down';
    chevron.setAttribute('aria-hidden', 'true');
    trigger.append(text, chevron);
    const list = document.createElement('div');
    list.className = 'at-select-menu';
    list.id = `at-options-${index}`;
    list.setAttribute('role', 'listbox');
    list.setAttribute('aria-label', caption);
    list.hidden = true;
    const options = Array.from(select.options).map(option => {
      const item = document.createElement('div');
      item.className = 'at-select-option';
      item.setAttribute('role', 'option');
      item.tabIndex = -1;
      item.textContent = option.textContent;
      item.dataset.value = option.value;
      list.append(item);
      return item;
    });
    const sync = () => {
      text.textContent = select.selectedOptions[0].textContent;
      trigger.setAttribute('aria-label', `${caption}: ${text.textContent}`);
      options.forEach(item => item.setAttribute('aria-selected', String(item.dataset.value === select.value)));
    };
    const close = (focus = false) => {
      list.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      if (focus) trigger.focus();
    };
    const open = () => {
      menus.forEach(menu => menu.close());
      list.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
      options[Math.max(0, select.selectedIndex)].focus();
    };
    const choose = item => {
      select.value = item.dataset.value;
      select.dispatchEvent(new Event('change', { bubbles: true }));
      close(true);
    };
    trigger.addEventListener('click', () => list.hidden ? open() : close());
    trigger.addEventListener('keydown', event => {
      if (['ArrowDown', 'ArrowUp'].includes(event.key)) { event.preventDefault(); open(); }
    });
    options.forEach(item => item.addEventListener('click', () => choose(item)));
    let query = '', lastKey = 0;
    list.addEventListener('keydown', event => {
      const current = options.indexOf(document.activeElement);
      if (event.key === 'Escape') { event.preventDefault(); close(true); }
      else if (event.key === 'Tab') close();
      else if (['Enter', ' '].includes(event.key)) { event.preventDefault(); if (current >= 0) choose(options[current]); }
      else if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1 : (current + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
        options[next].focus();
      } else if (event.key.length === 1) {
        query = Date.now() - lastKey > 700 ? event.key : query + event.key;
        lastKey = Date.now();
        options.find(item => item.textContent.toLocaleLowerCase().startsWith(query.toLocaleLowerCase()))?.focus();
      }
    });
    select.addEventListener('change', sync);
    field.addEventListener('focusout', event => { if (!field.contains(event.relatedTarget)) close(); });
    select.hidden = true;
    holder.append(trigger);
    field.append(list);
    sync();
    menus.push({ field, close });
  });
  document.addEventListener('pointerdown', event => menus.forEach(menu => { if (!menu.field.contains(event.target)) menu.close(); }));

  const track = root.querySelector('.at-tour-grid');
  const controls = root.querySelector('.at-slider-controls');
  if (!track || !controls) return;
  controls.hidden = false;
  const previous = controls.querySelector('[data-slide="-1"]');
  const next = controls.querySelector('[data-slide="1"]');
  const update = () => {
    previous.disabled = track.scrollLeft < 2;
    next.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
  };
  controls.querySelectorAll('button').forEach(button => button.addEventListener('click', () => {
    const card = track.querySelector('.at-tour-card');
    const gap = parseFloat(getComputedStyle(track).gap) || 0;
    track.scrollBy({ left: Number(button.dataset.slide) * (card.getBoundingClientRect().width + gap), behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
  }));
  track.addEventListener('scroll', update, { passive: true });
  window.addEventListener('resize', update);
  update();
})();
