<?php
namespace Aura\Http\Content;

use Aura\Http\ContentTest;
use Aura\Http\Content\PartFactory;

class PartTest extends ContentTest
{
    protected function setUp()
    {
        $factory = new PartFactory;
        $this->content = $factory->newInstance();
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
    
    public function testSetEncoding()
    {
        $this->content->setEncoding('binary');
        $header = $this->content->getHeaders()->get('Content-Encoding');
        $expect = 'binary';
        $this->assertSame($expect, $header->getValue());
    }
}
