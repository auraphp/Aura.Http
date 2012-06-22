<?php
namespace Aura\Http\Message;

use Aura\Http\Content\SinglePart;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\MessageTest;

class ResponseTest extends MessageTest
{
    protected function setUp()
    {
        $this->message = new Response(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory),
            new SinglePart(new Headers(new HeaderFactory))
        );
    }
    
    public function testSetAndGetStatusCode()
    {
        $this->assertSame(200, $this->message->getStatusCode());
        
        $this->message->setStatusCode(101);
        $this->assertSame(101, $this->message->getStatusCode());
        $this->assertSame(101, $this->message->status_code);
    }
    
    public function testSetStatusCodeNoDefaultText()
    {
        $this->message->setStatusCode(569);
        $this->assertSame(569, $this->message->getStatusCode());
        $this->assertSame(569, $this->message->status_code);
        
        $this->assertSame('', $this->message->getStatusText());
        $this->assertSame('', $this->message->status_text);
    }
    
    public function testSetStatusCodeExceptionLessThan100()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $this->message->setStatusCode(99);
    }

    public function testSetStatusCodeExceptionGreaterThan599()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $this->message->setStatusCode(600);
    }

    public function testSetAndGetStatusText()
    {
        $this->assertSame('OK', $this->message->getStatusText());
        
        $this->message->setStatusText("I'm a teapot");
        $this->assertSame("I'm a teapot", $this->message->getStatusText());
        $this->assertSame("I'm a teapot", $this->message->status_text);
    }
}
