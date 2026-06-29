export function confettiBurst() {
    const colors = ['#ef4444', '#f59e0b', '#22c55e', '#0ea5e9', '#8b5cf6'];
    for (let i = 0; i < 90; i++) {
        const piece = document.createElement('span');
        piece.style.position = 'fixed';
        piece.style.left = `${Math.random() * 100}vw`;
        piece.style.top = '-12px';
        piece.style.width = '8px';
        piece.style.height = '14px';
        piece.style.background = colors[i % colors.length];
        piece.style.zIndex = '1200';
        piece.style.transform = `rotate(${Math.random() * 360}deg)`;
        piece.style.transition = 'transform 1.8s ease-out, top 1.8s ease-out, opacity 1.8s ease-out';
        document.body.appendChild(piece);
        requestAnimationFrame(() => {
            piece.style.top = `${80 + Math.random() * 20}vh`;
            piece.style.transform = `translateX(${(Math.random() - 0.5) * 240}px) rotate(${Math.random() * 720}deg)`;
            piece.style.opacity = '0';
        });
        window.setTimeout(() => piece.remove(), 1900);
    }
}
