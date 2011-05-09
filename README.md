<?php
$di->params['aura\http\Response']['mime_utility'] = $di->lazyGet('http_mime_utility');

$di->set('http_mime_utility', function() use ($di) {
    return $di->newInstance('aura\http\MimeUtility');
});
