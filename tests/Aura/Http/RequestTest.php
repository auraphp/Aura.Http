<?php

namespace Aura\Http;

use Aura\Http\Request\Adapter\MockAdapter as Mock;
use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Factory\Cookie as CookieFactory;

require_once 'MockAdapter.php';
require_once 'MockFunctions.php';

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected function newRequest($opts = [], $seturl = true)
    {
        $adapter  = new Mock();
        $request  = new Request(
                            $adapter, 
                            new Headers(new HeaderFactory),
                            new Cookies(new CookieFactory),
                            $opts);

        if ($seturl) {
            $request->setUrl('http://example.com');
        }

        return $request;
    }

    public function test__clone()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('reset'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
                 ->method('reset');

        $newreq = clone $req;
    }

    public function testInvalidPropertyException()
    {
        $this->setExpectedException('\Aura\Http\Exception');
        
        $req     = $this->newRequest();
        $invalid = $req->invalid;
    }

    public function testInvalidMethodException()
    {
        $this->setExpectedException('\Aura\Http\Exception');
        
        $req     = $this->newRequest();
        $invalid = $req->invalid();
    }

    public function test__callGET()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('setMethod', 'setUrl'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(Request::GET))
            ->will($this->returnValue($req));
        
        $req->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('http://auraphp.com'));

        // because setUrl() is mocked send() will throw an exception but 
        // we are not testing send() here.
        try {
            $req->get('http://auraphp.com');
        } catch (\Aura\Http\Exception $e) {} 
    }

    public function test__callPOST()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('setMethod', 'setUrl'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(Request::POST))
            ->will($this->returnValue($req));
        
        $req->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('http://auraphp.com'));

        // because setUrl() is mocked send() will throw an exception but 
        // we are not testing send() here.
        try {
            $req->post('http://auraphp.com');
        } catch (\Aura\Http\Exception $e) {} 
    }

    public function test__callPUT()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('setMethod', 'setUrl'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(Request::PUT))
            ->will($this->returnValue($req));
        
        $req->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('http://auraphp.com'));

        // because setUrl() is mocked send() will throw an exception but 
        // we are not testing send() here.
        try {
            $req->put('http://auraphp.com');
        } catch (\Aura\Http\Exception $e) {} 
    }

    public function test__callDELETE()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('setMethod', 'setUrl'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(Request::DELETE))
            ->will($this->returnValue($req));
        
        $req->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('http://auraphp.com'));

        // because setUrl() is mocked send() will throw an exception but 
        // we are not testing send() here.
        try {
            $req->delete('http://auraphp.com');
        } catch (\Aura\Http\Exception $e) {} 
    }

    public function testSetUrlThroughSend()
    {
        $req = $this->getMock(
                    '\Aura\Http\Request', 
                    array('setUrl'),
                    array(),
                    '',
                    false);
        
        $req->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('http://auraphp.com'));

        // because setUrl() is mocked send() will throw an exception but 
        // we are not testing send() here.
        try {
            $req->send('http://auraphp.com');
        } catch (\Aura\Http\Exception $e) {} 
    }

    public function testSendNoUriException()
    {
        $this->setExpectedException('\Aura\Http\Exception');
        $req = $this->newRequest([], false);
        $req->send();
    }

    public function testSaveToDisablesEncoding()
    {
        $req = $this->newRequest();
        $req->setEncoding(true);

        $this->assertTrue(isset($req->headers->{'Accept-Encoding'}));

        $GLOBALS['is_writeable'] = true;
        $req->saveTo(__DIR__ . DIRECTORY_SEPARATOR . '_files');
        unset($GLOBALS['is_writeable']);
        
        $req->send();

        $this->assertFalse(isset(Mock::$request->headers->{'Accept-Encoding'}));
    }

    public function testSaveToNotWritableException()
    {
        $this->setExpectedException('\Aura\Http\Exception\NotWriteable');

        $req = $this->newRequest();

        $GLOBALS['is_writeable'] = false;
        $req->saveTo(__DIR__ . DIRECTORY_SEPARATOR . '_files');
        unset($GLOBALS['is_writeable']);
        
        $req->send();
    }

    public function testSetCookieJar()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $req  = $this->newRequest();
        $req->setCookieJar($file)->send();

        $this->assertSame($file, Mock::$request->options->cookiejar);
    }

    public function testUnsetCookieJar()
    {
        touch(__DIR__ . '/_files/cookietest');

        $req = $this->newRequest();
        $req->setCookieJar(__DIR__ . '/_files/cookietest');
        
        // check the file was created for the tests
        $this->assertTrue(file_exists(__DIR__ . '/_files/cookietest'));
        $this->assertTrue(isset($req->options->cookiejar));

        // ready to test deleting the cookie jar
        $req->setCookieJar(false)->send();

        $this->assertFalse(isset(Mock::$request->options->cookiejar));
        $this->assertFalse(file_exists(__DIR__ . '/_files/cookietest'));
    }

    public function testSetCookieJarReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setCookieJar(__DIR__ . '/_files/cookietest');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetCookieJarNotWritableException()
    {
        $this->setExpectedException('\Aura\Http\Exception\NotWriteable');

        $req    = $this->newRequest();

        $GLOBALS['is_writeable'] = false;
        $return = $req->setCookieJar(__DIR__ . '/_files/cookietest');
        unset($GLOBALS['is_writeable']);
    }

    public function testSetHttpAuth()
    {
        $req = $this->newRequest();
        
        $req->setHttpAuth('usr', 'pass');
        $req->send();

        $this->assertEquals(array(0 => Request::BASIC, 1 => 'usr:pass', ), 
                          Mock::$request->options->http_auth);
    }

    public function testUnsetHttpAuth()
    {
        $req = $this->newRequest();
        $req->setHttpAuth('usr', 'pass');
        $req->send();

        $this->assertFalse(empty(Mock::$request->options->http_auth));

        // test unsetting
        $req->setHttpAuth(false, false);
        $req->send();

        $this->assertTrue(empty(Mock::$request->options->http_auth));
    }

    public function testSetHttpAuthUnknownAuthTypeException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownAuthType');

        $req = $this->newRequest();
        $req->setHttpAuth('usr', 'pass', 'FooBar');
    }

    public function testSetHttpAuthColonInHandleException()
    {
        $this->setExpectedException('\Aura\Http\Exception\InvalidHandle');

        $req = $this->newRequest();
        $req->setHttpAuth('invalid:handle', 'pass');
    }

    public function testSetHttpAuthReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setHttpAuth('usr', 'pass');
        
        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetUrlWithString()
    {
        $req = $this->newRequest();
        $req->setUrl('http://example.com');
        $req->send();

        $this->assertSame('http://example.com', Mock::$request->url);
    }

    public function testSetUrlReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setUrl('http://example.com');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetUrlWithoutFullUrlException()
    {
        $req = $this->newRequest();
        $this->setExpectedException('\Aura\Http\Exception\FullUrlExpected');
        $req->setUrl('example.com')->send();
    }

    public function testSetMethod()
    {
        $allowed = array(
            Request::GET,
            Request::POST,
            Request::PUT,
            Request::DELETE,
            Request::TRACE,
            Request::OPTIONS,
            Request::TRACE,
            Request::COPY,
            Request::LOCK,
            Request::MKCOL,
            Request::MOVE,
            Request::PROPFIND,
            Request::PROPPATCH,
            Request::UNLOCK
        );

        foreach ($allowed as $method) {
            $req = $this->newRequest();
            $req->setMethod($method)->send();

            $this->assertSame($method, Mock::$request->method);
        }
    }

    public function testSetMethodUnknownMethodException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownMethod');

        $req = $this->newRequest();
        $req->setMethod('INVALID_METHOD');
    }

    public function testSetMethodReturnRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setMethod(Request::GET);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContentType()
    {
        $req = $this->newRequest();
        $req->setContentType('text/text')
            ->setContent('hello')
            ->setMethod(Request::POST)
            ->send();
        
        // charset utf-8 is the default option
        $this->assertSame('text/text; charset=utf-8', 
                          Mock::$request->headers->get('Content-Type', false)
                                        ->getValue());
    }

    public function testSetContentTypeAndCharset()
    {
        $req = $this->newRequest();
        $req->setContentType('text/text')
            ->setCharset('utf-7')
            ->setContent('hello')
            ->setMethod(Request::POST)
            ->send();
        
        $this->assertSame('text/text; charset=utf-7', 
                          Mock::$request->headers->get('Content-Type', false)
                                        ->getValue());
    }

    public function testSetCharsetTypeReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setCharset('utf-8');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContentTypeReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setContentType('text/text');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetContent()
    {
        $req = $this->newRequest();
        $req->setContent('Hello World')
            ->setContentType('text/text')
            ->send();
        
        $this->assertSame('Hello World', Mock::$request->content);
    }

    public function testSetContentAsArrayByGet()
    {
        $req = $this->newRequest();
        $data = array('var' => '123', 'var2' => 'abc');
        $req->setContent($data)
            ->send();
        
        $this->assertSame('http://example.com?var=123&var2=abc', Mock::$request->url);
    }

    public function testSetContentAsArrayByPost()
    {
        $req  = $this->newRequest();
        $data = array('var' => '123', 'var2' => 'abc');
        $req->setContent($data)
            ->setContentType('text/text')
            ->setMethod(Request::POST)
            ->send();
        
        // content-type should be overwritten
        $this->assertSame('application/x-www-form-urlencoded; charset=utf-8', 
                            Mock::$request->headers->get('Content-Type', false)->getValue());
        $this->assertSame($data, Mock::$request->content);
    }

    public function testSetFileContentByPost()
    {
        $req  = $this->newRequest();
        $data = array('file' => '@/path/to/file.ext');
        $req->setContent($data)
            ->setContentType('text/text')
            ->setMethod(Request::POST)
            ->send();
        
        // content-type should be overwritten
        $this->assertSame('multipart/form-data; charset=utf-8', 
                Mock::$request->headers->get('Content-Type', false)->getValue());
        $this->assertSame($data, Mock::$request->content);
    }

    public function testSetContentReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setContent('Hello World');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetVersion()
    {
        $req = $this->newRequest();
        $req->setVersion('1.0')
            ->send();
        
        $this->assertSame('1.0', Mock::$request->version);

        $req = $this->newRequest();
        $req->setVersion('1.1')
            ->send();
        
        $this->assertSame('1.1', Mock::$request->version);
    }

    public function testSetVersionUnknownVersionException()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownVersion');
        $req = $this->newRequest();
        $req->setVersion('100');
    }

    public function testSetVersionReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setVersion('1.1');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetUserAgent()
    {
        $req = $this->newRequest();
        $req->setUserAgent('My/UserAgent 1.0')
            ->send();
        
        $this->assertSame('My/UserAgent 1.0', 
            Mock::$request->headers->get('User-Agent', false)->getValue());
    }

    public function testSetUserAgentReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setUserAgent('My/UserAgent 1.0');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetEncoding()
    {
        $req = $this->newRequest();
        $req->setEncoding()
            ->send();
        
        $this->assertSame('gzip,deflate', 
            Mock::$request->headers->get('Accept-Encoding', false)->getValue());
    }

    public function testUnsetEncoding()
    {
        $req = $this->newRequest();

        $req->setEncoding()
            ->send();
        
        $this->assertSame('gzip,deflate', 
            Mock::$request->headers->get('Accept-Encoding', false)->getValue());


        $req->setEncoding(false)
            ->send();
        
        $this->assertFalse(isset(Mock::$request->headers->{'Accept-Encoding'}));
    }

    public function testSetEncodingWithoutZlibException()
    {
        $this->setExpectedException('\Aura\Http\Exception');

        $GLOBALS['function_exists'] = false;
        $req = $this->newRequest();
        $req->setEncoding();
    }

    public function testSetEncodingReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setEncoding();

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetMaxRedirects()
    {
        $req = $this->newRequest();
        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$request->options->max_redirects);
    }

    public function testSetMaxRedirectsToDefaultUsingFalse()
    {
        $req = $this->newRequest(array('max_redirects' => 11));

        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$request->options->max_redirects);

        $req->setMaxRedirects(false)
            ->send();
        
        $this->assertSame(11, Mock::$request->options->max_redirects);
    }

    public function testSetMaxRedirectsToDefaultUsingNull()
    {
        $req = $this->newRequest(array('max_redirects' => 11));

        $req->setMaxRedirects(42)
            ->send();
        
        $this->assertSame(42, Mock::$request->options->max_redirects);

        $req->setMaxRedirects(null)
            ->send();
        
        $this->assertSame(11, Mock::$request->options->max_redirects);
    }

    public function testSetMaxRedirectsReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setMaxRedirects(42);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetTimeout()
    {
        $req = $this->newRequest();
        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$request->options->timeout);
    }

    public function testSetTimeoutToDefaultUsingFalse()
    {
        $req = $this->newRequest(array('timeout' => 11));

        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$request->options->timeout);

        $req->setTimeout(false)
            ->send();
        
        $this->assertSame(11.0, Mock::$request->options->timeout);
    }

    public function testSetTimeoutToDefaultUsingNull()
    {
        $req = $this->newRequest(array('timeout' => 11));

        $req->setTimeout(42)
            ->send();
        
        $this->assertSame(42.0, Mock::$request->options->timeout);

        $req->setTimeout(null)
            ->send();
        
        $this->assertSame(11.0, Mock::$request->options->timeout);
    }

    public function testSetTimeoutReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setTimeout(42);

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetHeaderReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setHeader('referer', 'http://example.com');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetHeaderSanitizesLabel()
    {
        $req    = $this->newRequest();
        $req->setHeader("key\r\n-=foo", 'value')->send();

        $this->assertTrue(array_key_exists('Key-Foo', Mock::$request->headers->getAll()));;
    }

    public function testSetHeaderDeleteHeaderWithNullOrFalseValue()
    {
        $req     = $this->newRequest();

        // false
        $req->setHeader("key", 'value')->send();

        $this->assertTrue(isset(Mock::$request->headers->Key));

        $req->setHeader("key", false)->send();

        $this->assertFalse(isset(Mock::$request->headers->Key));

        // null
        $req->setHeader("key", 'value')->send();

        $this->assertTrue(isset(Mock::$request->headers->Key));

        $req->setHeader("key", null)->send();

        $this->assertFalse(isset(Mock::$request->headers->Key));
    }

    public function testSetHeaderReplaceValue()
    {
        $req     = $this->newRequest();
        
        $req->setHeader("key", 'value')->send();

        $this->assertSame('value', Mock::$request->headers->Key->getValue());

        $req->setHeader("key", 'value2')->send();

        $this->assertSame('value2', Mock::$request->headers->Key->getValue());
    }

    public function testSetHeaderMultiValue()
    {
        $req     = $this->newRequest();
        
        $req->setHeader("key", 'value', false);
        $req->setHeader("key", 'value2', false);
        $req->send();

        $expected = ['value', 'value2'];

        foreach (Mock::$request->headers->get('Key') as $i => $value) {
            $this->assertSame('Key', $value->getLabel());
            $this->assertSame($expected[$i], $value->getValue());
        }
    }

    public function testSetHeaderSettingCookiesException()
    {
        $req    = $this->newRequest();
        $this->setExpectedException('\Aura\Http\Exception');
        $req->setHeader("cookie", 'value');
    }

    public function testSetCookieReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setCookie("cookie", 'value');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetCookie()
    {
        $req = $this->newRequest();
        $req->setCookie("cookie", array('value' => 'value', 'httponly' => false));
        $req->setCookie("cookie-name", 'value2');
        $req->send();
        
        $expected = 'cookie=value; cookie-name=value2';

        $this->assertSame($expected, Mock::$request->headers->Cookie->getValue());
    }

    public function testSetRefererReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setReferer('http://example.com');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetReferer()
    {
        $req    = $this->newRequest();
        $req->setReferer('http://example.com')->send();

        $this->assertSame('http://example.com', 
            Mock::$request->headers->Referer->getValue());
    }

    public function testSetRefererWithoutFullUrlException()
    {
        $req    = $this->newRequest();
        $this->setExpectedException('\Aura\Http\Exception\FullUrlExpected');
        $req->setReferer('example.com')->send();
    }

    public function testSetProxyReturnsRequest()
    {
        $req    = $this->newRequest();
        $return = $req->setProxy('http://example.com');

        $this->assertInstanceOf('\Aura\Http\Request', $return);
    }

    public function testSetProxy()
    {
        $req    = $this->newRequest();
        $req->setProxy('http://example.com')->send();

        $this->assertSame('http://example.com', Mock::$request->proxy->url);
    }

    public function testSetProxyWithoutFullUrlException()
    {
        $req    = $this->newRequest();
        $this->setExpectedException('\Aura\Http\Exception\FullUrlExpected');
        $req->setProxy('example.com')->send();
    }

    public function testSetProxyUserPass()
    {
        $req    = $this->newRequest();
        $req->setProxy('http://example.com')
            ->setProxyUserPass('usr', 'pass')
            ->send();

        $this->assertSame('usr:pass', Mock::$request->proxy->usrpass);
    }

    public function testRemovingProxyUserPass()
    {
        $req    = $this->newRequest();
        $req->setProxy('http://example.com')
            ->setProxyUserPass('usr', 'pass')
            ->send();

        $this->assertSame('usr:pass', Mock::$request->proxy->usrpass);

        $req->setProxy('http://example.com')
            ->setProxyUserPass(false, false)
            ->send();

        $this->assertEmpty(Mock::$request->proxy->usrpass);
    }
}
