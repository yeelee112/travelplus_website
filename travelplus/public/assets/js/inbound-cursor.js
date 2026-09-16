(() => {
    const landing = document.querySelector('.inbound-landing');
    if (!landing) return;
    const enabled = window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)');
    const cursor = document.createElement('div');
    cursor.className = 'inbound-cursor';
    cursor.setAttribute('aria-hidden', 'true');
    const label = document.createElement('span');
    cursor.append(label);
    document.body.append(cursor);
    let targetX = 0, targetY = 0, x = 0, y = 0, frame = 0, visible = false, pointerActive = false, scrollFrame = 0;
    const paint = () => {
        x += (targetX - x) * .22;
        y += (targetY - y) * .22;
        cursor.style.transform = `translate3d(${x}px, ${y}px, 0)`;
        if (visible && Math.abs(targetX - x) + Math.abs(targetY - y) > .2) {
            frame = requestAnimationFrame(paint);
        } else frame = 0;
    };
    const hide = () => {
        visible = false;
        cursor.classList.remove('is-visible', 'is-pressed');
        cancelAnimationFrame(frame);
        frame = 0;
    };
    const updateTarget = target => {
        if (!enabled.matches) return hide();
        if (!(target instanceof Element) || !target.closest('.inbound-landing')
            || target.closest('input, textarea, select, [contenteditable], iframe')) return hide();
        if (!visible) { x = targetX; y = targetY; }
        visible = true;
        const photo = target.closest('.inbound-glimpse__card, .inbound-moment, .inbound-destination, .inbound-featured-card');
        const interactive = target.closest('a, button:not(:disabled), summary');
        cursor.classList.toggle('is-photo', Boolean(photo));
        cursor.classList.toggle('is-link', Boolean(interactive) && !photo);
        label.textContent = photo ? 'Explore ↗' : '';
        cursor.classList.add('is-visible');
        if (!frame) frame = requestAnimationFrame(paint);
    };
    document.addEventListener('pointermove', event => {
        pointerActive = enabled.matches && event.pointerType === 'mouse';
        if (!pointerActive) return hide();
        targetX = event.clientX;
        targetY = event.clientY;
        updateTarget(event.target);
    }, {passive: true});
    document.addEventListener('pointerdown', () => cursor.classList.add('is-pressed'), {passive: true});
    document.addEventListener('pointerup', () => cursor.classList.remove('is-pressed'), {passive: true});
    const leave = () => { pointerActive = false; hide(); };
    document.documentElement.addEventListener('pointerleave', leave);
    window.addEventListener('blur', leave);
    document.addEventListener('scroll', () => {
        if (!pointerActive || scrollFrame) return;
        scrollFrame = requestAnimationFrame(() => {
            scrollFrame = 0;
            if (pointerActive) updateTarget(document.elementFromPoint(targetX, targetY));
        });
    }, {passive: true, capture: true});
    enabled.addEventListener('change', hide);
})();
