export function showToast(message, type = 'info') {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }

    const item = document.createElement('div');
    item.className = `alert alert-${type === 'error' ? 'danger' : type} shadow-sm mb-0`;
    item.textContent = message;
    stack.appendChild(item);
    window.setTimeout(() => item.remove(), 3600);
}
