<?php
namespace Aura\Http\Integration;

use Aura\Http\Message\Request;
use org\bovigo\vfs\vfsStream;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    protected $url;

    protected function setUp()
    {
        $this->url = 'http://localhost';
    }

    // yes, i know it should not touch the network. let me know if you
    // actually write a test that gets better coverage and reliability.
    public function testExec()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->headers->set('User-Agent', 'aura-test');
        $request->headers->set('Referer', 'http://auraphp.github.com');
        $request->headers->set('X-Foo', 'bar');
        $request->cookies->set('foo', [
            'value' => 'bar',
        ]);
        $request->setContent('Should be ignored for GET');
        $stack = $this->http->sendRequest($request);

        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);

        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_connectionFailed()
    {
        $request = $this->http->newRequest();
        $request->setUrl('http://no-such-host.localhost');
        $this->setExpectedException('Aura\Http\Exception\ConnectionFailed');
        $stack = $this->http->sendRequest($request);
    }

    public function testExec_version10()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setVersion('1.0');
        $stack = $this->http->sendRequest($request);
        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_noVersion()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setVersion(null);
        $stack = $this->http->sendRequest($request);
        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_post()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setMethod(Request::METHOD_POST);
        $request->setContent(['foo' => 'bar']);
        $stack = $this->http->sendRequest($request);
        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_head()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setMethod(Request::METHOD_HEAD);
        $stack = $this->http->sendRequest($request);
        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertTrue(empty($content));
        $headers = $stack[0]->headers->__toString();
        $this->assertFalse(empty($headers));
    }

    public function testExec_putFile()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setMethod(Request::METHOD_PUT);

        $structure = array('resource.txt' => 'Hello Resource');
        $root = vfsStream::setup('root', null, $structure);
        $file = vfsStream::url('root/resource.txt');
        $storage = fopen($file, 'w+');
        fwrite($storage, 'foobar');
        rewind($storage);
        $request->setContent($storage);

        $stack = $this->http->sendRequest($request);

        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_putString()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setMethod(Request::METHOD_PUT);
        $request->setContent('foobar');

        $stack = $this->http->sendRequest($request);

        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_custom()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $request->setMethod(Request::METHOD_TRACE);
        $stack = $this->http->sendRequest($request);
        $this->assertInstanceOf('Aura\Http\Message\ResponseStack', $stack);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }

    public function testExec_saveToFile()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $structure = array('resource.txt' => 'Hello Resource');
        $root = vfsStream::setup('root', null, $structure);
        $file = vfsStream::url('root/resource.txt');
        $stream = fopen($file, 'w+');
        $request->setSaveToStream($stream);
        $stack = $this->http->sendRequest($request);
        $content = $stack[0]->content;
        $this->assertSame($stream, $content);
    }

    public function testExec_cookiejar()
    {
        $request = $this->http->newRequest();
        $request->setUrl($this->url);
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(uniqid());
        $this->http->transport->options->setCookieJar($file);
        $stack = $this->http->sendRequest($request);
        $content = $stack[0]->content;
        $this->assertFalse(empty($content));
    }
}
