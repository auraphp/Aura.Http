<?php
namespace Aura\Http\Content;

use Aura\Http\Content\Factory as ContentFactory;

class MultiPartTest extends \PHPUnit_Framework_TestCase
{
    protected $content;
    
    protected function setUp()
    {
        $factory = new ContentFactory;
        $this->content = $factory->newMultiPart();
    }
    
    public function testSetAndGetBoundary()
    {
        // the initial boundary should be 23 chars
        $actual = strlen($this->content->getBoundary());
        $this->assertSame(23, $actual);
        
        // set and get a new boundary
        $expect = uniqid(null, true);
        $this->content->setBoundary($expect);
        $this->assertSame($expect, $this->content->getBoundary());
    }
    
    public function testAddAndCount()
    {
        $this->assertSame(0, $this->content->count());
        
        $part1 = $this->content->add();
        $this->assertInstanceOf('Aura\Http\Content\SinglePart', $part1);
        
        $part2 = $this->content->add();
        $this->assertInstanceOf('Aura\Http\Content\SinglePart', $part2);
        
        $this->assertNotSame($part1, $part2);
        
        $this->assertSame(2, $this->content->count());
    }
    
    public function testAddData()
    {
        $part = $this->content->addData('field_name', 'field_value');
        
        // check the disposition
        $expect = 'form-data; name="field_name"';
        $actual = $part->getHeaders()->get('Content-Disposition');
        $this->assertSame($expect, $actual->getValue());
        
        // check the content
        $expect = 'field_value';
        $actual = $part->get();
        $this->assertSame($expect, $actual);
    }
    
    public function testAddFile()
    {
        $part = $this->content->addFile(
            'field_name',
            'image.png',
            'binary_picture_data',
            'image/png',
            'binary'
        );
        
        // check the disposition
        $expect = 'form-data; name="field_name"; filename="image.png"';
        $actual = $part->getHeaders()->get('Content-Disposition');
        $this->assertSame($expect, $actual->getValue());
        
        // check the type
        $expect = 'image/png';
        $actual = $part->getHeaders()->get('Content-Type');
        $this->assertSame($expect, $actual->getValue());
        
        // check the encoding
        $expect = 'binary';
        $actual = $part->getHeaders()->get('Content-Encoding');
        $this->assertSame($expect, $actual->getValue());
        
        // check the content
        $expect = 'binary_picture_data';
        $actual = $part->get();
        $this->assertSame($expect, $actual);
    }
    
    public function testEofReadRewind()
    {
        // add two data fields and a file upload
        $this->content->addData('foo', 'bar');
        $this->content->addData('baz', 'dib');
        $this->content->addFile('zim', 'gir.png', 'gir-binary-data', 'image/png', 'binary');
        
        // not eof yet
        $this->assertFalse($this->content->eof());
        
        // what we expect
        $boundary = $this->content->getBoundary();
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="foo"';
        $expect[] = '';
        $expect[] = 'bar';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="baz"';
        $expect[] = '';
        $expect[] = 'dib';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="zim"; filename="gir.png"';
        $expect[] = 'Content-Type: image/png';
        $expect[] = 'Content-Encoding: binary';
        $expect[] = '';
        $expect[] = 'gir-binary-data';
        $expect[] = "--{$boundary}--";
        $expect[] = '';
        $expect = implode("\r\n", $expect);
        
        // read the whole thing
        $this->content->rewind();
        $actual = null;
        while (! $this->content->eof()) {
            $actual .= $this->content->read();
        }
        
        // do they match?
        $this->assertSame($expect, $actual);
        
        // we should be at the end
        $this->assertTrue($this->content->eof());
        
        // rewind and make sure we're not at the end any more
        $this->content->rewind();
        $this->assertFalse($this->content->eof());
    }
    
    public function test__toString()
    {
        // add two data fields and a file upload
        $this->content->addData('foo', 'bar');
        $this->content->addData('baz', 'dib');
        $this->content->addFile('zim', 'gir.png', 'gir-binary-data', 'image/png', 'binary');
        
        // what we expect
        $boundary = $this->content->getBoundary();
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="foo"';
        $expect[] = '';
        $expect[] = 'bar';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="baz"';
        $expect[] = '';
        $expect[] = 'dib';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="zim"; filename="gir.png"';
        $expect[] = 'Content-Type: image/png';
        $expect[] = 'Content-Encoding: binary';
        $expect[] = '';
        $expect[] = 'gir-binary-data';
        $expect[] = "--{$boundary}--";
        $expect[] = '';
        $expect = implode("\r\n", $expect);
        
        // read the whole thing
        $actual = $this->content->__toString();
        $this->assertSame($expect, $actual);
    }
}
