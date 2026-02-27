
?<?php
function switch_lang_url($lang)
{
    $currentUrl = current_url(true);
    $segments = $currentUrl->getSegments();

    if (isset($segments[0]) && $segments[0] === 'en') {
        array_shift($segments);
    }

    if ($lang === 'en') {
        return base_url('en/' . implode('/', $segments));
    }

    return base_url(implode('/', $segments));
}
?>