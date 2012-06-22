<?php
namespace Aura\Http\Message;

use Aura\Http\Content\Factory as ContentFactory;
use Aura\Http\Content\Multipart;
use Aura\Http\Content\SinglePart;
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
        list($headers, $cookies) = $this->newHeadersCookies();
        $content = $this->newSinglePart();
        return new Message($headers, $cookies, $content);
    }
    
    public function newRequest()
    {
        list($headers, $cookies) = $this->newHeadersCookies();
        $content = $this->newSinglePart();
        return new Request($headers, $cookies, $content);
    }
    
    public function newRequestMultipart()
    {
        list($headers, $cookies) = $this->newHeadersCookies();
        $content = $this->newMultiPart();
        return new Request($headers, $cookies, $content);
    }
    
    public function newResponse()
    {
        list($headers, $cookies) = $this->newHeadersCookies();
        $content = $this->newSinglePart();
        return new Response($headers, $cookies, $content);
    }
    
    protected function newHeadersCookies()
    {
        return [
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory),
        ];
    }
    
    protected function newSinglePart()
    {
        return new SinglePart(new Headers(new HeaderFactory));
    }
    
    protected function newMultiPart()
    {
        return new MultiPart(
            new Headers(new HeaderFactory),
            new ContentFactory(new Headers(new HeaderFactory))
        );
    }
}
