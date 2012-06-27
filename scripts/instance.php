<?php
namespace Aura\Http;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
return new Manager(
    new Message\Factory,
    new Transport(
        new PhpFunc,
        new Transport\Options,
        // if curl is loaded, use the curl adapter,
        // otherwise use the stream adapter
        extension_loaded('curl')
            ?   new Adapter\Curl(
                    new Message\Response\StackBuilder(new Message\Factory)
                )
            :   new Adapter\Stream(
                    new Message\Response\StackBuilder(new Message\Factory),
                    new Multipart\FormData(new Multipart\PartFactory),
                    new Cookie\JarFactory
                )
    )
);
