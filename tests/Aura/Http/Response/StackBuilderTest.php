<?php

namespace Aura\Http\Request;

use Aura\Http\Request\ResponseStackFactory as ResponseStackFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;

class StackBuilderTest extends \PHPUnit_Framework_TestCase
{
    // protected $response;
    // protected $builder;
    // protected $tmp_dir;
    // 
    // 
    // protected function setUp()
    // {
    //     parent::setUp();
    // 
    //     $headers = new Headers(new HeaderFactory);
    //     $cookies = new Cookies(new CookieFactory);
    // 
    //     $this->tmp_dir  = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp';
    //     $this->response = new Response($headers, $cookies);
    //     $this->builder  = new ResponseBuilderExtended(
    //                             $this->response, 
    //                             new ResponseStackFactory);
    // 
    //     $this->builder->setRequestUrl('http://example.com');
    // }
    // 
    // protected function tearDown()
    // {
    //     parent::tearDown();
    //     $dir = $this->tmp_dir . DIRECTORY_SEPARATOR. 'content.*.out';
    //     array_map('unlink', glob($dir));
    // }
    // 
    // public function testGetStack()
    // {
    //     $this->assertInstanceOf('\Aura\Http\Request\ResponseStack', $this->builder->getStack());
    // }
    // 
    // public function testGetStackClosesFileHandle()
    // {
    //     $file = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'cookiejar';
    //     $this->builder->setFileHandle(fopen($file, 'r'));
    // 
    //     $this->builder->getStack();
    //     
    //     $this->assertNull($this->builder->getFileHandle());
    // }
    // 
    // public function testSaveContentCallback()
    // {
    //     $this->builder->saveContentCallback(null, 'hello', false);
    //     $this->assertEquals('hello', $this->response->getContent());
    // 
    //     $this->builder->saveContentCallback(null, ' world', false);
    //     $this->assertEquals('hello world', $this->response->getContent());
    // }
    // 
    // public function testSaveContentToFileWithoutContentDispositionHeader()
    // {
    //     $this->builder->saveContentCallback(null, 'hello', $this->tmp_dir);
    // 
    //     $content = $this->response->getContent();
    // 
    //     $this->assertTrue(is_resource($content));
    //     $this->assertEquals('hello', fread($content, 8192));
    // 
    //     $this->builder->saveContentCallback(null, ' world', $this->tmp_dir);
    // 
    //     $content = $this->response->getContent();
    // 
    //     $this->assertTrue(is_resource($content));
    //     $this->assertEquals('hello world', fread($content, 8192));
    //     
    //     fclose($content);
    // }
    // 
    // public function testSaveContentToFile()
    // {
    //     $this->response->headers->set('Content-Disposition', 'attachment; filename="content.testing.out";');
    // 
    //     $this->builder->saveContentCallback(null, 'hello', $this->tmp_dir);
    // 
    //     $content = $this->response->getContent();
    // 
    //     $this->assertTrue(is_resource($content));
    //     $this->assertEquals('hello', fread($content, 8192));
    // 
    //     $this->builder->saveContentCallback(null, ' world', $this->tmp_dir);
    // 
    //     $content = $this->response->getContent();
    // 
    //     $this->assertTrue(is_resource($content));
    //     $this->assertEquals('hello world', fread($content, 8192));
    //     
    //     $this->assertTrue(file_exists($this->tmp_dir . DIRECTORY_SEPARATOR . 'content.testing.out'));
    // 
    //     fclose($content);
    // }
    // 
    // public function testSaveHeaderBlankHeader()
    // {
    //     $this->assertEquals(2, $this->builder->saveHeaderCallback(null, '  '));
    // }
    // 
    // public function testSaveHeaderCallback()
    // {
    //     $headers = [
    //         22 => "HTTP/1.1 404 Not Found",
    //         35 => "Date: Thu, 23 Feb 2012 15:49:40 GMT",
    //         47 => "Server: Apache/2.2.20 (Unix) DAV/2 PHP/5.4.0RC3",
    //         19 => "Content-Length: 493",
    //         17 => "Connection: close",
    //         43 => "Content-Type: text/html; charset=iso-8859-1",
    //         85 => 'Set-Cookie: name=value; expires=42; path=/path; domain=.example.com; secure; HttpOnly'
    //     ];
    // 
    //     foreach ($headers as $length => $header) {
    //         $actual = $this->builder->saveHeaderCallback(null, $header);
    //         $this->assertEquals($length, $actual);
    //     }
    // 
    //     // should be a clone
    //     $this->assertNotEquals($this->response, $this->builder->getResponse());
    // 
    //     $response = $this->builder->getResponse();
    // 
    //     $this->assertEquals(substr($headers[85], 12), $response->cookies->name->toString());
    //     $this->assertEquals(1, count($response->cookies));
    // 
    //     $this->assertEquals(404, $response->getStatusCode());
    //     $this->assertEquals('Not Found', $response->getStatusText());
    // 
    //     $expect = [
    //         'Date'           => 'Thu, 23 Feb 2012 15:49:40 GMT',
    //         'Server'         => 'Apache/2.2.20 (Unix) DAV/2 PHP/5.4.0RC3',
    //         'Content-Length' => '493',
    //         'Connection'     => 'close',
    //         'Content-Type'   => 'text/html; charset=iso-8859-1'
    //     ];
    //     
    //     $this->assertEquals(5, count($response->headers));
    // 
    //     foreach ($expect as $label => $value) {
    //         $this->assertEquals($label, $response->headers->$label->getLabel());
    //         $this->assertEquals($value, $response->headers->$label->getValue());
    //     }
    // }
}