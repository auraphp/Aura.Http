<?php
namespace Aura\Http\Multipart;

use Aura\Http\Multipart\PartFactory;

class PartTest extends \PHPUnit_Framework_TestCase
{
    protected $part;
    
    protected function setUp()
    {
        $factory = new PartFactory;
        $this->part = $factory->newInstance();
    }
    
    public function testSetAndGetContent()
    {
        $expect = 'Hello World!';
        $this->part->setContent($expect);
        $this->assertSame($expect, $this->part->getContent());
    }
    
    public function testSetType()
    {
        $this->part->setType('text/plain');
        $header = $this->part->getHeaders()->get('Content-Type');
        $expect = 'text/plain';
        $this->assertSame($expect, $header->getValue());
        
        $this->part->setType('text/plain', 'UTF-8');
        $header = $this->part->getHeaders()->get('Content-Type');
        $expect = 'text/plain; charset=UTF-8';
        $this->assertSame($expect, $header->getValue());
    }
    
    public function testSetDisposition()
    {
        $this->part->setDisposition('form-data');
        $header = $this->part->getHeaders()->get('Content-Disposition');
        $expect = 'form-data';
        $this->assertSame($expect, $header->getValue());
        
        $this->part->setDisposition('form-data', 'fieldname');
        $header = $this->part->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; name="fieldname"';
        $this->assertSame($expect, $header->getValue());
        
        $this->part->setDisposition('form-data', null, 'filename.ext');
        $header = $this->part->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; filename="filename.ext"';
        $this->assertSame($expect, $header->getValue());
        
        $this->part->setDisposition('form-data', 'fieldname', 'filename.ext');
        $header = $this->part->getHeaders()->get('Content-Disposition');
        $expect = 'form-data; name="fieldname"; filename="filename.ext"';
        $this->assertSame($expect, $header->getValue());
    }
    
    public function testSetEncoding()
    {
        $this->part->setEncoding('binary');
        $header = $this->part->getHeaders()->get('Content-Encoding');
        $expect = 'binary';
        $this->assertSame($expect, $header->getValue());
    }
}
