<?php
namespace Aura\Http\Message;

use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\MessageTest;
use org\bovigo\vfs\vfsStream;

class RequestTest extends MessageTest
{
    protected function setUp()
    {
        $this->message = new Request(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
    }
    
    public function testSetAndGetUrl()
    {
        $expect = 'http://example.com';
        $this->message->setUrl($expect);
        $this->assertSame($expect, $this->message->url);
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
    
    public function testSetAndGetSaveToStream()
    {
        $structure = array('resource.txt' => 'Hello Resource');
        $root = vfsStream::setup('root', null, $structure);
        $file = vfsStream::url('root/resource.txt');
        $stream = fopen($file, 'wb+');
        $this->message->setSaveToStream($stream);
        $actual = $this->message->getSaveToStream();
        $this->assertSame($stream, $actual);
        fclose($stream);
    }
}
