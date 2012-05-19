<?php
namespace Aura\Http;

use Aura\Http\Response;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\MockAdapter;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    protected $phpfunc;
    
    protected $adapter;
    
    protected $transport;
    
    public function setUp()
    {
        $this->phpfunc   = new MockPhpFunc;
        $this->adapter   = new MockAdapter;
        $this->transport = new Transport($this->phpfunc, $this->adapter);
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
    
    protected function sendResponse(Response $response)
    {
        ob_start();
        $this->transport->sendResponse($response);
        return ob_get_clean();
    }
    
    public function testSendResponse()
    {
        $response = $this->newResponse();
        
        $actual_content = $this->sendResponse($response);
        $expect_content = 'Hola Mundo!';
        $this->assertSame($expect_content, $actual_content);
        
        $actual_headers = $this->phpfunc->headers;
        $expect_headers = [
          0 => 'HTTP/1.1 200 OK',
          1 => 'Foo: hello world',
          2 => 'Bar: hello world 2',
        ];
        $this->assertSame($expect_headers, $actual_headers);
    }
    
    public function testSendResponse_cgi()
    {
        $response = $this->newResponse();
        $response->setCgi(true);
        
        $actual_content = $this->sendResponse($response);
        $expect_content = 'Hola Mundo!';
        $this->assertSame($expect_content, $actual_content);
        
        $actual_headers = $this->phpfunc->headers;
        $expect_headers = [
            0 => 'Status: 200 OK',
            1 => 'Foo: hello world',
            2 => 'Bar: hello world 2',
        ];
        $this->assertSame($expect_headers, $actual_headers);
    }
    
    public function testSendResponse_headersAlreadySent()
    {
        $this->setExpectedException('Aura\Http\Exception\HeadersSent');
        $this->phpfunc->headers_sent = true;
        $response = $this->newResponse();
        $this->sendResponse($response);
    }
    
    public function testSendResponse_resource()
    {
        $expect_content = 'hello resource';
        
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR
              . 'tmp' . DIRECTORY_SEPARATOR
              . 'resource.txt';
        
        @mkdir(dirname($file));
        file_put_contents($file, $expect_content);
        
        $fh = fopen($file, 'r');
        
        $response = $this->newResponse();
        $response->setContent($fh);
        
        $actual_content = $this->sendResponse($response);
        $this->assertSame($expect_content, $actual_content);
        
        unlink($file);
    }
    
    public function testSendRequest()
    {
        $request = $this->newRequest();
        $request->setUrl('http://example.com');
        $stack = $this->transport->sendRequest($request);
        $this->assertTrue(true);
    }
}
