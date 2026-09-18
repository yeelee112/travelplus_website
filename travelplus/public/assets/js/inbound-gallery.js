(() => {
    const track = document.querySelector('#inbound-glimpse-slider');
    if (!track) return;
    const cards = [...track.querySelectorAll('.inbound-glimpse__card')];
    const buttons = [...document.querySelectorAll('[data-glimpse-direction]')];
    const position = document.querySelector('.inbound-glimpse__position');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const step = () => cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : track.clientWidth;
    const update = () => {
        const maximum = track.scrollWidth - track.clientWidth;
        buttons.forEach(button => {
            button.disabled = Number(button.dataset.glimpseDirection) < 0
                ? track.scrollLeft <= 2 : track.scrollLeft >= maximum - 2;
        });
        if (position) position.textContent = `${Math.min(cards.length, Math.round(track.scrollLeft / step()) + 1)} / ${cards.length}`;
    };
    const move = direction => track.scrollBy({left: direction * step(), behavior: reducedMotion.matches ? 'instant' : 'smooth'});
    buttons.forEach(button => button.addEventListener('click', () => move(Number(button.dataset.glimpseDirection))));
    track.addEventListener('keydown', event => {
        if (event.target !== track || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        move(event.key === 'ArrowLeft' ? -1 : 1);
    });
    track.addEventListener('scroll', update, {passive: true});
    window.addEventListener('resize', update, {passive: true});
    document.querySelectorAll('[data-glimpse-open]').forEach(button => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.glimpseOpen);
            if (dialog instanceof HTMLDialogElement) {
                dialog.querySelectorAll('[data-destination-photo]').forEach(photo => {
                    if (!photo.hasAttribute('src')) photo.src = photo.dataset.destinationPhoto;
                });
                dialog.showModal();
            }
        });
    });
    document.querySelectorAll('.inbound-glimpse-dialog').forEach(dialog => {
        const close = () => dialog.close();
        dialog.querySelector('[data-glimpse-close]')?.addEventListener('click', close);
        dialog.querySelector('[data-glimpse-plan]')?.addEventListener('click', event => {
            const destination = document.querySelector('.inbound-enquiry-form [name="destination"]');
            const name = event.currentTarget.dataset.glimpsePlan;
            if (destination && name) {
                const current = destination.value.trim();
                if (!current.toLowerCase().includes(name.toLowerCase())) {
                    destination.value = current ? `${current}, ${name}` : name;
                    destination.dispatchEvent(new Event('input', {bubbles: true}));
                }
            }
            close();
        });
        dialog.addEventListener('click', event => {
            const box = dialog.getBoundingClientRect();
            if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) close();
        });
    });
    update();
})();
