<?php
namespace Aura\Http\Content;

use Aura\Http\Content\PartFactory;

class MultiPartTest extends \PHPUnit_Framework_TestCase
{
    protected $multipart;
    
    protected function setUp()
    {
        $this->multipart = new MultiPart(new PartFactory);
    }
    
    public function testGetBoundary()
    {
        $actual = strlen($this->multipart->getBoundary());
        $this->assertSame(23, $actual);
    }
    
    public function testAddAndCount()
    {
        $this->assertSame(0, $this->multipart->count());
        
        $part1 = $this->multipart->add();
        $this->assertInstanceOf('Aura\Http\Content\Part', $part1);
        
        $part2 = $this->multipart->add();
        $this->assertInstanceOf('Aura\Http\Content\Part', $part2);
        
        $this->assertNotSame($part1, $part2);
        
        $this->assertSame(2, $this->multipart->count());
    }
    
    public function testAddData()
    {
        $part = $this->multipart->addData('field_name', 'field_value');
        
        // check the disposition
        $expect = 'form-data; name="field_name"';
        $actual = $part->getHeaders()->get('Content-Disposition');
        $this->assertSame($expect, $actual->getValue());
        
        // check the content
        $expect = 'field_value';
        $actual = $part->getContent();
        $this->assertSame($expect, $actual);
    }
    
    public function testAddFile()
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR
              . '_files' . DIRECTORY_SEPARATOR
              . 'resource.txt';
        
        $part = $this->multipart->addFile('field_name', $file);
        
        // check the disposition
        $expect = 'form-data; name="field_name"; filename="resource.txt"';
        $actual = $part->getHeaders()->get('Content-Disposition');
        $this->assertSame($expect, $actual->getValue());
        
        // check the content
        $expect = 'Hello Resource';
        $actual = $part->getContent();
        $this->assertSame($expect, $actual);
    }
    
    public function test__toString()
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR
              . '_files' . DIRECTORY_SEPARATOR
              . 'resource.txt';
        
        // add two data fields and a file upload
        $this->multipart->addData('foo', 'bar');
        $this->multipart->addData('baz', 'dib');
        $this->multipart->addFile('zim', $file);
        
        // what we expect
        $boundary = $this->multipart->getBoundary();
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="foo"';
        $expect[] = '';
        $expect[] = 'bar';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="baz"';
        $expect[] = '';
        $expect[] = 'dib';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="zim"; filename="resource.txt"';
        $expect[] = '';
        $expect[] = 'Hello Resource';
        $expect[] = "--{$boundary}--";
        $expect[] = '';
        $expect = implode("\r\n", $expect);
        
        // read the whole thing
        $actual = $this->multipart->__toString();
        $this->assertSame($expect, $actual);
    }
}
