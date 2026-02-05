<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

class Destination extends Controller
{
    public function search()
    {
        $keyword = strtolower($this->request->getGet('q'));

        if (!$keyword || strlen($keyword) < 2) {
            return $this->response->setJSON([]);
        }

        $data = include APPPATH . 'Views/data/destinations.php';

        $results = array_filter($data, function ($item) use ($keyword) {
            $text = $item['type'] === 'country'
                ? $item['name']
                : ($item['name'] . ' ' . $item['country']);

            return str_contains(strtolower($text), $keyword);
        });

        // giới hạn kết quả
        $results = array_slice(array_values($results), 0, 10);

        return $this->response->setJSON($results);
    }
}
