<?php
namespace Aura\Http\Message;

use Aura\Http\Content;
use Aura\Http\Content\Multipart;
use Aura\Http\Content\PartFactory;
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
        $content = new Content;
        return new Message($headers, $cookies, $content);
    }
    
    public function newRequest()
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        $content = new Content;
        return new Request($headers, $cookies, $content);
    }
    
    public function newRequestMultipart()
    {
        // basic components
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        $content = new MultiPart(new PartFactory);
        
        // preset the content-type on the headers using the boundary value
        $boundary = $content->getBoundary();
        $headers->set(
            'Content-Type',
            'multipart/form-data; boundary="{$boundary}"'
        );
        
        // now create the request object
        return new Request($headers, $cookies, $content);
    }
    
    public function newResponse()
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        $content = new Content;
        return new Response($headers, $cookies, $content);
    }
}
