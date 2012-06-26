<?php
namespace Aura\Http\Message;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Message;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

class Factory
{
    public function newMessage()
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        return new Message($headers, $cookies);
    }
    
    public function newRequest()
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        return new Request($headers, $cookies);
    }
    
    public function newResponse()
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        return new Response($headers, $cookies);
    }
}
