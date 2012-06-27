<?php
namespace Aura\Http\Request;

use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Cookie\Jar as CookieJar;
use Aura\Http\Cookie\JarFactory as CookieJarFactory;

class CookieJarTest extends \PHPUnit_Framework_TestCase
{
    protected $jar_factory;
    
    protected $cookie_factory;
    
    protected function setUp()
    {
        parent::setUp();
        $this->cookie_factory = new CookieFactory;
        $this->jar_factory = new CookieJarFactory;
    }
    
    protected function newJar($file)
    {
        return $this->jar_factory->newInstance($this->file($file));
    }
    
    protected function file($file)
    {
        return dirname(__DIR__)
              . DIRECTORY_SEPARATOR . '_files'
              . DIRECTORY_SEPARATOR . $file;
    }
    
    public function testOpening()
    {
        $jar = $this->newJar('cookiejar');
        
        $return = $jar->open();
        $this->assertTrue($return);
        
        $list   = $jar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookie_factory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookie_factory->newInstance('bar', [
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

    public function testOpeningMorethanOnceReturnFalse()
    {
        $jar = $this->newJar('cookiejar');
        
        $return = $jar->open();
        $this->assertTrue($return);

        $return = $jar->open();
        $this->assertFalse($return);
    }

    public function testMalformedLineIsIgnored()
    {
        $jar = $this->newJar('cookiejar_with_malformed_line');
        
        $jar->open();
        
        $list   = $jar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookie_factory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookie_factory->newInstance('bar', [
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
        $jar = $this->newJar('cookiejar_savetest');
        
        $jar->add($this->cookie_factory->newInstance('foo', [
            'value'    => 'bar',
            'expire'   => '1645033667',
            'path'     => '/',
            'domain'   => 'www.example.com',
            'secure'   => false,
            'httponly' => false,
        ]));
                
        $jar->add($this->cookie_factory->newInstance('bar', [
            'value'    => 'foo',
            'expire'   => '1645033667',
            'path'     => '/path',
            'domain'   => '.example.com',
            'secure'   => true,
            'httponly' => true,
        ]));

        $return   = $jar->save();
        $this->assertTrue($return);
        
        $expect = file_get_contents($this->file('cookiejar'));
        $actual = file_get_contents($this->file('cookiejar_savetest'));
        $this->assertEquals($expect, $actual);
        
        unlink($this->file('cookiejar_savetest'));
    }

    public function testSavingWithNoCookiesReturnFalse()
    {
        $jar = $this->newJar('cookiejar_savetest');
        $return = $jar->save();
        $this->assertFalse($return);
    }

    public function testListingAllThatMatch()
    {
        $jar = $this->newJar('cookiejar');
        $jar->open();
        
        $list   = $jar->listAll('http://www.example.com/');
        $expect = [
            'foowww.example.com/' => $this->cookie_factory->newInstance('foo', [
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
        $jar = $this->newJar('cookiejar');
        $jar->open();
        $this->setExpectedException('\Aura\Http\Exception');
        $jar->listAll('www.example.com');
    }

    // public function testExpiredCookiesAreNotSaved()
    // {
    //     // opens one file ...
    //     $this->cookiejar->open($this->file('cookiejar_with_expired_cookie'));
    // 
    //     $expected = $this->file('cookiejar');
    //     
    //     /// but saves another
    //     $test     = $this->file('cookiejar_savetest2');
    //     $list     = $this->cookiejar->save($test);
    //     
    //     $this->assertEquals(file_get_contents($expected), file_get_contents($test));
    //     unlink($test);
    // }
    
    public function testExpiringSessionCookies()
    {
        $jar = $this->newJar('cookiejar_with_session_cookie');
        $jar->expireSessionCookies();
        $jar->open();
        
        $list   = $jar->listAll();
        $expect = [
            'foowww.example.com/' => $this->cookie_factory->newInstance('foo', [
                    'value'    => 'bar',
                    'expire'   => '1645033667',
                    'path'     => '/',
                    'domain'   => 'www.example.com',
                    'secure'   => false,
                    'httponly' => false,
                ]),
            'bar.example.com/path' => $this->cookie_factory->newInstance('bar', [
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