<?php
$loader->add('Aura\Http\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

/**
 * Constructor params.
 */
$di->config['Aura\Http\Adapter\Curl'] = [
    'stack_builder' => $di->lazyNew('Aura\Http\Message\Response\StackBuilder'),
];

$di->config['Aura\Http\Adapter\Stream'] = [
    'stack_builder'      => $di->lazyNew('Aura\Http\Message\Response\StackBuilder'),
    'form_data'          => $di->lazyNew('Aura\Http\Multipart\FormData'),
    'cookie_jar_factory' => $di->lazyNew('Aura\Http\Cookie\JarFactory'),
];

$di->config['Aura\Http\Manager'] = [
    'message_factory' => $di->lazyNew('Aura\Http\Message\Factory',
    'transport'       => $di->lazyNew('Aura\Http\Transport'),
];

$di->config['Aura\Http\Message\Response\StackBuilder'] = [
    'message_factory' => $di->lazyNew('Aura\Http\Message\Factory'),
];

$di->config['Aura\Http\Multipart\FormData'] = [
    'part_factory' => $di->lazyNew('Aura\Http\Multipart\PartFactory'),
];

$di->config['Aura\Http\Transport'] = [
    'phpfunc' => $di->lazyNew('Aura\Http\PhpFunc'),
    'options' => $di->lazyNew('Aura\Http\Transport\Options'),
    'adapter' => extension_loaded('curl')
               ? $di->lazyNew('Aura\Http\Adapter\Curl')
               : $di->lazyNew('Aura\Http\Adapter\Stream'),
];

/**
 * Dependency services.
 */
$di->service('http_manager', function () use ($di) {
    return $di->newInstance('Aura\Http\Manager');
});
