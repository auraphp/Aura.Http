<?php
namespace Aura\Http\Multipart;

use Aura\Http\Multipart\PartFactory;
use org\bovigo\vfs\vfsStream;

class FormDataTest extends \PHPUnit_Framework_TestCase
{
    protected $form_data;
    
    protected function setUp()
    {
        $this->form_data = new FormData(new PartFactory);
    }
    
    public function getVfsFile()
    {
        $structure = array('resource.txt' => 'Hello Resource');
        $this->root = vfsStream::setup('root', null, $structure);
    }
    
    public function testGetBoundary()
    {
        $actual = strlen($this->form_data->getBoundary());
        $this->assertSame(23, $actual);
    }
    
    public function testAddAndCount()
    {
        $this->assertSame(0, $this->form_data->count());
        
        $part1 = $this->form_data->add();
        $this->assertInstanceOf('Aura\Http\Multipart\Part', $part1);
        
        $part2 = $this->form_data->add();
        $this->assertInstanceOf('Aura\Http\Multipart\Part', $part2);
        
        $this->assertNotSame($part1, $part2);
        
        $this->assertSame(2, $this->form_data->count());
    }
    
    public function testaddString()
    {
        $part = $this->form_data->addString('field_name', 'field_value');
        
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
        $this->getVfsFile();
        $file = vfsStream::url('root/resource.txt');
        $part = $this->form_data->addFile('field_name', $file);
        
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
        $this->getVfsFile();
        $file = vfsStream::url('root/resource.txt');
        // add two data fields and a file upload
        $this->form_data->addString('foo', 'bar');
        $this->form_data->addString('baz', 'dib');
        $this->form_data->addFile('zim', $file);
        
        // what we expect
        $boundary = $this->form_data->getBoundary();
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
        $actual = $this->form_data->__toString();
        $this->assertSame($expect, $actual);
    }
    
    public function testAddFromArray()
    {
        $this->getVfsFile();
        $file = vfsStream::url('root/resource.txt');
        $array = [
            'foo' => array('bar', 'baz'),
            'dib' => array(
                'zim' => 'gir'
            ),
            'doom' => '@' . $file,
        ];
        
        $this->form_data->addFromArray($array);
        
        $boundary = $this->form_data->getBoundary();
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="foo[0]"';
        $expect[] = '';
        $expect[] = 'bar';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="foo[1]"';
        $expect[] = '';
        $expect[] = 'baz';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="dib[zim]"';
        $expect[] = '';
        $expect[] = 'gir';
        $expect[] = "--{$boundary}";
        $expect[] = 'Content-Disposition: form-data; name="doom"; filename="resource.txt"';
        $expect[] = '';
        $expect[] = 'Hello Resource';
        $expect[] = "--{$boundary}--";
        $expect[] = '';
        $expect = implode("\r\n", $expect);
        
        // read the whole thing
        $actual = $this->form_data->__toString();
        $this->assertSame($expect, $actual);
    }
}
