<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Manager;

use Aura\Http\Adapter\Curl as AdapterCurl;
use Aura\Http\Adapter\Stream as AdapterStream;
use Aura\Http\Cookie\JarFactory as CookieJarFactory;
use Aura\Http\Exception;
use Aura\Http\Manager;
use Aura\Http\Message\Factory as MessageFactory;
use Aura\Http\Message\Response\StackBuilder;
use Aura\Http\Multipart\FormData;
use Aura\Http\Multipart\PartFactory;
use Aura\Http\PhpFunc;
use Aura\Http\Transport;
use Aura\Http\Transport\Options as TransportOptions;

/**
 * 
 * Factory class to create instance of manager easily
 * 
 * @package Aura.Http
 * 
 */
class Factory
{
    /**
     * 
     * Creates a new manager instance.
     * 
     * @param string $type The adapter type to use: `'curl'` or `'stream'`.
     * 
     * @return Manager
     * 
     */
    public function newInstance($type)
    {
        if ($type == 'curl') {
            $adapter = new AdapterCurl(
                new StackBuilder(new MessageFactory)
            );
        } elseif ($type == 'stream') {
            $adapter = new AdapterStream(
                new StackBuilder(new MessageFactory),
                new FormData(new PartFactory),
                new CookieJarFactory
            );
        } else {
            throw new Exception\UnknownAdapterType;
        }

        return new Manager(
            new MessageFactory,
            new Transport(
                new PhpFunc,
                new TransportOptions,
                $adapter
            )
        );
    }
}
 