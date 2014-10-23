<?php
/**
 * Loader
 */
$loader->add('Aura\Http\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Services
 */
$di->set('http_transport', $di->lazyNew('Aura\Http\Transport'));
$di->set('http_manager', $di->lazyNew('Aura\Http\Manager'));

/**
 * Aura\Http\Adapter\CurlAdapter
 */
$di->params['Aura\Http\Adapter\CurlAdapter'] = [
    'stack_builder' => $di->lazyNew('Aura\Http\Message\Response\StackBuilder'),
];

/**
 * Aura\Http\Adapter\StreamAdapter
 */
$di->params['Aura\Http\Adapter\StreamAdapter'] = [
    'stack_builder'      => $di->lazyNew('Aura\Http\Message\Response\StackBuilder'),
    'form_data'          => $di->lazyNew('Aura\Http\Multipart\FormData'),
    'cookie_jar_factory' => $di->lazyNew('Aura\Http\Cookie\JarFactory'),
];

/**
 * Aura\Http\Cookie\CookieCollection
 */
$di->params['Aura\Http\Cookie\CookieCollection'] = [
    'factory' => $di->lazyNew('Aura\Http\Cookie\Factory'),
];

/**
 * Aura\Http\Header\Collection
 */
$di->params['Aura\Http\Header\Collection'] = [
    'factory' => $di->lazyNew('Aura\Http\Header\HeaderFactory'),
];

/**
 * Aura\Http\Manager
 */
$di->params['Aura\Http\Manager'] = [
    'message_factory' => $di->lazyNew('Aura\Http\Message\MessageFactory'),
    'transport'       => $di->lazyGet('http_transport'),
];

/**
 * Aura\Http\Message
 */
$di->params['Aura\Http\Message'] = [
    'headers' => $di->lazyNew('Aura\Http\Header\Collection'),
    'cookies' => $di->lazyNew('Aura\Http\Cookie\CookieCollection'),
];

/**
 * Aura\Http\Message\Response\StackBuilder
 */
$di->params['Aura\Http\Message\Response\StackBuilder'] = [
    'message_factory' => $di->lazyNew('Aura\Http\Message\MessageFactory'),
];

/**
 * Aura\Http\Multipart\FormData
 */
$di->params['Aura\Http\Multipart\FormData'] = [
    'part_factory' => $di->lazyNew('Aura\Http\Multipart\PartFactory'),
];

/**
 * Aura\Http\Transport
 */
$di->params['Aura\Http\Transport'] = [
    'phpfunc' => $di->lazyNew('Aura\Http\PhpFunc'),
    'options' => $di->lazyNew('Aura\Http\Transport\Options'),
    'adapter' => extension_loaded('curl')
               ? $di->lazyNew('Aura\Http\Adapter\CurlAdapter')
               : $di->lazyNew('Aura\Http\Adapter\StreamAdapter'),
];
