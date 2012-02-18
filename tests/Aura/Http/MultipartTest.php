<?php

namespace Aura\Http\Request;

use Aura\Http as Http;

use Aura\Http\Factory\Header as HeaderFactory;
use Aura\Http\Factory\Cookie as CookieFactory;

class MultipartTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $multi = new Multipart;
        
        $this->assertNotEmpty($multi->getBoundary());
        $this->assertEmpty($multi->getLength());
    }

    public function test__cloneCallsReset()
    {
        $multi = $this->getMock('\Aura\Http\Request\Multipart', ['reset']);
        
        $multi->expects($this->once())
              ->method('reset');

        clone $multi;
    }

    public function test__toStringCallsToString()
    {
        $multi = $this->getMock('\Aura\Http\Request\Multipart', ['toString']);
        
        $multi->expects($this->once())
              ->method('toString');

        $multi->__toString();
    }

    public function testResetClosesOpenResources()
    {
        $multi = new Multipart;
        $file  = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'gziphttp';

        $multi->add(['test' => "@{$file}"]);
        $multi->reset(); // todo how to test for this?
    }

    public function testAddParams()
    {
        $multi = new Multipart;
        
        $multi->add(['foo' => 'bar', 42 => ['the', 'question']]);

        $expected  = "--" . $multi->getBoundary() . "\r\n";
        $expected .= 'Content-Disposition: form-data; name="foo"' ."\r\n";
        $expected .= "\r\n";
        $expected .= "bar\r\n";
        $expected .= "--" . $multi->getBoundary() . "\r\n";
        $expected .= 'Content-Disposition: form-data; name="42[0]"' ."\r\n";
        $expected .= "\r\n";
        $expected .= "the\r\n";
        $expected .= "--" . $multi->getBoundary() . "\r\n";
        $expected .= 'Content-Disposition: form-data; name="42[1]"' ."\r\n";
        $expected .= "\r\n";
        $expected .= "question\r\n";
        $expected .= "--" . $multi->getBoundary() . "--\r\n";

        $this->assertEquals($expected, $multi->toString());
    }

    public function testAddParamsAndFiles()
    {
        $multi = new Multipart;
        $file  = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'gziphttp';

        $multi->add(['foo' => 'bar', 'file' => "@$file"]);

        $expected  = "--" . $multi->getBoundary() . "\r\n";
        $expected .= 'Content-Disposition: form-data; name="file"; filename="gziphttp"' . "\r\n";
        $expected .= 'Content-Type: application/x-gzip' ."\r\n";
        $expected .= "\r\n";
        $expected .= file_get_contents($file) . "\r\n";
        $expected .= "--" . $multi->getBoundary() . "\r\n";
        $expected .= 'Content-Disposition: form-data; name="foo"' ."\r\n";
        $expected .= "\r\n";
        $expected .= "bar\r\n";
        $expected .= "--" . $multi->getBoundary() . "--\r\n";

        $this->assertEquals($expected, $multi->toString());
    }


    public function testAddFileDoesNotExistException()
    {
        $this->setExpectedException('\Aura\Http\Exception\FileDoesNotExist');

        $multi = new Multipart;
        $file  = '/invalid/file';

        $multi->add(['file' => "@$file"]);

    }
}