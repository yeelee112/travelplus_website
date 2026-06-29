import { apiPost } from './api.js';
import { Poller } from './polling.js';
import { showToast } from './toast.js';
import { byId, gameStatusLabel, renderNumbers, text } from './utils.js';

const root = document.querySelector('[data-bingo-host]');
let roomCode = root?.dataset.roomCode || '';
let poller = null;

const roomInput = byId('roomCodeInput');
if (roomInput && roomCode) {
    roomInput.value = roomCode;
}

function currentRoom() {
    return (roomInput?.value || roomCode || '').trim().toUpperCase();
}

async function hostAction(path, successMessage) {
    const data = await apiPost(path, { room_code: currentRoom() });
    roomCode = data.room_code || currentRoom();
    if (roomInput) {
        roomInput.value = roomCode;
    }
    startPolling();
    showToast(successMessage, 'success');
}

byId('createRoomBtn')?.addEventListener('click', async () => {
    try {
        const data = await apiPost('/host/create', { room_code: currentRoom() });
        roomCode = data.room_code;
        if (roomInput) {
            roomInput.value = roomCode;
        }
        const base = document.querySelector('meta[name="bingo-base-url"]')?.content || window.location.origin;
        window.history.replaceState({}, '', new URL(`host/${roomCode}`, base).toString());
        startPolling();
        showToast('Đã tạo phòng.', 'success');
    } catch (error) {
        showToast(error.message, 'error');
    }
});

byId('openRoomBtn')?.addEventListener('click', () => hostAction('/host/open', 'Đã mở phòng.').catch((error) => showToast(error.message, 'error')));
byId('startGameBtn')?.addEventListener('click', () => hostAction('/host/start', 'Game đã bắt đầu.').catch((error) => showToast(error.message, 'error')));
byId('drawNumberBtn')?.addEventListener('click', () => hostAction('/host/draw', 'Đã xổ số.').catch((error) => showToast(error.message, 'error')));
byId('resetGameBtn')?.addEventListener('click', () => {
    if (window.confirm('Chơi lại game này?')) {
        hostAction('/host/reset', 'Đã chơi lại.').catch((error) => showToast(error.message, 'error'));
    }
});
byId('endGameBtn')?.addEventListener('click', () => {
    if (window.confirm('Kết thúc game này?')) {
        hostAction('/host/end', 'Game đã kết thúc.').catch((error) => showToast(error.message, 'error'));
    }
});

function startPolling() {
    const code = currentRoom();
    if (!code) {
        return;
    }
    poller?.stop();
    poller = new Poller({
        roomCode: code,
        onChange: renderState,
        onError: () => {},
    });
    poller.start();
}

function renderState(state) {
    text('roomCodeText', state.game.room_code);
    text('gameStatus', gameStatusLabel(state.game.status));
    text('currentNumber', state.current_number ?? '-');
    text('playerCount', state.player_count);
    text('onlineCount', state.online_count);
    text('offlineCount', state.offline_count);
    text('readyCount', state.ready_players.length);
    text('winnerCount', state.winner_count);
    renderNumbers(byId('drawnNumbers'), state.drawn_numbers);
    renderList('readyPlayers', state.ready_players.map((player) => player.name));
    renderList('winners', state.winners.map((winner) => `Hạng #${winner.winner_position} ${winner.name}`));
    renderList('players', state.players.map((player) => `${player.online ? 'Đang online' : 'Đã offline'} - ${player.name}`));

    const drawButton = byId('drawNumberBtn');
    if (drawButton) {
        drawButton.disabled = state.game.status !== 'running' || state.winner_count >= Number(state.game.max_winners);
    }
}

function renderList(id, rows) {
    const element = byId(id);
    if (!element) {
        return;
    }
    element.innerHTML = rows.length
        ? rows.map((row) => `<li class="list-group-item">${escapeHtml(row)}</li>`).join('')
        : '<li class="list-group-item text-muted">Chưa có dữ liệu</li>';
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}

startPolling();
