<?php

namespace App\Controllers;

use App\Services\BoardService;
use App\Services\DrawService;
use App\Services\GameService;
use App\Services\PlayerService;
use App\Services\PollingService;
use App\Services\WinnerService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ApiController extends BaseController
{
    public function create(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new GameService())->createRoom($input['room_code'] ?? null);
        }, 'Đã tạo phòng.');
    }

    public function open(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new GameService())->openRoom((string) ($input['room_code'] ?? ''));
        }, 'Đã mở phòng.');
    }

    public function start(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new GameService())->startGame((string) ($input['room_code'] ?? ''));
        }, 'Game đã bắt đầu.');
    }

    public function draw(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new DrawService())->draw((string) ($input['room_code'] ?? ''));
        }, 'Đã xổ số.');
    }

    public function reset(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new GameService())->resetGame((string) ($input['room_code'] ?? ''));
        }, 'Đã chơi lại.');
    }

    public function end(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new GameService())->endGame((string) ($input['room_code'] ?? ''));
        }, 'Game đã kết thúc.');
    }

    public function join(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new PlayerService())->join((string) ($input['room_code'] ?? ''), (string) ($input['name'] ?? ''));
        }, 'Người chơi đã tham gia.');
    }

    public function board(): ResponseInterface
    {
        return $this->handle(function (array $_) {
            $playerId = (int) ($this->request->getGet('player_id') ?? 0);

            return (new BoardService())->boardForPlayer($playerId);
        });
    }

    public function regenerateBoard(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new BoardService())->regenerateForPlayer((int) ($input['player_id'] ?? 0));
        }, 'Đã đổi bảng số.');
    }

    public function mark(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new PlayerService())->mark((int) ($input['player_id'] ?? 0), (int) ($input['number'] ?? 0));
        }, 'Đã đánh dấu số.');
    }

    public function leave(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new PlayerService())->leave((int) ($input['player_id'] ?? 0));
        }, 'Người chơi đã rời phòng.');
    }

    public function bingo(): ResponseInterface
    {
        return $this->handle(static function (array $input) {
            return (new WinnerService())->confirmBingo((int) ($input['player_id'] ?? 0));
        }, 'Đã xác nhận Bingo.');
    }

    public function status(): ResponseInterface
    {
        return $this->handle(function (array $_) {
            $roomCode = (string) ($this->request->getGet('room_code') ?? '');
            $game = (new GameService())->requireGame($roomCode);

            return (new GameService())->statusPayload((int) $game['id']);
        });
    }

    public function updates(): ResponseInterface
    {
        return $this->handle(function (array $_) {
            return (new PollingService())->updates(
                (string) ($this->request->getGet('room_code') ?? ''),
                (int) ($this->request->getGet('version') ?? 0),
                $this->request->getGet('player_id') !== null ? (int) $this->request->getGet('player_id') : null,
                (bool) ((int) ($this->request->getGet('heartbeat') ?? 0))
            );
        });
    }

    private function handle(callable $callback, string $message = 'Thành công'): ResponseInterface
    {
        try {
            $result = $callback($this->input());

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => null,
            ]);
        }
    }

    private function input(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        $post = $this->request->getPost();

        return is_array($post) ? $post : [];
    }
}
