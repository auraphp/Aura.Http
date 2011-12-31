<?php
/**
 * Package prefix for autoloader.
 */
$loader->add('Aura\Http\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Constructor params.
 */
$di->params['Aura\Http\Response'] = [
    'headers' => $di->lazyNew('Aura\Http\Headers'),
    'cookies' => $di->lazyNew('Aura\Http\Cookies'),
];

/**
 * Dependency services.
 */
$di->set('http_response', function() use ($di) {
    return $di->newInstance('Aura\Http\Response');
});
