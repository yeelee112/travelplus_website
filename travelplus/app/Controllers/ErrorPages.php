<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class ErrorPages extends Controller
{
    public function forbidden(): ResponseInterface
    {
        return $this->render(403, 'error_403');
    }

    public function notFound(): ResponseInterface
    {
        return $this->render(404, 'error_404');
    }

    private function render(int $statusCode, string $view): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setBody(view('errors/html/' . $view));
    }
}
