<?php
namespace Aura\Http;

use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Header\Collection as Headers;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    protected $message;
    
    protected function setUp()
    {
        $this->message = new Message(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
    }
    
    public function testSetAndGetCookies()
    {
        $cookies = new Cookies(new CookieFactory);
        $this->message->setCookies($cookies);
        
        $this->assertSame($this->message->getCookies(), $cookies);
        $this->assertSame($this->message->cookies, $cookies);
    }
    
    public function testSetAndGetContent()
    {
        $content = 'Hello World!';
        $this->message->setContent($content);
        $this->assertSame($this->message->getContent(), $content);
        $this->assertSame($this->message->content, $content);
    }
    
    public function testSetAndGetHeaders()
    {
        $headers = new Headers(new HeaderFactory);
        $this->message->setHeaders($headers);
        
        $this->assertSame($this->message->getHeaders(), $headers);
        $this->assertSame($this->message->headers, $headers);
    }
    
    public function testSetAndGetVersion()
    {
        $version = '1.0';
        $this->message->setVersion($version);
        
        $this->assertSame($this->message->getVersion(), $version);
        $this->assertSame($this->message->version, $version);
    }
}