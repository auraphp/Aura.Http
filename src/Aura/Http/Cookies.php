<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * Collection of non-cookie HTTP headers.
 * 
 * @package Aura.Http
 * 
 */
class Cookies
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
     * Base values for a single cookie.
     * 
     * @todo Extract to a Cookie struct, and probably a CookieFactory.
     * 
     * @var array
     * 
     */
    protected $base = [
        'value'    => null,
        'expire'   => null,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    ];
    
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
        $info = array_merge($this->base, $info);
        settype($info['expire'],   'int');
        settype($info['secure'],   'bool');
        settype($info['httponly'], 'bool');
        $this->list[$name] = $info;
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
     * Sets all cookies at once.
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
        foreach ($this->list as $name => $info) {
            setcookie(
                $name,
                $info['value'],
                $info['expire'],
                $info['path'],
                $info['domain'],
                $info['secure'],
                $info['httponly']
            );
        }
    }
}
