<?php
namespace Aura\Http;

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

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';

if (extension_loaded('curl')) {
    return new Manager(
        new MessageFactory,
        new Transport(
            new PhpFunc,
            new TransportOptions,
            new AdapterCurl(
                new StackBuilder(new MessageFactory)
            )
        )
    );
} else {
    return new Manager(
        new MessageFactory,
        new Transport(
            new PhpFunc,
            new TransportOptions,
            new AdapterStream(
                new StackBuilder(new MessageFactory),
                new FormData(new PartFactory),
                new CookieJarFactory
            )
        )
    );
}
