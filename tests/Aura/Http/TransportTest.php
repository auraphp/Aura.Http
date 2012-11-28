<?php
namespace Aura\Http;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;
use Aura\Http\MockAdapter as MockAdapter;
use Aura\Http\MockPhpFunc as MockPhpFunc;
use Aura\Http\Transport\Options;
use org\bovigo\vfs\vfsStream;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    protected $phpfunc;
    
    protected $adapter;
    
    protected $transport;
    
    public function setUp()
    {
        $this->phpfunc   = new MockPhpFunc;
        $this->options   = new Options;
        $this->adapter   = new MockAdapter;
        
        $this->transport = new Transport(
            $this->phpfunc,
            $this->options,
            $this->adapter
        );
    }
    
    protected function newResponse()
    {
        $response = new Response(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
        
        $response->headers->setAll([
            'Foo' => 'hello world',
            'Bar' => 'hello world 2',
        ]);
        
        $response->cookies->set('foo', [
            'value' => 'bar',
        ]);
        
        $response->setContent('Hola Mundo!');
        
        return $response;
    }
    
    protected function newRequest()
    {
        $request = new Request(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
        
        return $request;
    }
    
    public function test__get()
    {
        $this->assertSame($this->phpfunc, $this->transport->phpfunc);
        $this->assertSame($this->options, $this->transport->options);
        $this->assertSame($this->adapter, $this->transport->adapter);
    }
    
    public function testSendResponse()
    {
        $response = $this->newResponse();
        
        $this->assertFalse($this->transport->isCgi());
        $this->transport->sendResponse($response);
        
        $expect = 'Hola Mundo!';
        $actual = $this->phpfunc->content;
        $this->assertSame($expect, $actual);
        
        $actual = $this->phpfunc->headers;
        $expect = [
            0 => 'HTTP/1.1 200 OK',
            1 => 'Foo: hello world',
            2 => 'Bar: hello world 2',
        ];
        $this->assertSame($expect, $actual);
        
        $actual = $this->phpfunc->cookies;
        $expect = [
           0 => [
                'name' => 'foo',
                'value' => 'bar',
                'expire' => 0,
                'path' => null,
                'domain' => null,
                'secure' => false,
                'httponly' => true,
            ],
        ];
        $this->assertSame($expect, $actual);
    }
    
    public function testSendResponse_cgi()
    {
        $response = $this->newResponse();
        
        $this->transport->setCgi(true);
        $this->transport->sendResponse($response);
        
        $expect = 'Hola Mundo!';
        $actual = $this->phpfunc->content;
        $this->assertSame($expect, $actual);
        
        $actual = $this->phpfunc->headers;
        $expect = [
            0 => 'Status: 200 OK',
            1 => 'Foo: hello world',
            2 => 'Bar: hello world 2',
        ];
        $this->assertSame($expect, $actual);
        
        $actual = $this->phpfunc->cookies;
        $expect = [
           0 => [
                'name' => 'foo',
                'value' => 'bar',
                'expire' => 0,
                'path' => null,
                'domain' => null,
                'secure' => false,
                'httponly' => true,
            ],
        ];
        $this->assertSame($expect, $actual);
    }
    
    public function testSendResponse_headersAlreadySent()
    {
        $this->phpfunc->headers_sent = true;
        $response = $this->newResponse();
        
        $this->setExpectedException('Aura\Http\Exception\HeadersSent');
        $this->transport->sendResponse($response);
    }
    
    public function testSendResponse_resource()
    {
        $response = $this->newResponse();
        
        $structure = array('resource.txt' => 'Hello Resource');
        $root = vfsStream::setup('root', null, $structure);
        $file = vfsStream::url('root/resource.txt');
        
        $fh = fopen($file, 'r');
        $response->setContent($fh);
        $this->transport->sendResponse($response);
        fclose($fh);
        
        $expect = file_get_contents($file);
        $actual = $this->phpfunc->content;
        $this->assertSame($expect, $actual);
    }
    
    public function testSendRequest()
    {
        $request = $this->newRequest();
        $request->setUrl('http://example.com');
        $this->transport->sendRequest($request);
        
        $this->assertSame($request, $this->adapter->request);
        $this->assertSame($this->options, $this->adapter->options);
    }
}
