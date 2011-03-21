<?php

namespace aura\http;

class AbstractResponseTest extends \PHPUnit_Framework_TestCase
{
    protected function newAbstractResponse()
    {
        return $this->getMockForAbstractClass('\aura\http\AbstractResponse', 
            array(new MimeUtility()));
    }

    public function test__get()
    {
        $resp = $this->newAbstractResponse();
        
        // test that we can access without causing an exception
        $resp->content;
        $resp->header;
        $resp->cookie;
        $resp->version;
        $resp->status_code;
        $resp->status_text;
        
        // invalid or protected should cause an exception
        $this->setExpectedException('\LogicException');
        $resp->invalid;
    }

    public function test__set()
    {
        $resp = $this->newAbstractResponse();
        
        // test that we can access without causing an exception
        $resp->content     = 'Hi';
        $resp->version     = '1.1';
        $resp->status_code = 201;
        $resp->status_text = 'Status';
        
        // invalid or protected should cause an exception
        $this->setExpectedException('\LogicException');
        $resp->invalid = 'xxx';
    }

    public function testSetVersion()
    {
        $resp   = $this->newAbstractResponse();
        $resp->setVersion('1.0');
        $actual = $resp->getVersion();
        $this->assertSame('1.0', $actual);
        
        $resp->version = '1.0';
        $actual = $resp->getVersion();
        $this->assertSame('1.0', $actual);
    }

    public function testSetVersionExceptionOnInvalidVersion()
    {
        $this->setExpectedException('\LogicException');
        $resp   = $this->newAbstractResponse();
        $resp->setVersion('2.0');
    }

    public function testGetVersion()
    {
        $resp   = $this->newAbstractResponse();
        $actual = $resp->getVersion();
        // 1.1 is default
        $this->assertSame('1.1', $actual);
        
        $actual = $resp->version;
        $this->assertSame('1.1', $actual);
    }

    public function testSetStatusCode()
    {
        $resp   = $this->newAbstractResponse();
        $resp->setStatusCode(101);
        $actual = $resp->getStatusCode();
        $this->assertSame(101, $actual);
        
        $resp->status_code = 102;
        $actual = $resp->getStatusCode();
        $this->assertSame(102, $actual);
    }

    public function testSetStatusCodeExceptionLessThan100()
    {
        $this->setExpectedException('\LogicException');
        $resp   = $this->newAbstractResponse();
        $resp->setStatusCode(99);
    }

    public function testSetStatusCodeExceptionGreaterThan599()
    {
        $this->setExpectedException('\LogicException');
        $resp   = $this->newAbstractResponse();
        $resp->setStatusCode(600);
    }

    public function testSetStatusText()
    {
        $resp   = $this->newAbstractResponse();
        $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
        
        $resp   = $this->newAbstractResponse();
        $resp->status_text = "I'm a teapot";
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
    }

    public function testGetStatusCode()
    {
        $resp   = $this->newAbstractResponse();
        $actual = $resp->getStatusCode();
        // 200 is default
        $this->assertSame(200, $actual);
        
        $actual = $resp->status_code;
        $this->assertSame(200, $actual);
    }

    public function testGetStatusText()
    {
        $resp   = $this->newAbstractResponse();
        $actual = $resp->getStatusText();
        // null is default
        $this->assertNull($actual);
        
        $resp->setStatusText("I'm a teapot");
        $actual = $resp->getStatusText();
        $this->assertSame("I'm a teapot", $actual);
        
        $actual = $resp->status_text;
        $this->assertSame("I'm a teapot", $actual);
    }

    public function testSetHeader()
    {
        $resp     = $this->newAbstractResponse();
        $resp->setHeader('foo_bar', 'foobar header');
        
        $actual   = $this->readAttribute($resp, 'headers');
        $expected = array(
            'Foo-Bar' => 'foobar header'
        );
        $this->assertEquals($expected, $actual);
    }
    
