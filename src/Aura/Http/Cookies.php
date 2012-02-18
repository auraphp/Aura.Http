<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Factory\Cookie as CookieFactory;

/**
 * 
 * Collection of Cookie objects.
 * 
 * @package Aura.Http
 * 
 */
class Cookies implements \IteratorAggregate, \Countable
{
    /**
     * 
     * The list of all cookies.
     * 
     * @var array
     * 
     */
    protected $list = [];

    /**
     * 
     * @var Aura\Http\Factory\Cookie
     * 
     */
    protected $factory;

    /**
     *
     * @param Aura\Http\Factory\Cookie $factory
     *
     */
    public function __construct(CookieFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * 
     * Reset the cookie list.
     * 
     */
    public function __clone()
    {
        $this->list = [];
    }
    
    /**
     * 
     * Get a cookie.
     * 
     * @param string $key 
     * 
     * @return array
     * 
     */
    public function __get($key)
    {
        return $this->list[$key];
    }
    
    /**
     * 
     * Does a cookie exist.
     * 
     * @param string $key 
     * 
     * @return boolean
     * 
     */
    public function __isset($key)
    {
        return isset($this->list[$key]);
    }
    
    /**
     * 
     * Unset a cookie.
     * 
     * @param string $key 
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        unset($this->list[$key]);
    }
    
    /**
     * 
     * Count the number of cookies.
     * 
     * @return integer
     * 
     */
    public function count()
    {
        return count($this->list);
    }
    
    /** 
     * 
     * Gets all cookies as an iterator.
     * 
     * @return array
     * 
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }
    
    /**
     * 
     * Sets a single cookie by name.
     * 
     * @param string $name The cookie name.
     * 
     * @param array $info The cookie info.
     * 
     */
    public function set($name, array $info = [])
    {
        if ($name instanceof Cookie) {
            $cookie = $name;
        } else {
            $cookie = $this->factory->newInstance($name, $info);
        }

        $this->list[$cookie->getName()] = $cookie;
    }

    /**
     * 
     * Parses the value of the "Set-Cookie" header and sets it.
     * 
     * @param string $text The Set-Cookie text string value.
     * 
     * @return void
     * 
     */
    public function setFromString($str, $default_url = null)
    {
        $cookie = $this->factory->newInstance();
        $cookie->setFromString($str, $default_url);

        $this->list[$cookie->getName()] = $cookie;
    }
    
    /** 
     * 
     * Gets all cookies.
     * 
     * @return array
     * 
     */
    public function getAll()
    {
        return $this->list;
    }
    
    /**
     * 
     * Sets all cookies at once removing all previous cookies.
     * 
     * @param array $cookies The array of all cookies where the key is the
     * name and the value is the array of cookie info.
     * 
     * @return void
     * 
     */
    public function setAll(array $cookies = [])
    {
        $this->list = [];
        foreach ($cookies as $name => $info) {
            $this->set($name, $info);
        }
    }
    
    /**
     * 
     * Sends the cookies using `setcookie()`.
     * 
     * @return void
     * 
     */
    public function send()
    {
        foreach ($this->list as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }
    }
}
