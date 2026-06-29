export function byId(id) {
    return document.getElementById(id);
}

export function text(id, value) {
    const element = byId(id);
    if (element) {
        element.textContent = value ?? '';
    }
}

export function renderNumbers(container, numbers) {
    if (!container) {
        return;
    }

    container.innerHTML = '';
    numbers.forEach((number) => {
        const chip = document.createElement('span');
        chip.className = 'number-chip';
        chip.textContent = number;
        container.appendChild(chip);
    });
}

export function storageKey(roomCode) {
    return `tp_bingo_player_${roomCode}`;
}

export function gameStatusLabel(status) {
    return {
        created: 'Đã tạo phòng',
        open: 'Đang mở phòng',
        running: 'Đang chơi',
        finished: 'Đã kết thúc',
        ended: 'Đã kết thúc',
    }[status] || status || '-';
}
