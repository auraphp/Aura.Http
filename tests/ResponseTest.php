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

    public function testSetCookiesHttponly()
    {
        $response = $this->newResponse();
        $prop     = $this->readAttribute($response, 'cookies_httponly');
        
        // default is true
        $this->assertTrue($prop);
        
        $result = $response->setCookiesHttponly(false);
        $prop   = $this->readAttribute($response, 'cookies_httponly');
        $this->assertFalse($prop);
        
        // returns self  
        $this->assertInstanceOf('\aura\http\Response', $result);
    }

    public function testSetNoCache()
    {
        $response = $this->newResponse();
        
        $response->setNoCache();
        $expected = array(
            'Pragma'        => 'no-cache',
            'Cache-Control' => array(
                0 => 'no-store, no-cache, must-revalidate',
                1 => 'post-check=0, pre-check=0',
            ),
            'Expires'       => '1',
        );
        
        $this->assertEquals($expected, $response->header);
        
        $return = $response->setNoCache(false);
        $this->assertEquals(array(), $response->header);
        
        // returns self  
        $this->assertInstanceOf('\aura\http\Response', $return);
    }

    public function testHeadersSendException()
    {
        $response = $this->newResponse();
        static::$headers_sent = true;
        
        $this->setExpectedException('aura\http\Exception_HeadersSent');
        
        ob_start();
        $response->display();
        ob_end_clean();
    }
        
    public function testSendingStatusUsingProperties()
    {
        $response = $this->newResponse();
        
        $response->version     = '1.0';
        $response->status_code = '300';
        $response->status_text = 'Hello';
        
        // overwrite the header that is set by default in setUp()
        $this->header_expect[0] = array('HTTP/1.0 300 Hello', true, 300);
        
        ob_start();
        $response->display();
        ob_end_clean();
        
        $this->assertEmpty($this->header_expect);
    }
    
    public function testSendingStatusUsingMethods()
    {
        $response = $this->newResponse();
        
        $response->setVersion('1.0');
        $response->setStatusCode('300');
        $response->setStatusText('Hello');
        
        // overwrite the header that is set by default in setUp()
        $this->header_expect[0] = array('HTTP/1.0 300 Hello', true, 300);
        
        ob_start();
        $response->display();
        ob_end_clean();
        
        $this->assertEmpty($this->header_expect);
    }
    
    public function testEmptyOrSanitizedKeysAreSkiped()
    {
        $response = $this->newResponse();
        
        $response->setHeader('', 'skip me');
        $response->setHeader('++', 'me too');
        
        // The fail will come from headerCallback if header() is called
        
        ob_start();
        $response->display();
        ob_end_clean();
    }
    
    public function testCRLFRemovedFromHeaderValue()
    {
        $response = $this->newResponse();
        
        $response->setHeader('foo', "hello \r\nworld");
        
        $this->header_expect[] = array('Foo: hello world');
        
        ob_start();
        $response->display();
        ob_end_clean();
        
        $this->assertEmpty($this->header_expect);
    }
    
    public function testSendingHeaders()
    {
        $response = $this->newResponse();
        
        $response->setHeader('foo', 'hello world');
        $response->setHeader('bar', 'hello world 2');
        
        $this->header_expect[] = array('Foo: hello world');
        $this->header_expect[] = array('Bar: hello world 2');
        
        ob_start();
        $response->display();
        ob_end_clean();
        
        $this->assertEmpty($this->header_expect);
    }
    
    public function testSendingCookies()
    {
        $response = $this->newResponse();
        
        $response->setCookie('foo');
        $response->setCookie('bar',      'hello world');
        $response->setCookie('far' ,     'away', 1286705400);
        $response->setCookie('halfway',  'to far away', '10:10 10/10/10');
        $response->setCookie('faraway' , 'away', 1286705400, 'path/', 
                             'blog.example.com', true, false);
        
        $this->cookie_expect[] = array('foo', '', 0, '', '', false, true);
        $this->cookie_expect[] = array('bar', 'hello world', 0, '', '', false, true);
        $this->cookie_expect[] = array('far', 'away', 1286705400, '', '', false, true);
        $this->cookie_expect[] = array('halfway', 'to far away', 1286705400, '',
                                       '', false, true);
        $this->cookie_expect[] = array('faraway', 'away', 1286705400, 'path/', 
                                       'blog.example.com', true, false);
        
        ob_start();
        $response->display();
        ob_end_clean();
        
        $this->assertEmpty($this->cookie_expect);
    }
    
    public function testDisplay()
    {
        $response = $this->newResponse();
        $response->setContent('hello');
        
        ob_start();
        $response->display();
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame('hello', $actual);
        
        // using content property
        $response = $this->newResponse();
        $response->content = 'hello';
        
        ob_start();
        $response->display();
        $actual = ob_get_contents();
        ob_end_clean();
        
        $this->assertSame('hello', $actual);
    }

    /**
     * @todo Implement testRedirect().
     */
    public function testRedirect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

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
