<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Exception;

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
     * Cookie name.
     * 
     * @var string
     * 
     */
    protected $name;

    /**
     * 
     * Cookie value.
     * 
     * @var string
     * 
     */
    protected $value;

    /**
     * 
     * Cookie expiration date in unix epoch seconds.
     * 
     * @var string
     * 
     */
    protected $expire;

    /**
     * 
     * Cookie path.
     * 
     * @var string
     * 
     */
    protected $path;

    /**
     * 
     * Cookie domain.
     * 
     * @var string
     * 
     */
    protected $domain;

    /**
     * 
     * Use SSL only.
     * 
     * @var boolean
     * 
     */
    protected $secure;

    /**
     * 
     * Use HTTP only.
     * 
     * @var boolean
     * 
     */
    protected $httponly;

    /**
     * 
     * Constructor.
     * 
     * @param string $name The cookie name.
     * 
     * @param string $value The cookie value.
     * 
     * @param string $expire The expiration time in Unix epoch seconds.
     * 
     * @param string $path The cookie path.
     * 
     * @param string $domain The cookie domain.
     * 
     * @param bool $secure Use SSL only?
     * 
     * @param type $httponly Use HTTP only?
     * 
     */
    public function __construct(
        $name,
        $value,
        $expire,
        $path,
        $domain,
        $secure,
        $httponly
    ) {
        $this->name     = $name;
        $this->value    = $value;
        $this->setExpire($expire);
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;

        if (! empty($expire)) {
            $this->expire = $this->isValidTimeStamp($expire)
                          ? $expire
                          : strtotime($expire);
        }
    }

    /**
     * 
     * Sets the $expire value on the cookie.
     * 
     * @param mixed $expire The expiration time.
     * 
     * @return void
     * 
     */
    public function setExpire($expire)
    {
        $this->expire = null;
        if ($expire !== null) {
            $this->expire = $this->isValidTimeStamp($expire)
                          ? $expire
                          : strtotime($expire);
        }
    }

    /**
     * 
     * Magic get to return property values.
     * 
     * @param string $key The property name.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * 
     * Parses the value of a "Set-Cookie" header and sets the cookie from it.
     * 
     * @param string $text The Set-Cookie text string value.
     * 
     * @param string $default_url The URL to use when setting the secure,
     * host and path property defaults.
     * 
     * @return void
     * 
     */
    public function setFromHeader($text, $default_url)
    {
        // setup defaults
        $this->httponly = false;
        $this->path     = '/';
        $this->expire  = '0';

        $defaults     = parse_url($default_url);
        $this->secure = (isset($defaults['scheme']) &&
                         'https' == $defaults['scheme']);
        $this->domain = isset($defaults['host']) ? $defaults['host'] : null;

        if (isset($defaults['path'])) {
            $this->path = substr(
                $defaults['path'],
                0,
                strrpos($defaults['path'], '/') + 1
            );
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
                case 'expires':
                    $this->expire = $this->isValidTimeStamp($data[1])
                                  ? $data[1]
                                  : strtotime($data[1]);
                    break;
                case 'path':
                    $this->path = $data[1];
                    break;
                case 'domain':
                    // prefix the domain with a dot to be consistent with Curl
                    $this->domain = ('.' == $data[1][0])
                                  ? $data[1]
                                  : ".{$data[1]}";
                    break;
                case 'secure':
                    $this->secure = true;
                    break;
                case 'httponly':
                    $this->httponly = true;
                    break;
                // FIXME Don't we need a default case?
            }
        }
    }

    /**
     * 
     * Parses a cookie jar line and sets the cookie from it.
     * 
     * @param string $line The line from the cookie jar.
     * 
     * @return void
     * 
     */
    public function setFromJar($line)
    {
        $line = trim($line);
        $parts = explode("\t", $line);

        // Malformed line
        if (7 != count($parts)) {
            throw new Exception\MalformedCookie($line);
        }

        // part 0
        $this->httponly = (boolean) ('#HttpOnly_' == substr($parts[0], 0, 10));
        if ($this->httponly) {
            $this->domain = substr($parts[0], 10);
        } else {
            $this->domain = $parts[0];
        }

        // part 1 is ignored; remaining parts follow
        $this->path     = $parts[2];
        $this->secure   = ("TRUE" === $parts[3]) ? true : false;
        $this->setExpire($parts[4]);
        $this->name     = $parts[5];
        $this->value    = $parts[6];
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
     * Get the cookie expiration date.
     * 
     * @return string
     * 
     */
    public function getExpire()
    {
        return $this->expire;
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
     * @return bool
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
     * @return bool
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
     * Has this cookie expired?
     *
     * @param boolean $expire_session_cookies Expire Session cookies.
     *
     * @return boolean
     *
     */
    public function isExpired($expire_session_cookies = false)
    {
        if (! $this->expire && $expire_session_cookies) {
            return true;
        } elseif (! $this->expire) {
            // FIXME Usage of ELSE IF is discouraged; use ELSEIF instead
            return false;
        }

        return $this->expire < time();
    }

    /**
     * 
     * Converts this cookie to a line for a cookie jar.
     * 
     * @return string
     * 
     */
    public function toJarString()
    {
        $domain = $this->getDomain();
        $expire = $this->getExpire();
        $path   = $this->getPath();

        if ($this->getHttpOnly()) {
            $domain = '#HttpOnly_' . $domain;
        }

        return sprintf(
            "%s\t%s\t%s\t%s\t%s\t%s\t%s",
            $domain,
            ('.' == $this->getDomain()[0]) ? 'TRUE' : 'FALSE',
            $path ?: '/',
            $this->getSecure() ? 'TRUE' : 'FALSE',
            $expire ?: '0',
            $this->getName(),
            $this->getValue()
        );
    }

    /**
     * 
     * Returns this cookie as a request header string.
     * 
     * @return string
     * 
     */
    public function toRequestHeaderString()
    {
        return urlencode($this->name) . '=' . urlencode($this->value);
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
                preg_match(
                    '/\.' . preg_quote($cookie_domain) . '$/',
                    $host_domain
                )
            );
    }

    /**
     * 
     * Check a string to see if it could be a unix time stamp.
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
        return (((int) $timestamp === $timestamp) || // Allow the timestamp to be a string or integer
                ((string) (int) $timestamp === $timestamp)) &&
               ($timestamp <= PHP_INT_MAX) &&
               ($timestamp >= ~PHP_INT_MAX);
    }
}
