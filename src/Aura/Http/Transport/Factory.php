<?php
namespace Aura\Http\Transport;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Request\Response as RequestResponse;
use Aura\Http\Request\ResponseBuilder;
use Aura\Http\Request\ResponseStackFactory;
use Aura\Http\Transport\Multipart;
use Aura\Http\PhpFunc;

class Factory
{
    public function newInstance($type = null)
    {
        if (! $type) {
            if (extension_loaded('curl')) {
                $type = 'curl';
            } else {
                $type = 'stream';
            }
        }
        
        $response_builder = new ResponseBuilder(
            new RequestResponse(
                new Headers(new HeaderFactory),
                new Cookies(new CookieFactory)
            ),
            new ResponseStackFactory
        );

        if ($type == 'curl') {
            $adapter = new Curl($response_builder);
        } else {
            $adapter = new Stream(
                $response_builder, 
                new Multipart, 
                new CookieJar(new CookieFactory)
            );
        }
        
        return new Transport(new PhpFunc, $adapter);
    }
}