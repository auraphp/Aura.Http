<?php
namespace Aura\Http;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    protected function newCookie(
        $name     = null, 
        $value    = null, 
        $expire   = null, 
        $path     = null,  
        $domain   = null, 
        $secure   = null, 
        $httponly = null
    ) {
        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function test__get()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', false, true);

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals('/path',        $cookie->path);
        $this->assertEquals('.example.com', $cookie->domain);

        $this->assertFalse($cookie->secure);
        $this->assertTrue($cookie->httponly);
    }

    public function testSetFromHeader()
    {
        $cookie = $this->newCookie();
        $cookie->setFromHeader('cname=cvalue; expires=42; path=/path; domain=.example.com; Secure; HttpOnly',
                               'https://example.com/path/');

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals('/path',        $cookie->path);
        $this->assertEquals('.example.com', $cookie->domain);

        $this->assertTrue($cookie->secure);
        $this->assertTrue($cookie->httponly);
    }

    public function testSetFromJar()
    {
        $lines = [
            // correct
            "www.example.com\tFALSE\t/\tFALSE\t1645033667\tfoo\tbar",
            // correct, httponly
            "#HttpOnly_.example.com\tTRUE\t/path\tTRUE\t1645033667\tbar\tfoo",
            // malformed, only 6 parts
            "#HttpOnly_.example.com	TRUE	/	FALSE	1645033667	foo",
        ];
        
        $cookie = $this->newCookie();
        $cookie->setFromJar($lines[0]);
        $this->assertSame(false, $cookie->httponly);
        $this->assertSame('www.example.com', $cookie->domain);
        $this->assertSame('/', $cookie->path);
        $this->assertSame(false, $cookie->secure); 
        $this->assertSame('1645033667', $cookie->expire); 
        $this->assertSame('foo', $cookie->name);
        $this->assertSame('bar', $cookie->value);
        
        $cookie = $this->newCookie();
        $cookie->setFromJar($lines[1]);
        $this->assertSame(true, $cookie->httponly);
        $this->assertSame('.example.com', $cookie->domain);
        $this->assertSame('/path', $cookie->path);
        $this->assertSame(true, $cookie->secure); 
        $this->assertSame('1645033667', $cookie->expire); 
        $this->assertSame('bar', $cookie->name);
        $this->assertSame('foo', $cookie->value);
        
        // malformed line
        $cookie = $this->newCookie();
        $this->setExpectedException('Aura\Http\Exception\MalformedCookie');
        $cookie->setFromJar($lines[2]);
    }
    
    public function testToJarString()
    {
        // not httponly, not secure
        $cookie = $this->newCookie(
            'foo',
            'bar',
            '1645033667',
            '/',
            'www.example.com',
            false,
            false
        );
        
        $actual = $cookie->toJarString();
        $expect = "www.example.com\tFALSE\t/\tFALSE\t1645033667\tfoo\tbar";
        $this->assertSame($expect, $actual);
        
        // httponly, secure
        $cookie = $this->newCookie(
            'bar',
            'foo',
            '1645033667',
            '/path',
            '.example.com',
            true,
            true
        );
        
        $actual = $cookie->toJarString();
        $expect = "#HttpOnly_.example.com\tTRUE\t/path\tTRUE\t1645033667\tbar\tfoo";
        $this->assertSame($expect, $actual);
    }
    
    public function testsetFromHeaderUsingDefault()
    {
        $cookie = $this->newCookie();
        $cookie->setFromHeader('cname=cvalue; expires=42; HttpOnly', 'https://example.com/path/');

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals('/path/',       $cookie->path);
        $this->assertEquals('example.com',  $cookie->domain);

        $this->assertTrue($cookie->secure);
        $this->assertTrue($cookie->httponly);
    }

    public function testGetName()
    {
        $cookie = $this->newCookie('cname');

        $this->assertEquals('cname', $cookie->getName());
    }

    public function testGetValue()
    {
        $cookie = $this->newCookie('cname', 'cvalue');

        $this->assertEquals('cvalue', $cookie->getValue());
    }

    public function testGetExpire()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42);

        $this->assertEquals(42, $cookie->getExpire());
    }

    public function testGetPath()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path');

        $this->assertEquals('/path', $cookie->getPath());
    }

    public function testGetDomain()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com');

        $this->assertEquals('.example.com', $cookie->getDomain());
    }

    public function testGetSecure()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', false);

        $this->assertFalse($cookie->getSecure());
    }

    public function testGetHttpOnly()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', false, true);

        $this->assertTrue($cookie->getHttpOnly());
    }

    public function test__toString()
    {
        $cookie = $this->newCookie('cname', 'cvalue');

        $this->assertEquals('cname=cvalue', $cookie->toRequestHeaderString());
    }

    public function testIsMatch()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com');

        $this->assertTrue($cookie->isMatch('http', 'www.example.com', '/path'));
        $this->assertFalse($cookie->isMatch('http', 'www.example2.com', '/path'));
        $this->assertFalse($cookie->isMatch('https', 'www.example.com', '/path'));
        $this->assertFalse($cookie->isMatch('http', 'www.example.com', '/newpath'));
    }

    public function testIsMatchWithoutDomainIsFalse()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42);

        $this->assertFalse($cookie->isMatch('http', 'www.example.com', '/'));
    }
    
    public function testIsExpired()
    {
        $cookie = $this->newCookie('cname', 'cvalue', time() + 1, '/path', '.example.com');

        $this->assertFalse($cookie->isExpired());
        sleep(2);
        $this->assertTrue($cookie->isExpired());
    }
    
    public function testIsExpiredSessionCookie()
    {
        $cookie = $this->newCookie('cname', 'cvalue');

        $this->assertFalse($cookie->isExpired());
        $this->assertTrue($cookie->isExpired(true));
    }
}