import { apiGet, apiPost } from './api.js';
import { renderBoard, updateDrawnState, markCell, hasBingo } from './board.js';
import { Poller } from './polling.js';
import { showToast } from './toast.js';
import { byId, gameStatusLabel, renderNumbers, storageKey, text } from './utils.js';

const root = document.querySelector('[data-bingo-player]');
const roomCode = root?.dataset.roomCode || '';
const key = storageKey(roomCode);
let player = JSON.parse(localStorage.getItem(key) || 'null');
let poller = null;
let drawnNumbers = [];
let bingoEnabled = false;

byId('joinForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        player = await apiPost('/player/join', {
            room_code: roomCode,
            name: byId('playerName').value,
        });
        localStorage.setItem(key, JSON.stringify(player));
        await enterGame();
    } catch (error) {
        showToast(error.message, 'error');
    }
});

byId('bingoBtn')?.addEventListener('click', async () => {
    if (!bingoEnabled || !player) {
        return;
    }
    try {
        const winner = await apiPost('/player/bingo', { player_id: player.id });
        showToast(`BINGO! Bạn là người thắng hạng #${winner.winner_position}`, 'success');
        byId('bingoBtn').disabled = true;
    } catch (error) {
        showToast(error.message, 'error');
    }
});

byId('regenerateBoardBtn')?.addEventListener('click', async () => {
    if (!player?.id) {
        return;
    }

    try {
        const boardData = await apiPost('/player/board/regenerate', { player_id: player.id });
        bingoEnabled = false;
        byId('bingoBtn').disabled = true;
        text('playerNotice', '');
        renderBoard(byId('board'), boardData.cells, drawnNumbers, onCellClick);
        showToast('Đã đổi bảng số.', 'success');
    } catch (error) {
        showToast(error.message, 'error');
    }
});

window.addEventListener('pagehide', () => {
    if (!player?.id || !navigator.sendBeacon) {
        return;
    }
    const base = document.querySelector('meta[name="bingo-base-url"]')?.content || window.location.origin;
    const url = new URL('player/leave', base).toString();
    const payload = new Blob([JSON.stringify({ player_id: player.id })], { type: 'application/json' });
    navigator.sendBeacon(url, payload);
});

async function enterGame() {
    if (!player?.id) {
        byId('joinPanel').classList.remove('d-none');
        byId('gamePanel').classList.add('d-none');
        return;
    }

    byId('joinPanel').classList.add('d-none');
    byId('gamePanel').classList.remove('d-none');
    text('playerNameText', player.name);
    await loadBoard();
    poller?.stop();
    poller = new Poller({
        roomCode,
        playerId: player.id,
        onChange: renderState,
        onError: () => {},
    });
    poller.start();
}

async function loadBoard() {
    const boardData = await apiGet('/player/board', { player_id: player.id });
    renderBoard(byId('board'), boardData.cells, drawnNumbers, onCellClick);
}

async function onCellClick(number, button) {
    if (!drawnNumbers.includes(number)) {
        showToast('Số này chưa được Host xổ.', 'warning');
        return;
    }

    const wasReady = hasBingo(byId('board'));
    try {
        const result = await apiPost('/player/mark', {
            player_id: player.id,
            number,
        });
        markCell(button);
        const isReady = hasBingo(byId('board'));
        if (!wasReady && isReady && result.ready_bingo) {
            enableBingo();
            showToast('Bạn có thể BINGO!', 'success');
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function renderState(state) {
    drawnNumbers = state.drawn_numbers.map(Number);
    text('gameStatus', gameStatusLabel(state.game.status));
    text('currentNumber', state.current_number ?? '-');
    renderNumbers(byId('drawnNumbers'), drawnNumbers);
    updateDrawnState(byId('board'), drawnNumbers);

    const me = state.players.find((item) => Number(item.id) === Number(player?.id));
    if (me?.ready_bingo_at && !me?.bingo_at) {
        enableBingo();
    }
    if (state.game.status !== 'running') {
        byId('bingoBtn').disabled = true;
    }

    const canChangeBoard = ['created', 'open'].includes(state.game.status);
    const regenerateButton = byId('regenerateBoardBtn');
    if (regenerateButton) {
        regenerateButton.disabled = !canChangeBoard;
        regenerateButton.classList.toggle('d-none', !canChangeBoard);
    }
}

function enableBingo() {
    bingoEnabled = true;
    byId('bingoBtn').disabled = false;
    text('playerNotice', 'Bạn có thể BINGO!');
}

enterGame();
