<?php

namespace Aura\Http\Request;

use Aura\Http\MockHttp as MockHttp;
use Aura\Http\Headers as Headers;
use Aura\Http\Cookies as Cookies;
use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Factory\Cookie as CookieFactory;

class RequestResponseTest extends \PHPUnit_Framework_TestCase
{
    public static $callback;
    public static $headers_sent = false;
    protected $header_expect    = [];
    protected $cookie_expect    = [];
    
    protected $response;

    protected function setUp()
    {
        parent::setUp();
        static::$callback = $this;
        MockHttp::reset();
        // for setting cookies
        date_default_timezone_set('GMT');
    }
    
    protected function newResponse()
    {
        $this->header_expect    = [];
        $this->cookie_expect    = [];
        $this->header_expect[0] = ['HTTP/1.1 200 OK', true, 200];
        static::$headers_sent   = false;
        
        return new Response(new Headers(new HeaderFactory), new Cookies(new CookieFactory));
    }
    
    public function test__get()
    {
        $response = $this->newResponse();
        $this->assertInstanceOf('Aura\Http\Headers', $response->headers);
        $this->assertInstanceOf('Aura\Http\Cookies', $response->cookies);
    }
    
    public function test__getNoSuchProperty()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $response = $this->newResponse();
        $response->no_such_property;
    }
    
    public function testSetVersion()
    {
        $resp   = $this->newResponse();
        $return = $resp->setVersion('1.0');
        $actual = $resp->getVersion();
        $this->assertSame('1.0', $actual);
    }

    public function testSetVersionExceptionOnInvalidVersion()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $resp   = $this->newResponse();
        $resp->setVersion('2.0');
    }

    public function testGetVersion()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getVersion();
        $this->assertSame('1.1', $actual);
    }

    public function testSetStatusCode()
    {
        $resp   = $this->newResponse();
        $return = $resp->setStatusCode(101);
        $actual = $resp->getStatusCode();
        $this->assertSame(101, $actual);
    }
    
    public function testSetStatusCodeNoDefaultText()
    {
        $resp   = $this->newResponse();
        $return = $resp->setStatusCode(569);
        $actual = $resp->getStatusCode();
        $this->assertSame(569, $actual);
        $actual = $resp->getStatusText();
        $this->assertSame('', $actual);
    }
    
    public function testSetStatusCodeExceptionLessThan100()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $resp   = $this->newResponse();
        $resp->setStatusCode(99);
    }

    public function testSetStatusCodeExceptionGreaterThan599()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $resp   = $this->newResponse();
        $resp->setStatusCode(600);
    }

    public function testSetStatusText()
    {
        $resp   = $this->newResponse();
        $return = $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
    }

    public function testGetStatusCode()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getStatusCode();
        $this->assertNull($actual);
    }
    
    public function testGetStatusText()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getStatusText();
        $this->assertNull($actual);
        
        $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
    }
    
    public function testSetAndGetHeaders()
    {
        $response = $this->newResponse();
        $headers = new Headers(new HeaderFactory);
        $response->setHeaders($headers);
        $actual = $response->getHeaders();
        $this->assertSame($headers, $actual);
    }

    public function testSetContent()
    {
        $resp   = $this->newResponse();
        $return = $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
    }

    public function testGetContent()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getContent();
        $this->assertSame('', $actual);
        
        $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
    }

    public function testSetAndGetCookies()
    {
        $response = $this->newResponse();
        $cookies = new Cookies(new CookieFactory);
        $response->setCookies($cookies);
        $actual = $response->getCookies();
        $this->assertSame($cookies, $actual);
    }
}
