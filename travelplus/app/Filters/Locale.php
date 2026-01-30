<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Locale implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $locale = service('uri')->getSegment(1);

        if (! in_array($locale, ['vi', 'en'])) {
            $locale = 'vi';
        }

        service('request')->setLocale($locale);
        dd(__FILE__);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }

}