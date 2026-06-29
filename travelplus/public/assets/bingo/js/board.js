export function renderBoard(container, cells, drawnNumbers, onCellClick) {
    container.innerHTML = '';
    const drawn = new Set(drawnNumbers.map(Number));

    cells.forEach((cell) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'bingo-cell';
        button.dataset.number = cell.number;
        button.textContent = cell.number;

        if (drawn.has(Number(cell.number))) {
            button.classList.add('is-drawn');
        }
        if (Number(cell.marked) === 1) {
            button.classList.add('is-marked');
            button.disabled = true;
        }

        button.addEventListener('click', () => onCellClick(Number(cell.number), button));
        container.appendChild(button);
    });
}

export function updateDrawnState(container, drawnNumbers) {
    const drawn = new Set(drawnNumbers.map(Number));
    container.querySelectorAll('.bingo-cell').forEach((cell) => {
        cell.classList.toggle('is-drawn', drawn.has(Number(cell.dataset.number)));
    });
}

export function markCell(button) {
    button.classList.add('is-marked');
    button.disabled = true;
}

export function hasBingo(container) {
    return completedLineCount(container) >= 2;
}

export function completedLineCount(container) {
    const cells = Array.from(container.querySelectorAll('.bingo-cell'));
    const marked = cells.map((cell) => cell.classList.contains('is-marked'));
    let completed = 0;

    for (let i = 0; i < 5; i++) {
        if ([0, 1, 2, 3, 4].every((j) => marked[i * 5 + j])) {
            completed += 1;
        }
        if ([0, 1, 2, 3, 4].every((j) => marked[j * 5 + i])) {
            completed += 1;
        }
    }

    if ([0, 6, 12, 18, 24].every((i) => marked[i])) {
        completed += 1;
    }
    if ([4, 8, 12, 16, 20].every((i) => marked[i])) {
        completed += 1;
    }

    return completed;
}
