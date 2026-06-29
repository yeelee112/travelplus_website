import { apiGet } from './api.js';

export class Poller {
    constructor({ roomCode, playerId = null, onChange, onError }) {
        this.roomCode = roomCode;
        this.playerId = playerId;
        this.onChange = onChange;
        this.onError = onError;
        this.version = 0;
        this.timer = null;
        this.lastHeartbeat = 0;
    }

    start() {
        this.stop();
        this.tick();
        this.timer = window.setInterval(() => this.tick(), 1000);
    }

    stop() {
        if (this.timer) {
            window.clearInterval(this.timer);
        }
    }

    async tick() {
        try {
            const now = Date.now();
            const heartbeat = this.playerId && now - this.lastHeartbeat > 15000;
            const data = await apiGet('/updates', {
                room_code: this.roomCode,
                version: this.version,
                player_id: this.playerId,
                heartbeat: heartbeat ? 1 : 0,
            });
            if (heartbeat) {
                this.lastHeartbeat = now;
            }
            if (data.changed === false) {
                return;
            }
            this.version = data.version;
            this.onChange(data.state);
        } catch (error) {
            if (this.onError) {
                this.onError(error);
            }
        }
    }
}
