(() => {
    if (!('PerformanceObserver' in window)) return;
    let lcp = 0;
    let cls = 0;
    let shiftWindow = 0;
    let shiftStart = 0;
    let lastShift = 0;
    let sent = false;
    const observe = (type, callback) => {
        if (!PerformanceObserver.supportedEntryTypes.includes(type)) return;
        new PerformanceObserver(list => list.getEntries().forEach(callback)).observe({ type, buffered: true });
    };
    observe('largest-contentful-paint', entry => { lcp = entry.startTime; });
    observe('layout-shift', entry => {
        if (entry.hadRecentInput) return;
        if (entry.startTime - lastShift > 1000 || entry.startTime - shiftStart > 5000) {
            shiftStart = entry.startTime;
            shiftWindow = 0;
        }
        shiftWindow += entry.value;
        lastShift = entry.startTime;
        cls = Math.max(cls, shiftWindow);
    });
    const report = () => {
        if (sent || typeof window.travelplusTrackEvent !== 'function') return;
        const consent = document.cookie.split('; ').find(value => value.startsWith('tp_cookie_consent='));
        if (!consent || !decodeURIComponent(consent.split('=')[1] || '').split(',').includes('analytics')) return;
        const navigation = performance.getEntriesByType('navigation')[0];
        if (!navigation) return;
        sent = true;
        window.travelplusTrackEvent('site_performance', {
            page_type: document.querySelector('[data-checkout-stepper]') ? 'checkout' : document.querySelector('.tour-detail-content') ? 'tour' : document.body.classList.contains('is-home-page') ? 'home' : 'other',
            device_layout: matchMedia('(max-width:767px)').matches ? 'mobile' : 'desktop',
            ttfb_ms: Math.round(navigation.responseStart - navigation.requestStart),
            dom_ready_ms: Math.round(navigation.domContentLoadedEventEnd),
            lcp_ms: Math.round(lcp),
            cls: Number(cls.toFixed(4)),
            transport_type: 'beacon'
        });
    };
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') report(); });
    window.addEventListener('pagehide', report);
})();
