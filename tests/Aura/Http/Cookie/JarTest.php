<?php
namespace Aura\Http\Request;

use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Cookie\Jar as CookieJar;

class CookieJarTest extends \PHPUnit_Framework_TestCase
{
    protected $cookiejar;
    
    protected $cookiefactory;

    protected function setUp()
    {
        parent::setUp();
        $this->cookiejar     = new CookieJar(new CookieFactory);
        $this->cookiefactory = new CookieFactory;
    }
    
    protected function file($file)
    {
        return dirname(__DIR__)
              . DIRECTORY_SEPARATOR . '_files'
              . DIRECTORY_SEPARATOR . $file;
    }
    
    public function testOpening()
    {
        $return = $this->cookiejar->open($this->file('cookiejar'));
        
        $list   = $this->cookiejar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookiefactory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookiefactory->newInstance('bar', [
                    'value'    => 'foo',
                    'expire'   => '1645033667',
                    'path'     => '/path',
                    'domain'   => '.example.com',
                    'secure'   => true,
                    'httponly' => true,
                ]),
        ];

        $this->assertTrue($return);
        $this->assertEquals($expect, $list);
    }

    public function testOpeningMorethanOnceReturnFalse()
    {
        $return = $this->cookiejar->open($this->file('cookiejar'));
        
        $this->assertTrue($return);

        $return = $this->cookiejar->open($this->file('cookiejar'));

        $this->assertFalse($return);
    }

    public function testMalformedLineIsIgnored()
    {
        $this->cookiejar->open($this->file('cookiejar_with_malformed_line'));
        
        $list   = $this->cookiejar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookiefactory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookiefactory->newInstance('bar', [
                    'value'    => 'foo',
                    'expire'   => '1645033667',
                    'path'     => '/path',
                    'domain'   => '.example.com',
                    'secure'   => true,
                    'httponly' => true,
                ]),
        ];

        $this->assertEquals($expect, $list);
    }

    public function testSaving()
    {
        $this->cookiejar->add($this->cookiefactory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]));
        $this->cookiejar->add($this->cookiefactory->newInstance('bar', [
                    'value'    => 'foo',
                    'expire'   => '1645033667',
                    'path'     => '/path',
                    'domain'   => '.example.com',
                    'secure'   => true,
                    'httponly' => true,
                ]));

        $test     = $this->file('cookiejar_savetest');
        $expected = $this->file('cookiejar');
        $return   = $this->cookiejar->save($test);

        $this->assertTrue($return);
        $this->assertEquals(file_get_contents($expected), file_get_contents($test));
        unlink($test);
    }

    public function testSavingWithNoCookiesReturnFalse()
    {
        $test     = $this->file('cookiejar_savetest');
        $return   = $this->cookiejar->save($test);

        $this->assertFalse($return);
    }

    public function testListingAllThatMatch()
    {
        $this->cookiejar->open($this->file('cookiejar'));
        
        $list   = $this->cookiejar->listAll('http://www.example.com/');
        $expect = [
            'foowww.example.com/' => $this->cookiefactory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
        ];

        $this->assertEquals($expect, $list);
    }

    public function testListingAllWithoutSchemeOnMatchingUrlException()
    {
        $this->setExpectedException('\Aura\Http\Exception');
        $this->cookiejar->open($this->file('cookiejar'));
        
        $this->cookiejar->listAll('www.example.com');
    }

    public function testExpiredCookiesAreNotSaved()
    {
        $this->cookiejar->open($this->file('cookiejar_with_expired_cookie'));

        $expected = $this->file('cookiejar');
        $test     = $this->file('cookiejar_savetest2');
        $list     = $this->cookiejar->save($test);

        $this->assertEquals(file_get_contents($expected), file_get_contents($test));
        unlink($test);
    }

    public function testExpiringSessionCookies()
    {
        $this->cookiejar->expireSessionCookies();
        $this->cookiejar->open($this->file('cookiejar_with_session_cookie'));

        $list   = $this->cookiejar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookiefactory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookiefactory->newInstance('bar', [
                    'value'    => 'foo',
                    'expire'   => '1645033667',
                    'path'     => '/path',
                    'domain'   => '.example.com',
                    'secure'   => true,
                    'httponly' => true,
                ]),
        ];

        $this->assertEquals($expect, $list);
    }
}