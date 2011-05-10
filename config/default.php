<?php
/**
 * Constructor params.
 */
$di->params['aura\http\Response'] = array(
    'headers' => $di->lazyNew('aura\http\Headers'),
    'cookies' => $di->lazyNew('aura\http\Cookies'),
);

/**
 * Dependency services.
 */
$di->set('http_response', function() use ($di) {
    return $di->newInstance('aura\http\Response');
});
