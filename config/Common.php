<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.Http
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Http\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

/**
 *
 * Configuration file for Aura.Di
 *
 * @package Aura.Http
 *
 */
class Common extends Config
{
    public function define(Container $di)
    {
        /**
         * Services
         */
        $di->set('aura/http:transport', $di->lazyNew('Aura\Http\Transport'));
        $di->set('aura/http:http', $di->lazyNew('Aura\Http\Http'));

        /**
         * Aura\Http\Adapter\CurlAdapter
         */
        $di->params['Aura\Http\Adapter\CurlAdapter'] = [
            'stack_builder' => $di->lazyNew('Aura\Http\Message\ResponseStackBuilder'),
        ];

        /**
         * Aura\Http\Adapter\StreamAdapter
         */
        $di->params['Aura\Http\Adapter\StreamAdapter'] = [
            'stack_builder'      => $di->lazyNew('Aura\Http\Message\ResponseStackBuilder'),
            'form_data'          => $di->lazyNew('Aura\Http\Multipart\FormData'),
            'cookie_jar_factory' => $di->lazyNew('Aura\Http\Cookie\CookieJarFactory'),
        ];

        /**
         * Aura\Http\Cookie\CookieCollection
         */
        $di->params['Aura\Http\Cookie\CookieCollection'] = [
            'factory' => $di->lazyNew('Aura\Http\Cookie\CookieFactory'),
        ];

        /**
         * Aura\Http\Header\HeaderCollectionCollection
         */
        $di->params['Aura\Http\Header\HeaderCollection'] = [
            'factory' => $di->lazyNew('Aura\Http\Header\HeaderFactory'),
        ];

        /**
         * Aura\Http\Http
         */
        $di->params['Aura\Http\Http'] = [
            'message_factory' => $di->lazyNew('Aura\Http\Message\MessageFactory'),
            'transport'       => $di->lazyGet('aura/http:transport'),
        ];

        /**
         * Aura\Http\Message
         */
        $di->params['Aura\Http\Message'] = [
            'headers' => $di->lazyNew('Aura\Http\Header\HeaderCollection'),
            'cookies' => $di->lazyNew('Aura\Http\Cookie\CookieCollection'),
        ];

        /**
         * Aura\Http\Message\ResponseStackBuilder
         */
        $di->params['Aura\Http\Message\ResponseStackBuilder'] = [
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
            'options' => $di->lazyNew('Aura\Http\Transport\TransportOptions'),
            'adapter' => extension_loaded('curl')
                       ? $di->lazyNew('Aura\Http\Adapter\CurlAdapter')
                       : $di->lazyNew('Aura\Http\Adapter\StreamAdapter'),
        ];
    }

    public function modify(Container $di)
    {
    }
}
