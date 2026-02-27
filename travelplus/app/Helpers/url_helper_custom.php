<?php

function localized_url($path = '')
{
    $locale = service('request')->getLocale();

    if ($locale === 'en') {
        return base_url('en/' . ltrim($path, '/'));
    }

    return base_url(ltrim($path, '/'));
}