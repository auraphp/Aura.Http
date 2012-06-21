<?php
namespace Aura\Http;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    protected $content;
    
    protected $headers;
    
    protected function setUp()
    {
        $this->headers = new Headers(new HeaderFactory);
        $this->content = new Content($this->headers);
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
    
    public function testSetType()
    {
        $this->content->setType('text/plain');
        $header = $this->content->getHeaders()->get('Content-Type');
        $expect = 'text/plain';
        $this->assertSame($expect, $header->getValue());
        
        $this->content->setType('text/plain', 'UTF-8');
        $header = $this->content->getHeaders()->get('Content-Type');
        $expect = 'text/plain; charset=UTF-8';
        $this->assertSame($expect, $header->getValue());
    }
    
    public function testSetDisposition()
    {
        $this->content->setDisposition('form-data');
        $header = $this->content->getHeaders()->get('Content-Disposition');
        $expect = 'form-data';
        $this->assertSame($expect, $header->getValue());
        
        $this->content->setDisposition('form-data', 'fieldname');
        $header = $this->content->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; name="fieldname"';
        $this->assertSame($expect, $header->getValue());
        
        $this->content->setDisposition('form-data', null, 'filename.ext');
        $header = $this->content->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; filename="filename.ext"';
        $this->assertSame($expect, $header->getValue());
        
        $this->content->setDisposition('form-data', 'fieldname', 'filename.ext');
        $header = $this->content->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; name="fieldname"; filename="filename.ext"';
        $this->assertSame($expect, $header->getValue());
    }
    
    public function testTransferEncoding()
    {
        $this->content->setTransferEncoding('binary');
        $header = $this->content->getHeaders()->get('Content-Transfer-Encoding');
        $expect = 'binary';
        $this->assertSame($expect, $header->getValue());
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
    
    public function testEofReadRewind_streamInterface()
    {
        $this->markTestIncomplete('todo');
    }
}