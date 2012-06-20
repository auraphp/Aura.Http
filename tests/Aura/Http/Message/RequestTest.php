<?php
namespace Aura\Http\Message;

use Aura\Http\Content;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\MessageTest;

class RequestTest extends MessageTest
{
    protected function setUp()
    {
        $this->message = new Request(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory),
            new Content(new Headers(new HeaderFactory))
        );
    }
    
    public function testSetAndGetUri()
    {
        $expect = 'http://example.com';
        $this->message->setUri($expect);
        $this->assertSame($expect, $this->message->uri);
    }
    
    public function testSetAndGetMethod()
    {
        $methods = array(
            Request::METHOD_GET,
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_DELETE,
            Request::METHOD_TRACE,
            Request::METHOD_OPTIONS,
            Request::METHOD_TRACE,
            Request::METHOD_COPY,
            Request::METHOD_LOCK,
            Request::METHOD_MKCOL,
            Request::METHOD_MOVE,
            Request::METHOD_PROPFIND,
            Request::METHOD_PROPPATCH,
            Request::METHOD_UNLOCK
        );
    
        foreach ($methods as $method) {
            $this->message->setMethod($method);
            $this->assertSame($method, $this->message->method);
        }
    }
    
    public function testSetMethod_unknown()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownMethod');
        $this->message->setMethod('INVALID_METHOD');
    }
    
    public function testSetAndGetAuth()
    {
        $expect = Request::AUTH_BASIC;
        $this->message->setAuth($expect);
        $this->assertSame($expect, $this->message->auth);
        
        $this->message->setAuth(null);
        $this->assertNull($this->message->auth);
    }
    
    public function testSetAuth_unknown()
    {
        $this->setExpectedException('\Aura\Http\Exception\UnknownAuthType');
        $this->message->setAuth('no-such-auth-type');
    }
    
    public function testSetAndGetUsername()
    {
        $expect = 'foobar';
        $this->message->setUsername($expect);
        $this->assertSame($expect, $this->message->username);
    }
    
    public function testUsername_invalid()
    {
        $this->setExpectedException('\Aura\Http\Exception\InvalidUsername');
        $this->message->setUsername('user:name');
    }
    
    public function testSetAndGetPassword()
    {
        $expect = 'bazdib';
        $this->message->setPassword($expect);
        $this->assertSame($expect, $this->message->password);
    }
    
    public function testGetCredentials()
    {
        $this->message->setUsername('foobar');
        $this->message->setPassword('bazdib');
        $expect = 'foobar:bazdib';
        $this->assertSame($expect, $this->message->getCredentials());
    }
    
    // public function testSetMaxRedirects()
    // {
    //     $req = $this->newRequest();
    //     $this->message->setMaxRedirects(42);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(42, Mock::$request->options->max_redirects);
    // }
    // 
    // public function testSetMaxRedirectsToDefaultUsingFalse()
    // {
    //     $req = $this->newRequest(array('max_redirects' => 11));
    // 
    //     $this->message->setMaxRedirects(42);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(42, Mock::$request->options->max_redirects);
    // 
    //     $this->message->setMaxRedirects(false);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(11, Mock::$request->options->max_redirects);
    // }
    // 
    // public function testSetMaxRedirectsToDefaultUsingNull()
    // {
    //     $req = $this->newRequest(array('max_redirects' => 11));
    // 
    //     $this->message->setMaxRedirects(42);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(42, Mock::$request->options->max_redirects);
    // 
    //     $this->message->setMaxRedirects(null);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(11, Mock::$request->options->max_redirects);
    // }
    // 
    // public function testSetMaxRedirectsReturnsRequest()
    // {
    //     $return = $this->message->setMaxRedirects(42);
    //     $this->assertInstanceOf('\Aura\Http\Request', $return);
    // }
    // 
    // public function testSetTimeout()
    // {
    //     $req = $this->newRequest();
    //     $this->message->setTimeout(42);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(42.0, Mock::$request->options->timeout);
    // }
    // 
    // public function testSetTimeoutToDefaultUsingFalse()
    // {
    //     $req = $this->newRequest(array('timeout' => 11));
    // 
    //     $this->message->setTimeout(42);
    //     $this->transport->sendRequest($req);
    //     $this->assertSame(42.0, Mock::$request->options->timeout);
    // 
    //     $this->message->setTimeout(false);
    //     $this->transport->sendRequest($req);
    //     $this->assertSame(11.0, Mock::$request->options->timeout);
    // }
    // 
    // public function testSetTimeoutToDefaultUsingNull()
    // {
    //     $req = $this->newRequest(array('timeout' => 11));
    // 
    //     $this->message->setTimeout(42);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(42.0, Mock::$request->options->timeout);
    // 
    //     $this->message->setTimeout(null);
    //     
    //     $this->transport->sendRequest($req);
    //     
    //     $this->assertSame(11.0, Mock::$request->options->timeout);
    // }
    // 
    // public function testSetTimeoutReturnsRequest()
    // {
    //     $return = $this->message->setTimeout(42);
    // 
    //     $this->assertInstanceOf('\Aura\Http\Request', $return);
    // }
    
    
    // public function testSetProxyReturnsRequest()
    // {
    //     $return = $this->message->setProxy('http://example.com');
    // 
    //     $this->assertInstanceOf('\Aura\Http\Request', $return);
    // }
    // 
    // public function testSetProxy()
    // {
    //     $this->message->setProxy('http://example.com');
    //     
    //     $this->transport->sendRequest($req);
    // 
    //     $this->assertSame('http://example.com', Mock::$request->proxy->url);
    // }
    // 
    // public function testSetProxyWithoutFullUrlException()
    // {
    //     $this->setExpectedException('\Aura\Http\Exception\FullUrlExpected');
    //     $this->message->setProxy('example.com');
    //     
    //     $this->transport->sendRequest($req);
    // }
    // 
    // public function testSetProxyUserPass()
    // {
    //     $this->message->setProxy('http://example.com')
    //         ->setProxyUserPass('usr', 'pass');
    //     
    //     $this->transport->sendRequest($req);
    // 
    //     $this->assertSame('usr:pass', Mock::$request->proxy->usrpass);
    // }
    // 
    // public function testRemovingProxyUserPass()
    // {
    //     $this->message->setProxy('http://example.com')
    //         ->setProxyUserPass('usr', 'pass');
    //     
    //     $this->transport->sendRequest($req);
    // 
    //     $this->assertSame('usr:pass', Mock::$request->proxy->usrpass);
    // 
    //     $this->message->setProxy('http://example.com')
    //         ->setProxyUserPass(false, false);
    //     
    //     $this->transport->sendRequest($req);
    // 
    //     $this->assertEmpty(Mock::$request->proxy->usrpass);
    // }
}
