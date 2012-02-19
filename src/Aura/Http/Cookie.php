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
 * A class representing a single cookie.
 * 
 * @package Aura.Http
 * 
 */
class Cookie
{
    /**
     * 
     * @var string Cookie name.
     * 
     */
    protected $name;

    /**
     * 
     * @var string Cookie value.
     * 
     */
    protected $value;

    /**
     * 
     * @var string Cookie expires date in unix epoch seconds.
     * 
     */
    protected $expires;


    /**
     * 
     * @var string Cookie path.
     * 
     */
    protected $path;

    /**
     * 
     * @var string Cookie domain.
     * 
     */
    protected $domain;

    /**
     * 
     * @var boolean Use SSL only
     * 
     */
    protected $secure;

    /**
     * 
     * @var boolean Use HTTP only.
     * 
     */
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
        $this->expires  = $this->isValidTimeStamp($expire) ? 
                                    $expire : strtotime($expire);
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
    }

    /**
     * 
     * Magic get.
     * 
     */
    public function __get($key)
    {
        if ('expire' == $key) {
            return $this->expires;
        }

        return $this->$key;
    }

    /**
     *
     * The properties to save when serializing this object.
     *
     * @return array
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
     * @param string $default_url The URL to use when setting the secure,
     * host and path property defaults.
     * 
     * @return void
     * 
     */
    public function setFromString($text, $default_url)
    {
        // setup defaults
        $this->setDefaults($default_url);

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
                $this->$data[0] = $this->isValidTimeStamp($data[1]) ?
                                        $data[1] : strtotime($data[1]);
                break;

            case 'path':
                $this->$data[0] = $data[1];
                break;

            case 'domain':
                // prefix the domain with a dot to be consistent with Curl
                $this->$data[0] = ('.' == $data[1][0]) ? $data[1] : ".{$data[1]}";
                break;
            
            // true/false values
            case 'secure':
            case 'httponly':
                $this->$data[0] = true;
                break;
            }
        }
    }

    /**
     * 
     * Get the cookie name.
     * 
     * @return string
     * 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * Get the cookie value.
     * 
     * @return string
     * 
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * 
     * Get the cookie expires date.
     * 
     * @return string
     * 
     */
    public function getExpire()
    {
        return $this->expires;
    }
    
    /**
     * 
     * Get the cookie path.
     * 
     * @return string
     * 
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * 
     * Get the cookie domain.
     * 
     * @return string
     * 
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * 
     * Use SSL only.
     * 
     * @return string
     * 
     */
    public function getSecure()
    {
        return $this->secure;
    }
    
    /**
     * 
     * Use HTTP only.
     * 
     * @return string
     * 
     */
    public function getHttpOnly()
    {
        return $this->httponly;
    }

    /**
     * 
     * Match a $scheme, $domain and $path to this cookie object.
     *
     * @param string $scheme
     *
     * @param string $domain
     *
     * @param string $path
     *
     * @return boolean
     *
     */
    public function isMatch($scheme, $domain, $path)
    {
        if (('https' == $scheme && ! $this->secure) ||
            ('http'  == $scheme &&   $this->secure)) {
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
     * Has this cookie expired
     *
     * @param boolean $expire_session_cookies Expire Session cookies.
     *
     * @return boolean
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
    
    /**
     * 
     * Return the cookie in the Set-Cookie format.
     * 
     * @return string
     * 
     */
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

    /**
     * 
     * Return the cookie in the Set-Cookie format.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     *
     * Try to match a $domain to this cookies domain.
     * 
     * @param string $domain
     *
     * @return boolean
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

    /**
     * 
     * Check a siring to see if it could be a unix time stamp.
     * 
     * @param string $timestamp
     * 
     * @return boolean
     * 
     * @see http://stackoverflow.com/questions/2524680/check-whether-the-string-is-a-unix-timestamp
     * 
     */
    protected function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp) 
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    protected function setDefaults($default_url)
    {
        $this->httponly = false;
        $this->path     = '/';
        $this->expires  = '0';

        $defaults     = parse_url($default_url);
        $this->secure = (isset($defaults['scheme']) && 
                         'https' == $defaults['scheme']);
        $this->domain = isset($defaults['host']) ? $defaults['host'] : null;

        if (isset($defaults['path'])) { 
            $this->path = substr($defaults['path'], 0,
                                strrpos($defaults['path'], '/') + 1);
        }
    }
    
}