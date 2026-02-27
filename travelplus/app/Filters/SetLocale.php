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