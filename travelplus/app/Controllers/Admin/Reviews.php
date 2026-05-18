<?php

namespace App\Controllers\Admin;

class Reviews extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return redirect()->to(site_url('admin'))->with('error', 'Bảng tour_reviews chưa tồn tại.');
        }

        $status = trim((string) $this->request->getGet('status'));
        $keyword = trim((string) $this->request->getGet('q'));

        $builder = $db->table('tour_reviews tr')
            ->select('
                tr.id,
                tr.tour_id,
                tr.reviewer_name,
                tr.reviewer_email,
                tr.rating_overall,
                tr.rating_destination,
                tr.rating_transport,
                tr.rating_value,
                tr.title,
                tr.content,
                tr.status,
                tr.created_at,
                COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", tr.tour_id)) AS tour_name
            ', false)
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = tr.tour_id AND tt_vi.locale = "vi"', 'left')
            ->join('tour_translations tt_en', 'tt_en.tour_id = tr.tour_id AND tt_en.locale = "en"', 'left');

        if ($status !== '' && in_array($status, ['pending', 'approved', 'hidden'], true)) {
            $builder->where('tr.status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('tr.reviewer_name', $keyword)
                ->orLike('tr.reviewer_email', $keyword)
                ->orLike('tr.content', $keyword)
                ->orLike('tt_vi.name', $keyword)
                ->orLike('tt_en.name', $keyword)
                ->groupEnd();
        }

        $reviews = $builder
            ->orderBy('tr.created_at', 'DESC')
            ->orderBy('tr.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/reviews/index', [
            'reviews' => $reviews,
            'status' => $status,
            'keyword' => $keyword,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function show(int $reviewId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Bảng tour_reviews chưa tồn tại.');
        }

        $review = $db->table('tour_reviews tr')
            ->select('
                tr.*,
                COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", tr.tour_id)) AS tour_name
            ', false)
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = tr.tour_id AND tt_vi.locale = "vi"', 'left')
            ->join('tour_translations tt_en', 'tt_en.tour_id = tr.tour_id AND tt_en.locale = "en"', 'left')
            ->where('tr.id', $reviewId)
            ->get()
            ->getRowArray();

        if (! is_array($review)) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Không tìm thấy review.');
        }

        return view('admin/reviews/show', [
            'review' => $review,
            'statusLogs' => $this->getStatusLogs($reviewId),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function updateStatus(int $reviewId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Bảng tour_reviews chưa tồn tại.');
        }

        $review = $db->table('tour_reviews')->where('id', $reviewId)->get()->getRowArray();
        if (! is_array($review)) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Không tìm thấy review.');
        }

        $status = trim((string) $this->request->getPost('status'));
        $note = trim((string) $this->request->getPost('status_note'));
        $redirectTo = trim((string) $this->request->getPost('redirect_to'));

        if (! in_array($status, ['pending', 'approved', 'hidden'], true)) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Trạng thái review không hợp lệ.');
        }

        $previousStatus = (string) ($review['status'] ?? '');

        $db->table('tour_reviews')
            ->where('id', $reviewId)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($previousStatus !== $status) {
            $this->logStatusChange($reviewId, $previousStatus, $status, $note);
        }

        $target = $redirectTo === 'show' ? site_url('admin/reviews/' . $reviewId) : site_url('admin/reviews');

        return redirect()->to($target)->with('success', 'Đã cập nhật review #' . $reviewId . '.');
    }

    public function delete(int $reviewId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();

        if (! $db->tableExists('tour_reviews')) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Bảng tour_reviews chưa tồn tại.');
        }

        $review = $db->table('tour_reviews')->where('id', $reviewId)->get()->getRowArray();
        if (! is_array($review)) {
            return redirect()->to(site_url('admin/reviews'))->with('error', 'Không tìm thấy review.');
        }

        $db->transStart();
        if ($db->tableExists('review_status_logs')) {
            $db->table('review_status_logs')->where('review_id', $reviewId)->delete();
        }
        $db->table('tour_reviews')->where('id', $reviewId)->delete();
        $db->transComplete();

        return redirect()->to(site_url('admin/reviews'))->with('success', 'Đã xóa review #' . $reviewId . '.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getStatusLogs(int $reviewId): array
    {
        $db = db_connect();

        if (! $db->tableExists('review_status_logs')) {
            return [];
        }

        return $db->table('review_status_logs')
            ->where('review_id', $reviewId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function logStatusChange(int $reviewId, string $fromStatus, string $toStatus, string $note = ''): void
    {
        $db = db_connect();

        if (! $db->tableExists('review_status_logs')) {
            return;
        }

        $authUser = session()->get('auth_user');

        $db->table('review_status_logs')->insert([
            'review_id' => $reviewId,
            'from_status' => $fromStatus !== '' ? $fromStatus : null,
            'to_status' => $toStatus,
            'actor_user_id' => is_array($authUser) ? ((int) ($authUser['id'] ?? 0) ?: null) : null,
            'actor_name' => is_array($authUser) ? ((string) ($authUser['full_name'] ?? '') ?: null) : null,
            'actor_email' => is_array($authUser) ? ((string) ($authUser['email'] ?? '') ?: null) : null,
            'note' => $note !== '' ? $note : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
