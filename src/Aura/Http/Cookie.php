<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * 
 * 
 * @package Aura.Http
 * 
 */
class Cookie
{
    protected $name;
    protected $value;
    protected $expires;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httponly;

    public function __construct(
        $name, 
        $value, 
        $expire, 
        $path, 
        $domain, 
        $secure, 
        $httponly)
    {
        $this->name     = $name;
        $this->value    = $value;
        $this->expires  = $expire;
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
    }

    public function __get($key)
    {
        if ('expire' == $key) {
            return $this->expires;
        }

        return $this->$key;
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    public function __sleep()
    {
        return ['name',   'value',  'expires', 'path', 
                'domain', 'secure', 'httponly'];
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
    public function setFromString($text, $default_url = null)
    {
        // setup defaults
        if ($default_url) {
            $defaults = parse_url($default_url);
            $this->secure = (isset($defaults['scheme']) && 
                             'https' == $defaults['scheme']);
            $this->domain = isset($defaults['host']) ? $defaults['host'] : null;

            if (isset($defaults['path'])) { 
                $this->path = substr($defaults['path'], 0, 
                                     strrpos($defaults['path'], '/'));
            }
        }

        // get the list of elements
        $list = explode(';', $text);
        
        // get the name and value
        list($this->name, $this->value) = explode('=', array_shift($list));
        $this->value                    = urldecode($this->value);
        
        foreach ($list as $item) {
            $data    = explode('=', trim($item));
            $data[0] = strtolower($data[0]);
            
            switch ($data[0]) {
            // string-literal values
            case 'expires':
            case 'path':
            case 'domain':
                $this->$data[0] = $data[1];
                break;
            
            // true/false values
            case 'secure':
            case 'httponly':
                $this->$data[0] = true;
                break;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function getExpire()
    {
        return $this->expires;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getDomain()
    {
        return $this->domain;
    }
    
    public function getSecure()
    {
        return $this->secure;
    }
    
    public function getHttpOnly()
    {
        return $this->httponly;
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    public function isMatch($scheme, $domain, $path)
    {
        if ('https' == $scheme && ! $this->secure) {
            return false;
        }

        if (! $this->isDomainMatch($domain)) {
            return false;
        }

        if ($this->path && 0 === strpos($path, $this->path)) {
            // we have a match
            return true;
        }

        return false;
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    public function isExpired($expire_session_cookies = false)
    {
        if (! $this->expires && $expire_session_cookies) {
            return true;
        } else if (! $this->expires) {
            return false;
        }

        return $this->expires < time();
    }
    
    public function toString()
    {
        $cookie[] = "$this->name=$this->value";
        $parts    = ['expires', 'path', 'domain'];

        foreach($parts as $part) {
            if (! empty($this->$part)) {
                $cookie[] = $part . '=' . $this->$part;
            }
        }

        if ($this->secure) {
            $cookie[] = 'secure';
        }

        if ($this->httponly) {
            $cookie[] = 'HttpOnly';
        }

        return implode('; ', $cookie);
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    protected function isDomainMatch($domain)
    {
        $cookie_domain = strtolower($this->domain);
        $host_domain   = strtolower($domain);

        if (! $cookie_domain) {
            return false; 
        }

        if ('.' == $cookie_domain[0]) {
            $cookie_domain = substr($cookie_domain, 1);
        }

        return ($cookie_domain == $host_domain ||
                preg_match('/\.' . preg_quote($cookie_domain) . '$/', 
                           $host_domain));
    }
}