<?php
namespace Aura\Http;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';

if (extension_loaded('curl')) {
    // use curl adapter for transport
    return new Manager(
        new Message\Factory,
        new Transport(
            new PhpFunc,
            new Transport\Options,
            new Adapter\Curl(
                new Response\StackBuilder(
                    new Message\Factory
                )
            )
        )
    );
} else {
    // use stream adapter for transport
    return new Manager(
        new Message\Factory,
        new Transport(
            new PhpFunc,
            new Transport\Options,
            new Adapter\Stream(
                new Response\StackBuilder(
                    new Message\Factory
                )
            )
        )
    );
}
