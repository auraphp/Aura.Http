<?php

/**
 * Constructor params.
 */
$di->params['aura\http\Response'] = array(
    'mime_utility' => $di->lazyGet('mime_utility'),
);

/**
 * Dependency services.
 */
$di->set('http_response', function() use ($di) {
    return $di->newInstance('aura\http\Response');
});

$di->set('mime_utility', function() use ($di) {
    return $di->newInstance('aura\http\MimeUtility');
});