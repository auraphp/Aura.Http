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
 * @package aura.http
 * 
 */
class Cookies
{
    protected $list = array();
    
    // extract to a Cookie struct, and probably a CookieFactory
    protected $base = array(
        'value'    => null,
        'expire'   => null,
        'path'     => null,
        'domain'   => null,
        'secure'   => false,
        'httponly' => true,
    );
    
    public function set($name, array $info = array())
    {
        $info = array_merge($this->base, $info);
        settype($info['expire'],   'int');
        settype($info['secure'],   'bool');
        settype($info['httponly'], 'bool');
        $this->list[$name] = $info;
    }
    
    public function getAll()
    {
        return $this->list;
    }
    
    public function setAll(array $cookies = array())
    {
        $this->list = array();
        foreach ($cookies as $name => $info) {
            $this->set($name, $info);
        }
    }
    
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
