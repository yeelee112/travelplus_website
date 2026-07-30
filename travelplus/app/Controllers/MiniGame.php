<?php

namespace App\Controllers;

use App\Services\MiniGameService;
use CodeIgniter\Controller;
use RuntimeException;
use Throwable;

class MiniGame extends Controller
{
    public function admin() { return view('mini-game/admin', ['csrf' => csrf_hash()]); }
    public function player() { return view('mini-game/player', ['csrf' => csrf_hash()]); }
    public function screen() { return view('mini-game/screen'); }

    public function state()
    {
        try {
            return $this->response->setJSON((new MiniGameService())->state((string) $this->request->getGet('token')));
        } catch (Throwable $e) { return $this->fail($e); }
    }

    public function join()
    {
        try {
            $result = (new MiniGameService())->join((string) $this->request->getPost('name'), (string) $this->request->getPost('office'));
            return $this->response->setJSON(['ok' => true] + $result + ['csrf' => csrf_hash()]);
        } catch (Throwable $e) { return $this->fail($e); }
    }

    public function buzz()
    {
        try {
            (new MiniGameService())->buzz((string) $this->request->getPost('token'));
            return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
        } catch (Throwable $e) { return $this->fail($e); }
    }

    public function command()
    {
        try {
            (new MiniGameService())->command((string) $this->request->getPost('action'), $this->request->getPost());
            return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
        } catch (Throwable $e) { return $this->fail($e); }
    }

    public function questions()
    {
        $db = db_connect();
        if ($this->request->getMethod() === 'POST') {
            $id = (int) $this->request->getPost('id');
            $data = [
                'plate_code' => trim((string) $this->request->getPost('plate_code')),
                'province' => trim((string) $this->request->getPost('province')),
                'places' => trim((string) $this->request->getPost('places')) ?: null,
                'specialty' => trim((string) $this->request->getPost('specialty')) ?: null,
                'airport' => trim((string) $this->request->getPost('airport')) ?: null,
                'unesco' => trim((string) $this->request->getPost('unesco')) ?: null,
                'sort_order' => (int) $this->request->getPost('sort_order'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($data['plate_code'] === '' || $data['province'] === '') return redirect()->back()->with('error', 'Biển số và tỉnh/thành là bắt buộc.');
            if ($id) $db->table('game_questions')->where('id', $id)->update($data);
            else $db->table('game_questions')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
            return redirect()->to(site_url('mini-game/questions'));
        }
        return view('mini-game/questions', ['questions' => $db->table('game_questions')->orderBy('sort_order')->orderBy('id')->get()->getResultArray()]);
    }

    public function deleteQuestion($id) { db_connect()->table('game_questions')->where('id', (int) $id)->delete(); return redirect()->to(site_url('mini-game/questions')); }

    public function exportQuestions()
    {
        $rows = db_connect()->table('game_questions')->orderBy('sort_order')->get()->getResultArray();
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Biển số', 'Tỉnh/Thành', 'Địa điểm', 'Đặc sản', 'Sân bay', 'UNESCO', 'Thứ tự']);
        foreach ($rows as $r) fputcsv($stream, [$r['plate_code'],$r['province'],$r['places'],$r['specialty'],$r['airport'],$r['unesco'],$r['sort_order']]);
        rewind($stream); $csv = stream_get_contents($stream); fclose($stream);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8')->setHeader('Content-Disposition', 'attachment; filename="mini-game-questions.csv"')->setBody($csv);
    }

    private function fail(Throwable $e)
    {
        log_message('error', 'Mini game: {message}', ['message' => $e->getMessage()]);
        $code = $e instanceof RuntimeException ? 422 : 500;
        return $this->response->setStatusCode($code)->setJSON(['ok' => false, 'message' => $e->getMessage(), 'csrf' => csrf_hash()]);
    }
}
