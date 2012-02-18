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
        $httponly = null)
    {
        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function test__get()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', false, true);

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals(42,             $cookie->expires);
        $this->assertEquals('/path',        $cookie->path);
        $this->assertEquals('.example.com', $cookie->domain);

        $this->assertFalse($cookie->secure);
        $this->assertTrue($cookie->httponly);
    }

    public function test__sleep()
    {
        $org_cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', false, true);
        
        $cookie = serialize($org_cookie);
        $cookie = unserialize($cookie);

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals(42,             $cookie->expires);
        $this->assertEquals('/path',        $cookie->path);
        $this->assertEquals('.example.com', $cookie->domain);

        $this->assertFalse($cookie->secure);
        $this->assertTrue($cookie->httponly);
    }

    public function testSetFromString()
    {
        $cookie = $this->newCookie();
        $cookie->setFromString('cname=cvalue; expires=42; path=/path; domain=.example.com; Secure; HttpOnly');

        $this->assertEquals('cname',        $cookie->name);
        $this->assertEquals('cvalue',       $cookie->value);
        $this->assertEquals(42,             $cookie->expire);
        $this->assertEquals(42,             $cookie->expires);
        $this->assertEquals('/path',        $cookie->path);
        $this->assertEquals('.example.com', $cookie->domain);

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

    public function testToString()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com', true, true);

        $this->assertEquals('cname=cvalue; expires=42; path=/path; domain=.example.com; secure; HttpOnly', $cookie->toString());
    }

    public function test__toString()
    {
        $cookie = $this->newCookie('cname', 'cvalue');

        $this->assertEquals('cname=cvalue', $cookie->__toString());
    }

    public function testIsMatch()
    {
        $cookie = $this->newCookie('cname', 'cvalue', 42, '/path', '.example.com');

        $this->assertTrue($cookie->isMatch('http', 'www.example.com', '/path'));
        $this->assertFalse($cookie->isMatch('http', 'www.example2.com', '/path'));
        $this->assertFalse($cookie->isMatch('https', 'www.example.com', '/path'));
        $this->assertFalse($cookie->isMatch('http', 'www.example.com', '/newpath'));
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