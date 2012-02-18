<?php

namespace Aura\Http\Request;

use Aura\Http as Http;

use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Factory\Cookie as CookieFactory;

class RequestResponseTest extends \PHPUnit_Framework_TestCase
{
    protected function newRequestResponse()
    {
        return new Response(
            new Http\Headers(new HeaderFactory),
            new Http\Cookies(new CookieFactory)
        );
    }

    public function test__clone()
    {
        $rr = $this->newRequestResponse();
        
        $rr->setContent('Hi');
        $rr->headers->set('foo', 'bar');
        $rr->cookies->set('name');
        $rr->setStatusCode(222);
        $rr->setStatusText('I\'m a teapot');
        $rr->setVersion('1.0');
        
        $clone = clone $rr;
        
        $this->assertEmpty($clone->getContent());
        $this->assertEmpty($clone->headers->getAll());
        $this->assertEmpty($clone->cookies->getAll());
        $this->assertEquals(200, $clone->getStatusCode());
        $this->assertEmpty($clone->getStatusText());
        $this->assertEquals('1.1', $clone->getVersion());
    }
    
    public function test__get()
    {
        $rr = $this->newRequestResponse();

        $this->assertInstanceOf('\Aura\Http\Headers', $rr->headers);
        $this->assertInstanceOf('\Aura\Http\Cookies', $rr->cookies);

        $this->setExpectedException('\Aura\Http\Exception');
        $rr->invalid;
    }
    
    public function testSetSavedContent()
    {
        $file = __DIR__ . '/_files/gziphttp';
        $rr   = $this->newRequestResponse();
        
        $rr->setContent($file, false, true);
        $actual = $rr->getContent();
        
        $this->assertTrue(is_resource($actual));
    }

    public function testSetContent()
    {
        $rr = $this->newRequestResponse();
        
        $rr->setContent('Hello world!!');
        $actual = $rr->getContent();
        
        $this->assertSame('Hello world!!', $actual);
    }

    public function testGzipedSetContent()
    {
        $rr      = $this->newRequestResponse();
        $content = file_get_contents(__DIR__ . '/_files/gziphttp');
        
        $rr->headers->set('Content-Encoding', 'gzip');
        $rr->setContent($content);
        $actual = $rr->getContent();
        
        $this->assertSame('Hello gzip world', $actual);
    }

    public function testInvalidGzipedContent()
    {
        $rr      = $this->newRequestResponse();
        $content = file_get_contents(__DIR__ . '/_files/gziphttp');
        
        $rr->headers->set('Content-Encoding', 'gzip');
        $this->setExpectedException('\Aura\Http\Exception\UnableToDecompressContent');
        $rr->setContent('invalid' . $content);
        $rr->getContent();
    }

    public function testInvalidInflatedContent()
    {
        $rr      = $this->newRequestResponse();
        $content = file_get_contents(__DIR__ . '/_files/deflatehttp');
        
        $rr->headers->set('Content-Encoding', 'inflate');
        $this->setExpectedException('\Aura\Http\Exception\UnableToDecompressContent');
        $rr->setContent('invalid' . $content);
        $rr->getContent();
    }

    public function testInflatedSetContent()
    {
        $rr      = $this->newRequestResponse();
        $content = file_get_contents(__DIR__ . '/_files/deflatehttp');
        
        $rr->headers->set('Content-Encoding', 'inflate');
        $rr->setContent($content);
        $actual = $rr->getContent();
        
        $this->assertSame('Hello deflated world', $actual);
    }
    
    public function testSetCookies()
    {
        $rr      = $this->newRequestResponse();
        $cookies = new Http\Cookies(new CookieFactory);

        $rr->setCookies($cookies);
        $this->assertSame($cookies, $rr->getCookies());
    }
    
    public function testSetHeaders()
    {
        $rr      = $this->newRequestResponse();
        $headers = new Http\Headers(new HeaderFactory);

        $rr->SetHeaders($headers);
        $this->assertSame($headers, $rr->getHeaders());
    }
    
    public function testSetStatusCode()
    {
        $rr = $this->newRequestResponse();

        $rr->setStatusCode(200);
        $this->assertSame(200, $rr->getStatusCode());
        $this->assertSame('OK', $rr->getStatusText());

        $this->setExpectedException('\Aura\Http\Exception\UnknownStatus');
        $rr->setStatusCode(4200);
    }

    public function testSetStatusText()
    {
        $rr = $this->newRequestResponse();

        $rr->setStatusText("Hello\r\n World");
        $this->assertSame('Hello World', $rr->getStatusText());
    }

    public function testSetVersion()
    {
        $rr = $this->newRequestResponse();

        $rr->setVersion('1.0');
        $this->assertSame('1.0', $rr->getVersion());

        $this->setExpectedException('\Aura\Http\Exception\UnknownVersion');
        $rr->setVersion('2.2');
    }
}