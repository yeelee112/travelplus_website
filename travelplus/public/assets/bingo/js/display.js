import { apiPost } from './api.js';
import { Poller } from './polling.js';
import { confettiBurst } from './confetti.js';
import { showToast } from './toast.js';
import { byId, gameStatusLabel, renderNumbers, text } from './utils.js';

const root = document.querySelector('[data-bingo-display]');
const roomCode = root?.dataset.roomCode || '';
const seenWinners = new Set();
let drawing = false;
let lastState = null;

const poller = new Poller({
    roomCode,
    onChange: renderState,
    onError: () => {},
});
poller.start();

byId('displayDrawNumberBtn')?.addEventListener('click', async () => {
    if (drawing) {
        return;
    }

    drawing = true;
    setDrawButtonDisabled(true);
    try {
        await apiPost('/host/draw', { room_code: roomCode });
        await poller.tick();
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        drawing = false;
        updateDrawButton();
    }
});

function renderState(state) {
    lastState = state;
    text('roomCodeText', state.game.room_code);
    text('gameStatus', gameStatusLabel(state.game.status));
    text('currentNumber', state.current_number ?? '-');
    text('playerCount', state.player_count);
    text('winnerCount', state.winner_count);
    renderNumbers(byId('drawnNumbers'), state.drawn_numbers);
    renderWinners(state.winners);

    state.winners.forEach((winner) => {
        if (!seenWinners.has(String(winner.id))) {
            seenWinners.add(String(winner.id));
            showWinner(winner.name);
        }
    });

    const isGameOver = state.game.status === 'finished' || state.winner_count >= Number(state.game.max_winners);
    byId('gameOver').classList.toggle('d-none', !isGameOver);
    const showRules = ['created', 'open'].includes(state.game.status);
    byId('displayRulesPanel')?.classList.toggle('d-none', !showRules);
    updateDrawButton();
}

function renderWinners(winners) {
    const element = byId('topWinners');
    element.innerHTML = winners.length
        ? winners.map((winner) => `<div class="fs-4 fw-bold">Hạng #${winner.winner_position}: ${escapeHtml(winner.name)}</div>`).join('')
        : '<div class="text-white-50">Chưa có người thắng</div>';
}

function showWinner(name) {
    const popup = byId('winnerPopup');
    text('winnerPopupText', `${name} BINGO!`);
    popup.classList.add('is-visible');
    confettiBurst();
    window.setTimeout(() => popup.classList.remove('is-visible'), 5000);
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

function setDrawButtonDisabled(disabled) {
    const button = byId('displayDrawNumberBtn');
    if (button) {
        button.disabled = disabled;
    }
}

function updateDrawButton() {
    if (!lastState) {
        setDrawButtonDisabled(true);
        return;
    }

    const isGameOver = lastState.game.status === 'finished'
        || lastState.winner_count >= Number(lastState.game.max_winners);
    setDrawButtonDisabled(lastState.game.status !== 'running' || isGameOver || drawing);
}
