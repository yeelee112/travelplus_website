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

        if (in_array($segment, $this->supported, true)) {
            $request->setLocale($segment);
            helper('cookie');
            set_cookie('site_lang', $segment, 60 * 60 * 24 * 30);
        } else {
            helper('cookie');
            $cookie = get_cookie('site_lang');
            if ($cookie && in_array($cookie, $this->supported, true)) {
                $request->setLocale($cookie);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing to do after
    }
}
