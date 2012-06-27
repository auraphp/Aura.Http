<?php
namespace Aura\Http;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
if (extension_loaded('curl')) {
    return new Manager(
        new Message\Factory,
        new Transport(
            new PhpFunc,
            new Transport\Options,
            // use curl adapter for transport
            new Adapter\Curl(
                new Message\Response\StackBuilder(new Message\Factory)
            )
        )
    );
} else {
    return new Manager(
        new Message\Factory,
        new Transport(
            new PhpFunc,
            new Transport\Options,
            // use stream adapter for transport
            new Adapter\Stream(
                new Message\Response\StackBuilder(new Message\Factory),
                new Multipart\FormData(new Multipart\PartFactory),
                new Cookie\JarFactory,
            )
        )
    );
}
