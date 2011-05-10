<?php

namespace aura\http;

// function header($header, $replace = -1, $response_code = -1)
// {
//     ResponseTest::$callback->headerCallBack($header, $replace, $response_code);
// }

// function setcookie($name, $val = -1, $expires = -1, $path = -1, $domain = -1,
//                    $secure = -1, $httponly = -1)
// {
//     ResponseTest::$callback->cookieCallBack($name, $val, $expires, $path, 
//                                             $domain, $secure, $httponly);
// }

// function headers_sent(&$file, &$line)
// {
//     $file = 'file/foo.php';
//     $line = 1;
//     
//     return ResponseTest::$headers_sent;
// }

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public static $callback;
    public static $headers_sent = false;
    protected $header_expect    = array();
    protected $cookie_expect    = array();
    
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
        $this->header_expect    = array();
        $this->cookie_expect    = array();
        $this->header_expect[0] = array('HTTP/1.1 200 OK', true, 200);
        static::$headers_sent   = false;
        
        return new Response(new Headers, new Cookies);
    }
    
    public function test__get()
    {
        $response = $this->newResponse();
        $this->assertType('aura\http\Headers', $response->headers);
        $this->assertType('aura\http\Cookies', $response->cookies);
    }
    
    public function test__getNoSuchProperty()
    {
        $this->setExpectedException('aura\http\Exception');
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
        $this->setExpectedException('aura\http\Exception');
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
        $this->setExpectedException('aura\http\Exception');
        $resp   = $this->newResponse();
        $resp->setStatusCode(99);
    }

    public function testSetStatusCodeExceptionGreaterThan599()
    {
        $this->setExpectedException('aura\http\Exception');
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
        $this->assertSame(200, $actual);
    }
    
    public function testGetStatusText()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getStatusText();
        $this->assertSame('OK', $actual);
        
        $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
    }
    
    public function testSetAndGetHeaders()
    {
        $response = $this->newResponse();
        $headers = new Headers;
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
        $this->assertNull($actual);
        
        $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
    }

    public function testSetAndGetCookies()
    {
        $response = $this->newResponse();
        $cookies = new Cookies;
        $response->setCookies($cookies);
        $actual = $response->getCookies();
        $this->assertSame($cookies, $actual);
    }
    
    public function testSendHeaders()
    {
        $response = $this->newResponse();
        
        $headers = new Headers;
        $headers->setAll(array(
            'Foo' => 'hello world',
            'Bar' => 'hello world 2',
        ));
        
        $response->setHeaders($headers);
        
        ob_start();
        $response->sendHeaders();
        ob_end_clean();
        
        $expect = array (
          0 => 'HTTP/1.1 200 OK',
          1 => 'Foo: hello world',
          2 => 'Bar: hello world 2',
        );
        
        $this->assertSame($expect, MockHttp::$headers);
    }
    
    public function testSendHeadersAlreadySent()
    {
        $this->setExpectedException('aura\http\Exception_HeadersSent');
        MockHttp::$headers_sent = true;
        $response = $this->newResponse();
        $response->sendHeaders();
    }
    
    public function testSend()
    {
        $response = $this->newResponse();
        $response->setContent('hello');
        
        ob_start();
        $response->send();
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame('hello', $actual);
    }
}
