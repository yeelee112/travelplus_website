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
    update();
})();
