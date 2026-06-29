import { apiPost } from './api.js';
import { showToast } from './toast.js';

const root = document.querySelector('[data-bingo-entry]');
const targetPath = root?.dataset.targetPath || 'play';
const roomInput = document.getElementById('roomCodeEntry');
const createInput = document.getElementById('newRoomCode');
const createButton = document.getElementById('quickCreateRoomBtn');

document.getElementById('roomEntryForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    goToRoom(roomInput.value);
});

createButton?.addEventListener('click', async () => {
    try {
        const data = await apiPost('/host/create', {
            room_code: normalizeRoom(createInput?.value || ''),
        });
        goToRoom(data.room_code);
    } catch (error) {
        showToast(error.message, 'error');
    }
});

function goToRoom(value) {
    const roomCode = normalizeRoom(value);
    if (!roomCode) {
        showToast('Vui lòng nhập mã phòng.', 'warning');
        return;
    }

    const base = document.querySelector('meta[name="bingo-base-url"]')?.content || window.location.origin;
    window.location.href = new URL(`${targetPath}/${encodeURIComponent(roomCode)}`, base).toString();
}

function normalizeRoom(value) {
    return String(value || '').trim().toUpperCase();
}
