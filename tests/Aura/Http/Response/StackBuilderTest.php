<?php
namespace Aura\Http\Response;

use Aura\Http\Message\Factory as MessageFactory;

class StackBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $headers = [
        'HTTP/1.1 302 Found',
        'Location: /redirect-to-here.php',
        'Set-Cookie: foo=bar',
        'Content-Length: 0',
        'Connection: close',
        'Content-Type: text/html',
        'HTTP/1.1 200 OK',
        'Content-Length: 13',
        'Connection: close',
        'Content-Type: text/html',
    ];

    protected $content = 'Hello World!';
    
    protected $builder;
    
    
    protected function setUp()
    {
        $factory = new MessageFactory;
        $this->builder = new StackBuilder(new MessageFactory);
    }
    
    public function testNewInstance()
    {
        $stack = $this->builder->newInstance($this->headers, $this->content);
        
        // there should be two responses in the stack
        $this->assertSame(2, count($stack));
        
        // the most-recent response
        $response = $stack[0];
        $this->assertEquals('1.1', $response->version);
        $this->assertEquals('200', $response->status_code);
        $this->assertEquals('OK', $response->status_text);
        $expect = [
            'Content-Length: 13',
            'Connection: close',
            'Content-Type: text/html',
        ];
        foreach ($response->headers as $actual) {
            $this->assertTrue(in_array($actual->__toString(), $expect));
        }
        $expect = array('foo' => 'bar');
        foreach ($response->cookies as $actual) {
            $name = $actual->name;
            $this->assertSame($expect[$name], $actual->value);
        }
        $this->assertSame($this->content, $response->content);
        
        // the least-recent response
        $response = $stack[1];
        $this->assertEquals('1.1', $response->version);
        $this->assertEquals('302', $response->status_code);
        $this->assertEquals('Found', $response->status_text);
        $expect = [
            'Location: /redirect-to-here.php',
            'Content-Length: 0',
            'Connection: close',
            'Content-Type: text/html',
        ];
        foreach ($response->headers as $i => $actual) {
            $this->assertTrue(in_array($actual->__toString(), $expect));
        }
        $this->assertNull($response->content);
    }
}