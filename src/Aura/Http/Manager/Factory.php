<?php
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

class Factory
{
    public function newInstance($type = null)
    {
        if ($type == null) {
            if (extension_loaded('curl')) {
                $type = 'curl';
            } else {
                $type = 'stream';
            }
        }
        
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