    public function testSetHeaderReplace()
    {
        $resp     = $this->newAbstractResponse();
        $resp->setHeader('foo_bar', 'foobar header');
        $resp->setHeader('foo_bar', 'new foobar header');
        
        $actual   = $this->readAttribute($resp, 'headers');
        $expected = array(
            'Foo-Bar' => 'new foobar header'
        );
        $this->assertEquals($expected, $actual);
    }
    
    public function testSetHeaderSameHeaderMultipleTimes()
    {
        $resp     = $this->newAbstractResponse();
        $resp->setHeader('foo_bar', 'foobar header',     false);
        $resp->setHeader('foo_bar', 'another foobar header', false);
        
        $actual   = $this->readAttribute($resp, 'headers');
        $expected = array(
            'Foo-Bar' => array('foobar header', 'another foobar header')
        );
        $this->assertEquals($expected, $actual);
    }
    
    public function testSetHeaderExceptionWhenSettingHttpHeader()
    {
        $this->setExpectedException('\LogicException');
        $resp   = $this->newAbstractResponse();
        $resp->setHeader('Http', 'dont set here');
    }

    public function testGetHeader()
    {
        $resp   = $this->newAbstractResponse();
        $actual = $resp->getHeader('does-not-exist');
        // null is default if header does not exist
        $this->assertNull($actual);
        
        $resp->setHeader('foo_bar', 'foobar header');
        $actual = $resp->getHeader('Foo-Bar');
        $this->assertSame('foobar header', $actual);
        
        $resp->setHeader('foo_bar', 'foobar header 2.0');
        $actual = $resp->header['Foo-Bar'];
        $this->assertSame('foobar header 2.0', $actual);
    }
    
    public function testGetAllHeaders()
    {
        $resp     = $this->newAbstractResponse();
        $resp->setHeader('foo', 'foo header');
        $resp->setHeader('bar', 'bar header');
        
        $actual   = $resp->getHeader(null);
        $expected = array(
            'Foo' => 'foo header',
            'Bar' => 'bar header'
        );
        $this->assertEquals($expected, $actual);
        
        $actual = $resp->header;
        $this->assertEquals($expected, $actual);
    }

    public function testSetContent()
    {
        $resp   = $this->newAbstractResponse();
        $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
        
        $resp   = $this->newAbstractResponse();
        $resp->content = 'Hello World!';
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
    }

    public function testGetContent()
    {
        $resp   = $this->newAbstractResponse();
        $actual = $resp->getContent();
        // null is default
        $this->assertNull($actual);
        
        $resp->setContent('Hello World!');
        $actual = $resp->getContent();
        $this->assertSame('Hello World!', $actual);
        
        $actual = $resp->content;
        $this->assertSame('Hello World!', $actual);
    }

    public function testSetCookie()
    {
        $resp     = $this->newAbstractResponse();
        $resp->setCookie('login', '1234567890');
        
        $actual   = $this->readAttribute($resp, 'cookies');
        $expected = array(
            'login' => array(
                'value'     => '1234567890',
                'expires'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            )
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetCookie()
    {
        $resp     = $this->newAbstractResponse();
        $actual   = $resp->getCookie('does-not-exist');
        $this->assertNull($actual);
        
        $resp->setCookie('login', '1234567890');
        $actual   = $resp->getCookie('login');
        $expected = array(
            'value'     => '1234567890',
            'expires'   => 0,
            'path'      => '',
            'domain'    => '',
            'secure'    => false,
            'httponly'  => null,
        );
        $this->assertEquals($expected, $actual);
        
        $actual = $resp->cookie['login'];
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllCookies()
    {
        $resp     = $this->newAbstractResponse();
        
        $resp->setCookie('login', '1234567890');
        $resp->setCookie('usrid', '0987654321');
        
        $actual   = $resp->getCookie(null);
        $expected = array(
            'login' => array(
                'value'     => '1234567890',
                'expires'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            ),
            'usrid' => array(
                'value'     => '0987654321',
                'expires'   => 0,
                'path'      => '',
                'domain'    => '',
                'secure'    => false,
                'httponly'  => null,
            )
        );
        $this->assertEquals($expected, $actual);
        
        $actual = $resp->cookie;
        $this->assertEquals($expected, $actual);
    }
}