<?php

$loader->add('Aura\Http\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Constructor params.
 */
$di->params['Aura\Http\Cookie\Collection'] = [
    'factory' => $di->lazyNew('Aura\Http\Cookie\Factory'),
];
$di->params['Aura\Http\Header\Collection'] = [
    'factory' => $di->lazyNew('Aura\Http\Header\Factory'),

];
$di->params['Aura\Http\Response'] = [
    'headers' => $di->lazyNew('Aura\Http\Header\Collection'),
    'cookies' => $di->lazyNew('Aura\Http\Cookie\Collection'),
];
$di->params['Aura\Http\Request\Response'] = [
    'headers' => $di->lazyNew('Aura\Http\Header\Collection'),
    'cookies' => $di->lazyNew('Aura\Http\Cookie\Collection'),
];
$di->params['Aura\Http\Request\ResponseBuilder'] = [
    'response' => $di->lazyNew('Aura\Http\RequestResponse'),
    'factory'  => $di->lazyNew('Aura\Http\Factory\ResponseStack'),
];
$di->params['Aura\Http\Request\Adapter\Curl'] = [
    'builder' => $di->lazyNew('Aura\Http\Request\ResponseBuilder'),
    'options' => [],
];
$di->params['Aura\Http\Request\Adapter\Stream'] = [
    'builder'   => $di->lazyNew('Aura\Http\Request\ResponseBuilder'),
    'multipart' => $di->lazyNew('Aura\Http\Request\Multipart'),
];

$adapter = extension_loaded('curl') ? 'Aura\Http\Request\Adapter\Curl' : 'Aura\Http\Request\Adapter\Stream';

$di->params['Aura\Http\Request'] = [
    'adapter' => $di->lazyNew($adapter),
    'headers' => $di->lazyNew('Aura\Http\Header\Collection'),
    'cookies' => $di->lazyNew('Aura\Http\Cookie\Collection'),
    'options' => [],
];


/**
 * Dependency services.
 */
$di->set('http_response', function() use ($di) {
    return $di->newInstance('Aura\Http\Response');
});

$di->set('http_request', function() use ($di) {
    return $di->newInstance('Aura\Http\Request');
});