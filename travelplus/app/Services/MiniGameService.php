<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class MiniGameService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function state(?string $playerToken = null): array
    {
        $state = $this->db->table('game_state s')
            ->select('s.*, q.plate_code, q.province, q.places, q.specialty, q.airport, q.unesco')
            ->join('game_questions q', 'q.id = s.question_id', 'left')->where('s.id', 1)->get()->getRowArray() ?? [];
        $round = max(1, (int) ($state['question_number'] ?? 1));
        $buzzes = [];
        if (! empty($state['question_id'])) {
            $buzzes = $this->db->table('game_buzzes b')
                ->select('b.id, b.player_id, b.status, b.buzz_order, b.buzzed_at, p.name, p.office')
                ->join('game_players p', 'p.id = b.player_id')
                ->where(['b.question_id' => $state['question_id'], 'b.round_no' => $round])
                ->orderBy('b.buzz_order', 'ASC')->get()->getResultArray();
        }
        $scores = $this->db->table('game_players')->select('office, SUM(score) score, COUNT(*) players')
            ->groupBy('office')->orderBy('score', 'DESC')->get()->getResultArray();
        $me = null;
        if ($playerToken) {
            $me = $this->db->table('game_players')->where('token', hash('sha256', $playerToken))->get()->getRowArray();
        }
        $state += $this->questionPresentation($state);
        if ($playerToken) {
            $scores = [];
            $state['question_answer'] = '';
        }
        return ['state' => $state, 'buzzes' => $buzzes, 'scores' => $scores, 'me' => $me];
    }

    public function join(string $name, string $office): array
    {
        $name = trim($name);
        $offices = ['Điều Hành VPSG', 'VPDN', 'Nhân Sự', 'Kế Toán'];
        if ($name === '' || mb_strlen($name) > 100 || ! in_array($office, $offices, true)) {
            throw new RuntimeException('Tên hoặc văn phòng không hợp lệ.');
        }
        $token = bin2hex(random_bytes(24));
        $this->db->table('game_players')->insert([
            'name' => $name, 'office' => $office, 'token' => hash('sha256', $token), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        return ['token' => $token, 'player_id' => $this->db->insertID()];
    }

    public function buzz(string $token): void
    {
        $this->db->transStart();
        $state = $this->db->query('SELECT * FROM game_state WHERE id = 1 FOR UPDATE')->getRowArray();
        $player = $this->db->table('game_players')->where('token', hash('sha256', $token))->get()->getRowArray();
        if (! $player || ! $state || ! $state['buzz_open'] || ! $state['question_id']) {
            $this->db->transRollback();
            throw new RuntimeException('Lượt giành quyền chưa mở hoặc phiên chơi không hợp lệ.');
        }
        $where = ['question_id' => $state['question_id'], 'round_no' => max(1, (int) $state['question_number']), 'player_id' => $player['id']];
        if ($this->db->table('game_buzzes')->where($where)->countAllResults() > 0) {
            $this->db->transRollback();
            return;
        }
        $max = $this->db->table('game_buzzes')->selectMax('buzz_order', 'n')
            ->where(['question_id' => $state['question_id'], 'round_no' => max(1, (int) $state['question_number'])])->get()->getRowArray();
        $order = ((int) ($max['n'] ?? 0)) + 1;
        $active = $this->db->table('game_buzzes')->where(['question_id' => $state['question_id'], 'round_no' => max(1, (int) $state['question_number']), 'status' => 'answering'])->countAllResults();
        $this->db->table('game_buzzes')->insert($where + ['status' => $active ? 'waiting' : 'answering', 'buzzed_at' => date('Y-m-d H:i:s.u'), 'buzz_order' => $order]);
        $this->touch();
        $this->db->transComplete();
    }

    public function command(string $action, array $data = []): void
    {
        $this->db->transStart();
        $state = $this->db->query('SELECT * FROM game_state WHERE id = 1 FOR UPDATE')->getRowArray();
        if (! $state) throw new RuntimeException('Chưa khởi tạo dữ liệu game.');
        $round = max(1, (int) $state['question_number']);
        $scope = ['question_id' => $state['question_id'], 'round_no' => $round];
        switch ($action) {
            case 'start':
                $first = $this->db->table('game_questions')->where('is_active', 1)->orderBy('sort_order')->orderBy('id')->get(1)->getRowArray();
                if (! $first) throw new RuntimeException('Chưa có câu hỏi.');
                $this->db->table('game_state')->where('id', 1)->update(['status' => 'playing', 'question_id' => $first['id'], 'question_number' => 1, 'question_type' => $this->chooseQuestionType($first), 'buzz_open' => 1, 'answer_revealed' => 0, 'bonus_active' => 0, 'bonus_type' => null, 'countdown_ends_at' => date('Y-m-d H:i:s', time() + (int) $state['countdown_seconds'])]);
                break;
            case 'next':
                $next = $this->db->table('game_questions')->where('is_active', 1)->where('id >', (int) $state['question_id'])->orderBy('id')->get(1)->getRowArray();
                if (! $next) { $this->db->table('game_state')->where('id', 1)->update(['status' => 'finished', 'buzz_open' => 0]); break; }
                $this->db->table('game_state')->where('id', 1)->update(['question_id' => $next['id'], 'question_number' => $round + 1, 'question_type' => $this->chooseQuestionType($next), 'buzz_open' => 1, 'answer_revealed' => 0, 'bonus_active' => 0, 'bonus_type' => null, 'countdown_ends_at' => date('Y-m-d H:i:s', time() + (int) $state['countdown_seconds'])]);
                break;
            case 'wrong':
                $current = $this->db->table('game_buzzes')->where($scope + ['status' => 'answering'])->get()->getRowArray();
                if ($current) $this->db->table('game_buzzes')->where('id', $current['id'])->update(['status' => 'wrong']);
                $next = $this->db->table('game_buzzes')->where($scope + ['status' => 'waiting'])->orderBy('buzz_order')->get(1)->getRowArray();
                if ($next) $this->db->table('game_buzzes')->where('id', $next['id'])->update(['status' => 'answering']);
                break;
            case 'correct':
                $current = $this->db->table('game_buzzes')->where($scope + ['status' => 'answering'])->get()->getRowArray();
                if (! $current) throw new RuntimeException('Chưa có người đang trả lời.');
                $points = (int) $state['main_points'];
                $this->db->table('game_players')->where('id', $current['player_id'])->set('score', "score + {$points}", false)->update();
                $this->db->table('game_buzzes')->where('id', $current['id'])->update(['status' => 'correct']);
                $this->db->table('game_state')->where('id', 1)->update(['buzz_open' => 0, 'answer_revealed' => 1, 'bonus_active' => 0, 'bonus_type' => null]);
                break;
            case 'reset_buzz':
                $this->db->table('game_buzzes')->where($scope)->delete();
                $this->db->table('game_state')->where('id', 1)->update(['buzz_open' => 1]);
                break;
            case 'reveal': $this->db->table('game_state')->where('id', 1)->set('answer_revealed', 1)->update(); break;
            case 'countdown':
                $seconds = in_array((int) ($data['seconds'] ?? 20), [5,10,15,20,30], true) ? (int) $data['seconds'] : 20;
                $this->db->table('game_state')->where('id', 1)->update(['countdown_seconds' => $seconds, 'countdown_ends_at' => date('Y-m-d H:i:s', time() + $seconds), 'buzz_open' => 1]); break;
            case 'reset_scores': $this->db->table('game_players')->set('score', 0)->update(); break;
            case 'reset_game':
                $this->db->table('game_buzzes')->truncate();
                $this->db->table('game_players')->set('score', 0)->update();
                $this->db->table('game_state')->where('id', 1)->update(['status' => 'waiting', 'question_id' => null, 'question_number' => 0, 'question_type' => 'plate_to_province', 'buzz_open' => 0, 'answer_revealed' => 0, 'bonus_active' => 0]); break;
            default: throw new RuntimeException('Lệnh không hợp lệ.');
        }
        $this->touch();
        $this->db->transComplete();
    }

    private function touch(): void
    {
        $this->db->table('game_state')->where('id', 1)->set('version', 'version + 1', false)->set('updated_at', date('Y-m-d H:i:s'))->update();
    }

    private function chooseQuestionType(array $question): string
    {
        // Trọng số trên thang 200: 35%, 35%, 12%, 8%, 5%, 5%.
        $weighted = [
            'plate_to_province' => 70,
            'province_to_plate' => 70,
            'places' => 24,
            'specialty' => 16,
            'airport' => 10,
            'unesco' => 10,
        ];
        foreach (['places', 'specialty', 'airport', 'unesco'] as $type) {
            if (trim((string) ($question[$type] ?? '')) === '') unset($weighted[$type]);
        }
        $roll = random_int(1, array_sum($weighted));
        foreach ($weighted as $type => $weight) {
            $roll -= $weight;
            if ($roll <= 0) return $type;
        }
        return 'plate_to_province';
    }

    private function questionPresentation(array $state): array
    {
        if (empty($state['question_id'])) return ['question_prompt' => 'Chờ MC bắt đầu game', 'question_display' => '--', 'question_answer' => ''];
        $province = (string) ($state['province'] ?? '');
        $plate = (string) ($state['plate_code'] ?? '');
        return match ((string) ($state['question_type'] ?? 'plate_to_province')) {
            'province_to_plate' => ['question_prompt' => 'Tỉnh/thành này có biển số xe nào?', 'question_display' => $province, 'question_answer' => $plate],
            'places' => ['question_prompt' => "Kể tên một địa điểm nổi tiếng của {$province}", 'question_display' => $province, 'question_answer' => (string) ($state['places'] ?? '')],
            'specialty' => ['question_prompt' => "Đặc sản nổi tiếng của {$province} là gì?", 'question_display' => $province, 'question_answer' => (string) ($state['specialty'] ?? '')],
            'airport' => ['question_prompt' => "{$province} có sân bay nào?", 'question_display' => $province, 'question_answer' => (string) ($state['airport'] ?? '')],
            'unesco' => ['question_prompt' => "{$province} có di sản UNESCO nào?", 'question_display' => $province, 'question_answer' => (string) ($state['unesco'] ?? '')],
            default => ['question_prompt' => 'Biển số xe này thuộc tỉnh/thành nào?', 'question_display' => $plate, 'question_answer' => $province],
        };
    }
}
