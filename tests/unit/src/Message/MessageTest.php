<?php
namespace Aura\Http\Message;

use Aura\Http\Cookie\CookieFactory;
use Aura\Http\Cookie\CookieCollection;
use Aura\Http\Header\HeaderFactory;
use Aura\Http\Header\HeaderCollection;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    protected $message;

    protected function setUp()
    {
        $this->message = new Message(
            new HeaderCollection(new HeaderFactory),
            new CookieCollection(new CookieFactory)
        );
    }

    public function testSetAndGetCookieCollection()
    {
        $cookies = new CookieCollection(new CookieFactory);
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
        $headers = new HeaderCollection(new HeaderFactory);
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