<?php
namespace Aura\Http;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\MockAdapter as Adapter;
use Aura\Http\MockPhpFunc as PhpFunc;
use Aura\Http\Response;
use Aura\Http\Transport\Options;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    protected $phpfunc;
    
    protected $adapter;
    
    protected $transport;
    
    public function setUp()
    {
        $this->phpfunc   = new PhpFunc;
        $this->options   = new Options;
        $this->adapter   = new Adapter;
        
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
    }
    
    public function testSendResponse_headersAlreadySent()
    {
        $this->phpfunc->headers_sent = true;
        $response = $this->newResponse();
        
        $this->setExpectedException('Aura\Http\Exception\HeadersSent');
        $this->transport->sendResponse($response);
    }
    
    // public function testSendResponse_resource()
    // {
    //     $expect_content = 'hello resource';
    //     
    //     $file = dirname(__DIR__) . DIRECTORY_SEPARATOR
    //           . 'tmp' . DIRECTORY_SEPARATOR
    //           . 'resource.txt';
    //     
    //     @mkdir(dirname($file));
    //     file_put_contents($file, $expect_content);
    //     
    //     $fh = fopen($file, 'r');
    //     
    //     $response = $this->newResponse();
    //     $response->setContent($fh);
    //     
    //     $actual_content = $this->sendResponse($response);
    //     $this->assertSame($expect_content, $actual_content);
    //     
    //     unlink($file);
    // }
    
    // public function testSendRequest()
    // {
    //     $request = $this->newRequest();
    //     $request->setUrl('http://example.com');
    //     $stack = $this->transport->sendRequest($request);
    //     $this->assertTrue(true);
    // }
}
