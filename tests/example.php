<?php
namespace Aura\Http;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
$curl = new Manager(
    new Message\Factory,
    new Transport(
        new PhpFunc,
        new Transport\Options,
        new Adapter\Curl(
            new Message\Response\StackBuilder(new Message\Factory)
        )
    )
);

$stream = new Manager(
    new Message\Factory,
    new Transport(
        new PhpFunc,
        new Transport\Options,
        new Adapter\Stream(
            new Message\Response\StackBuilder(new Message\Factory),
            new Multipart\FormData(new Multipart\PartFactory),
            new Cookie\JarFactory
        )
    )
);

$request = $curl->newRequest();
$request->setUrl('http://example.com');
$stack = $curl->send($request);
echo $stack[0]->content;

echo PHP_EOL . PHP_EOL;

$request = $stream->newRequest();
$request->setUrl('http://example.com');
$stack = $stream->send($request);
echo $stack[0]->content;
