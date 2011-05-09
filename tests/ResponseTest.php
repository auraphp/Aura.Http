<?php

namespace aura\http;


function header($header, $replace = -1, $response_code = -1)
{
    ResponseTest::$callback->headerCallBack($header, $replace, $response_code);
}

function setcookie($name, $val = -1, $expires = -1, $path = -1, $domain = -1,
                   $secure = -1, $httponly = -1)
{
    ResponseTest::$callback->cookieCallBack($name, $val, $expires, $path, 
                                            $domain, $secure, $httponly);
}

function headers_sent(&$file, &$line)
{
    $file = 'file/foo.php';
    $line = 1;
    
    return ResponseTest::$headers_sent;
}

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public static $callback;
    public static $headers_sent = false;
    protected $header_expect    = array();
    protected $cookie_expect    = array();


    protected function setUp()
    {
        parent::setUp();
        static::$callback = $this;
        
        // for setting cookies
        date_default_timezone_set('GMT');
    }
    
    protected function newResponse()
    {
        $this->header_expect    = $this->cookie_expect = array();
        $this->header_expect[0] = array('HTTP/1.1 200', true, 200);
        static::$headers_sent   = false;

        return new Response(new MimeUtility());
    }
    
    // public function test__get()
    // {
    //     $resp = $this->newResponse();
    //     
    //     // test that we can access without causing an exception
    //     $resp->content;
    //     $resp->header;
    //     $resp->cookie;
    //     $resp->version;
    //     $resp->status_code;
    //     $resp->status_text;
    //     
    //     // invalid or protected should cause an exception
    //     $this->setExpectedException('\UnexpectedValueException');
    //     $resp->invalid;
    // }

    // public function test__set()
    // {
    //     $resp = $this->newResponse();
    //     
    //     // test that we can access without causing an exception
    //     $resp->content     = 'Hi';
    //     $resp->version     = '1.1';
    //     $resp->status_code = 201;
    //     $resp->status_text = 'Status';
    //     
    //     // invalid or protected should cause an exception
    //     $this->setExpectedException('\UnexpectedValueException');
    //     $resp->invalid = 'xxx';
    // }

    public function testSetVersion()
    {
        $resp   = $this->newResponse();
        $return = $resp->setVersion('1.0');
        $actual = $resp->getVersion();
        $this->assertSame('1.0', $actual);
    }

    public function testSetVersionExceptionOnInvalidVersion()
    {
        $this->setExpectedException('\UnexpectedValueException');
        $resp   = $this->newResponse();
        $resp->setVersion('2.0');
    }

    public function testGetVersion()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getVersion();
        // 1.1 is default
        $this->assertSame('1.1', $actual);
        
        $actual = $resp->version;
        $this->assertSame('1.1', $actual);
    }

    public function testSetStatusCode()
    {
        $resp   = $this->newResponse();
        $return = $resp->setStatusCode(101);
        $actual = $resp->getStatusCode();
        $this->assertSame(101, $actual);
    }

    public function testSetStatusCodeExceptionLessThan100()
    {
        $this->setExpectedException('\UnexpectedValueException');
        $resp   = $this->newResponse();
        $resp->setStatusCode(99);
    }

    public function testSetStatusCodeExceptionGreaterThan599()
    {
        $this->setExpectedException('\UnexpectedValueException');
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
        // 200 is default
        $this->assertSame(200, $actual);
        
        $actual = $resp->status_code;
        $this->assertSame(200, $actual);
    }

    public function testGetStatusText()
    {
        $resp   = $this->newResponse();
        $actual = $resp->getStatusText();
        // null is default
        $this->assertNull($actual);
        
        $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
    }

    // public function testSetHeader()
    // {
    //     $resp     = $this->newResponse();
    //     $return   = $resp->setHeader('foo_bar', 'foobar header');
    //     
    //     $actual   = $this->readAttribute($resp, 'headers');
    //     $expected = array(
    //         'Foo-Bar' => 'foobar header'
    //     );
    //     $this->assertEquals($expected, $actual);
    //     
    //     // returns self  
    //     $this->assertType('\aura\http\AbstractResponse', $return);
    // }
    
    // public function testSetHeaderReplace()
    // {
    //     $resp     = $this->newResponse();
    //     $resp->setHeader('foo_bar', 'foobar header');
    //     $resp->setHeader('foo_bar', 'new foobar header');
    //     
    //     $actual   = $this->readAttribute($resp, 'headers');
    //     $expected = array(
    //         'Foo-Bar' => 'new foobar header'
    //     );
    //     $this->assertEquals($expected, $actual);
    // }
    
    // public function testSetHeaderSameHeaderMultipleTimes()
    // {
    //     $resp     = $this->newResponse();
    //     $resp->setHeader('foo_bar', 'foobar header',     false);
    //     $resp->setHeader('foo_bar', 'another foobar header', false);
    //     
    //     $actual   = $this->readAttribute($resp, 'headers');
    //     $expected = array(
    //         'Foo-Bar' => array('foobar header', 'another foobar header')
    //     );
    //     $this->assertEquals($expected, $actual);
    // }
    
    // public function testSetHeaderExceptionWhenSettingHttpHeader()
    // {
    //     $this->setExpectedException('\UnexpectedValueException');
    //     $resp   = $this->newResponse();
    //     $resp->setHeader('Http', 'dont set');
    // }

    public function testSetHeaders()
    {
        $resp      = $this->newResponse();
        $headers[] = array('name' => 'foo', 'value' => 'bar');
        $headers[] = array('name' => 'bar', 'value' => 'foobars', 'replace' => true);
        
        $return    = $resp->setHeaders($headers);
        
        $actual    = $this->readAttribute($resp, 'headers');
        $expected  = array('Foo'  => 'bar', 'Bar' => 'foobars');
        
        $this->assertEquals($expected, $actual);
        
        // returns self  
        $this->assertType('\aura\http\AbstractResponse', $return);
    }

    public function testSetHeadersNoNameException()
    {
        $resp      = $this->newResponse();
        $headers[] = array('value' => 'foobars');
        
        $this->setExpectedException('\UnexpectedValueException');
        $resp->setHeaders($headers);
    }

    public function testSetHeadersNoValueException()
    {
        $resp      = $this->newResponse();
        $headers[] = array('name' => 'foo');
        
        $this->setExpectedException('\UnexpectedValueException');
        $resp->setHeaders($headers);
    }

    // public function testGetHeader()
    // {
    //     $resp   = $this->newResponse();
    //     $actual = $resp->getHeader('does-not-exist');
    //     // null is default if header does not exist
    //     $this->assertNull($actual);
    //     
    //     $resp->setHeader('foo_bar', 'foobar header');
    //     $actual = $resp->getHeader('Foo-Bar');
    //     $this->assertSame('foobar header', $actual);
    //     
    //     $resp->setHeader('foo_bar', 'foobar header 2.0');
    //     $actual = $resp->header['Foo-Bar'];
    //     $this->assertSame('foobar header 2.0', $actual);
    // }
    
    public function testGetHeaders()
    {
        $resp     = $this->newResponse();
        $resp->setHeaders(array(
            'foo' => 'foo header',
            'bar' => 'bar header',
        ));
        
        $actual   = $resp->getHeaders();
        $expected = array(
            'Foo' => 'foo header',
            'Bar' => 'bar header'
        );
        $this->assertEquals($expected, $actual);
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
        // null is default
        $this->assertNull($actual);
        
        $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
    }

    // public function testSetCookie()
    // {
    //     $resp     = $this->newResponse();
    //     $return   = $resp->setCookie('login', '1234567890');
    //     
    //     $actual   = $this->readAttribute($resp, 'cookies');
    //     $expected = array(
    //         'login' => array(
    //             'value'     => '1234567890',
    //             'expire'   => 0,
    //             'path'      => '',
    //             'domain'    => '',
    //             'secure'    => false,
    //             'httponly'  => null,
    //         )
    //     );
    //     $this->assertEquals($expected, $actual);
    //     
    //     // returns self  
    //     $this->assertType('\aura\http\AbstractResponse', $return);
    // }

    public function testSetCookies()
    {
        $resp      = $this->newResponse();
        $cookies[] = array('name' => 'foo');
        $cookies[] = array('name' => 'bar', 'value' => 'foobars');
        
        $return    = $resp->setCookies($cookies);
        
        $actual    = $this->readAttribute($resp, 'cookies');
        $expected  = array(
            'foo' => array(
                'value'     => null,
                'expire'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            ),
            'bar' => array(
                'value'     => 'foobars',
                'expire'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            ),
        );
        $this->assertEquals($expected, $actual);
        
        // returns self  
        $this->assertType('\aura\http\AbstractResponse', $return);
    }

    public function testSetCookiesNoNameException()
    {
        $resp      = $this->newResponse();
        $cookies[] = array('value' => 'foobars');
        
        $this->setExpectedException('\UnexpectedValueException');
        $resp->setCookies($cookies);
    }
    
    // public function testGetCookie()
    // {
    //     $resp     = $this->newResponse();
    //     $actual   = $resp->getCookie('does-not-exist');
    //     $this->assertNull($actual);
    //     
    //     $resp->setCookie('login', '1234567890');
    //     $actual   = $resp->getCookie('login');
    //     $expected = array(
    //         'value'     => '1234567890',
    //         'expire'   => 0,
    //         'path'      => '',
    //         'domain'    => '',
    //         'secure'    => false,
    //         'httponly'  => null,
    //     );
    //     $this->assertEquals($expected, $actual);
    //     
    //     $actual = $resp->cookie['login'];
    //     $this->assertEquals($expected, $actual);
    // }
    
    public function testGetCookies()
    {
        $resp     = $this->newResponse();
        
        $resp->setCookies(array(
            'login' => array(
                'value' => '1234567890',
            ),
            'usrid' => array(
                'value' => '0987654321'
            ),
        ));
        
        $actual   = $resp->getCookies();
        $expected = array(
            'login' => array(
                'value'     => '1234567890',
                'expire'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            ),
            'usrid' => array(
                'value'     => '0987654321',
                'expire'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            )
        );
        
        $this->assertEquals($expected, $actual);
    }
    
    public function test__toString()
    {
        $response = $this->newResponse();
        $response->setContent('hello');
        
        ob_start();
        echo $response;
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame('hello', $actual);
    }

    // public function testSetCookiesHttponly()
    // {
    //     $response = $this->newResponse();
    //     $prop     = $this->readAttribute($response, 'cookies_httponly');
    //     
    //     // default is true
    //     $this->assertTrue($prop);
    //     
    //     $result = $response->setCookiesHttponly(false);
    //     $prop   = $this->readAttribute($response, 'cookies_httponly');
    //     $this->assertFalse($prop);
    //     
    //     // returns self  
    //     $this->assertType('\aura\http\Response', $result);
    // }

    // public function testSetNoCache()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->setNoCache();
    //     $expected = array(
    //         'Pragma'        => 'no-cache',
    //         'Cache-Control' => array(
    //             0 => 'no-store, no-cache, must-revalidate',
    //             1 => 'post-check=0, pre-check=0',
    //         ),
    //         'Expires'       => '1',
    //     );
    //     
    //     $this->assertEquals($expected, $response->header);
    //     
    //     $return = $response->setNoCache(false);
    //     $this->assertEquals(array(), $response->header);
    //     
    //     // returns self  
    //     $this->assertType('\aura\http\Response', $return);
    // }

    // public function testHeadersSendException()
    // {
    //     $response = $this->newResponse();
    //     static::$headers_sent = true;
    //     
    //     $this->setExpectedException('aura\http\Exception_HeadersSent');
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    // }
        
    // public function testSendingStatusUsingProperties()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->version     = '1.0';
    //     $response->status_code = '300';
    //     $response->status_text = 'Hello';
    //     
    //     // overwrite the header that is set by default in setUp()
    //     $this->header_expect[0] = array('HTTP/1.0 300 Hello', true, 300);
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    //     
    //     $this->assertTrue(empty($this->header_expect));
    // }
    
    // public function testSendingStatusUsingMethods()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->setVersion('1.0');
    //     $response->setStatusCode('300');
    //     $response->setStatusText('Hello');
    //     
    //     // overwrite the header that is set by default in setUp()
    //     $this->header_expect[0] = array('HTTP/1.0 300 Hello', true, 300);
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    //     
    //     $this->assertTrue(empty($this->header_expect));
    // }
    
    // public function testEmptyOrSanitizedKeysAreSkiped()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->setHeader('', 'skip me');
    //     $response->setHeader('++', 'me too');
    //     
    //     // The fail will come from headerCallback if header() is called
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    // }
    
    // public function testCRLFRemovedFromHeaderValue()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->setHeader('foo', "hello \r\nworld");
    //     
    //     $this->header_expect[] = array('Foo: hello world');
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    //     
    //     $this->assertTrue(empty($this->header_expect));
    // }
    
    public function testSendingHeaders()
    {
        $response = $this->newResponse();
        
        $response->setHeaders(array(
            'foo' => 'hello world',
            'bar' => 'hello world 2',
        ));
        
        $this->header_expect[] = array('Foo: hello world');
        $this->header_expect[] = array('Bar: hello world 2');
        
        ob_start();
        $response->send();
        ob_end_clean();
        
        $this->assertTrue(empty($this->header_expect));
    }
    
    // public function testSendingCookies()
    // {
    //     $response = $this->newResponse();
    //     
    //     $response->setCookie('foo');
    //     $response->setCookie('bar',      'hello world');
    //     $response->setCookie('far' ,     'away', 1286705400);
    //     $response->setCookie('halfway',  'to far away', '10:10 10/10/10');
    //     $response->setCookie('faraway' , 'away', 1286705400, 'path/', 
    //                          'blog.example.com', true, false);
    //     
    //     $this->cookie_expect[] = array('foo', '', 0, '', '', false, true);
    //     $this->cookie_expect[] = array('bar', 'hello world', 0, '', '', false, true);
    //     $this->cookie_expect[] = array('far', 'away', 1286705400, '', '', false, true);
    //     $this->cookie_expect[] = array('halfway', 'to far away', 1286705400, '',
    //                                    '', false, true);
    //     $this->cookie_expect[] = array('faraway', 'away', 1286705400, 'path/', 
    //                                    'blog.example.com', true, false);
    //     
    //     ob_start();
    //     $response->display();
    //     ob_end_clean();
    //     
    //     $this->assertTrue(empty($this->cookie_expect));
    // }
    
    // public function testDisplay()
    // {
    //     $response = $this->newResponse();
    //     $response->setContent('hello');
    //     
    //     ob_start();
    //     $response->display();
    //     $actual = ob_get_contents();
    //     ob_end_clean();
    //     
    //     $this->assertSame('hello', $actual);
    //     
    //     // using content property
    //     $response = $this->newResponse();
    //     $response->content = 'hello';
    //     
    //     ob_start();
    //     $response->display();
    //     $actual = ob_get_contents();
    //     ob_end_clean();
    //     
    //     $this->assertSame('hello', $actual);
    // }
    
    // public function testRedirect()
    // {
    //     $response = $this->newResponse();
    //     
    //     // overwrite the header that is set by default in setUp()
    //     $this->header_expect[0] = array('HTTP/1.1 302 Found', true, 302);
    //     $this->header_expect[]  = array('Location: http://google.com/q=search');
    //     
    //     ob_start();
    //     //  \r\n should be removed
    //     $response->redirect("http://google.com/\r\nq=search");
    // }
    
    // public function testRedirectWithoutFullUriException()
    // {
    //     $response = $this->newResponse();
    //     
    //     $this->setExpectedException('aura\http\Exception');
    //     
    //     // A full uri requires a scheme
    //     $response->redirect('google.com');
    // }

    public function headerCallback($header, $replace, $response_code)
    {
        $expect  = array_shift($this->header_expect);
        
        if (! $expect) {
            $this->fail('No more headers expected received: ' . $header);
            return;
        }
        
        $this->assertSame(isset($expect[0]) ? $expect[0] : -1, $header);
        $this->assertSame(isset($expect[1]) ? $expect[1] : -1, $replace);
        $this->assertSame(isset($expect[2]) ? $expect[2] : -1, $response_code);
    }

    public function cookieCallBack($name, $val, $expires, $path, $domain, $secure, $httponly)
    {
        $expect  = array_shift($this->cookie_expect);
        
        if (! $expect) {
            $this->fail('No more cookies expected received: ' . $name);
            return;
        }
        
        $this->assertSame(isset($expect[0]) ? $expect[0] : -1, $name);
        $this->assertSame(isset($expect[1]) ? $expect[1] : -1, $val);
        $this->assertSame(isset($expect[2]) ? $expect[2] : -1, $expires);
        $this->assertSame(isset($expect[3]) ? $expect[3] : -1, $path);
        $this->assertSame(isset($expect[4]) ? $expect[4] : -1, $domain);
        $this->assertSame(isset($expect[5]) ? $expect[5] : -1, $secure);
        $this->assertSame(isset($expect[6]) ? $expect[6] : -1, $httponly);
    }
}
