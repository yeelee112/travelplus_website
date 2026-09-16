<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class SetLocale implements FilterInterface
{
    protected array $supported = ['en', 'vi'];

    public function before(RequestInterface $request, $arguments = null)
    {
        $segment = $request->getUri()->getSegment(1);

        if ($segment === '' && strtolower(trim((string) $request->getCookie('travelplus_locale'))) === 'en') {
            return redirect()->to(base_url('en'))->setStatusCode(302);
        }

        if ($segment === 'en') {
            $request->setLocale('en');
        } else {
            $request->setLocale('vi');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
