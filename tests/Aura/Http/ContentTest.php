<?php
namespace Aura\Http;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    protected $content;
    
    protected function setUp()
    {
        $this->content = new Content;
    }
    
    public function test__toString()
    {
        $this->content->set('Hello World!');
        $expect = "Hello World!";
        $actual = $this->content->__toString();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGet()
    {
        $expect = 'Hello World!';
        $this->content->set($expect);
        $this->assertSame($expect, $this->content->get());
    }
    
    public function testEofReadRewind_string()
    {
        $this->content->set('Hello World!');
        
        $this->assertFalse($this->content->eof());
        
        $expect = "Hello World!";
        $actual = $this->content->read();
        $this->assertSame($expect, $actual);
        
        $this->assertTrue($this->content->eof());
        $this->content->rewind();
        $this->assertFalse($this->content->eof());
    }
    
    public function testEofReadRewind_array()
    {
        $this->content->set([
            'foo' => 'bar',
            'baz' => 'dib',
        ]);
        
        $this->assertFalse($this->content->eof());
        
        $expect = "foo=bar&baz=dib";
        $actual = $this->content->read();
        $this->assertSame($expect, $actual);
        
        $this->assertTrue($this->content->eof());
        $this->content->rewind();
        $this->assertFalse($this->content->eof());
    }
    
    public function testEofReadRewind_resource()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR
              . '_files' . DIRECTORY_SEPARATOR
              . 'resource.txt';
        
        $fh = fopen($file, 'r+');
        $this->content->set($fh);
        
        $this->assertFalse($this->content->eof());
        
        $expect = "Hello Resource";
        $actual = $this->content->read();
        $this->assertSame($expect, $actual);
         
        $this->assertTrue($this->content->eof());
        $this->content->rewind();
        $this->assertFalse($this->content->eof());
        
        fclose($fh);
    }
}
