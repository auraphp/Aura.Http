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
namespace Aura\Http\Message;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Message;
use Aura\Http\Exception;

/**
 * 
 * HTTP Request library.
 * 
 * @package Aura.Http
 * 
 */
class Request extends Message
{
    /**
     * HTTP method constants.
     */
    const METHOD_DELETE     = 'DELETE';
    const METHOD_GET        = 'GET';
    const METHOD_HEAD       = 'HEAD';
    const METHOD_OPTIONS    = 'OPTIONS';
    const METHOD_POST       = 'POST';
    const METHOD_PUT        = 'PUT';
    const METHOD_TRACE      = 'TRACE';
    
    /**
     * WebDAV method constants.
     */
    const METHOD_COPY       = 'COPY';
    const METHOD_LOCK       = 'LOCK';
    const METHOD_MKCOL      = 'MKCOL';
    const METHOD_MOVE       = 'MOVE';
    const METHOD_PROPFIND   = 'PROPFIND';
    const METHOD_PROPPATCH  = 'PROPPATCH';
    const METHOD_UNLOCK     = 'UNLOCK';
    
    /**
     * Auth constants
     */
    const AUTH_BASIC      = 'BASIC';
    const AUTH_DIGEST     = 'DIGEST';
    
    /**
     * 
     * HTTP request method to use.
     * 
     * @var string
     *
     */
    protected $method = self::METHOD_GET;
    
    /**
     *
     * The URL to request.
     * 
     * @var string
     * 
     */
    protected $url;
    
    protected $auth;
    
    protected $username;
    
    protected $password;
    
    protected $save_to_stream;
    
    /**
     * 
     * Sets the URL for the request.
     * 
     * @param string $url The URL for the request.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 
     * Sets the HTTP method for the request (GET, POST, etc).
     * 
     * Recognized methods are OPTIONS, GET, HEAD, POST, PUT, DELETE,
     * TRACE, and CONNECT, GET, POST, PUT, DELETE, TRACE, OPTIONS, COPY,
     * LOCK, MKCOL, MOVE, PROPFIND, PROPPATCH AND UNLOCK.
     * 
     * @param string $method The method to use for the request.
     * 
     * @return Aura\Http\Request This object.
     * 
     * @throws Aura\Http\Exception\UnknownMethod 
     * 
     */
    public function setMethod($method)
    {
        $const = __CLASS__ . '::METHOD_' . $method;
        if (! defined($const)) {
            throw new Exception\UnknownMethod("Method '{$method}' is unknown");
        }
        
        $this->method = $method;
        
        return $this;
    }
    
    public function setAuth($auth)
    {
        if (! $auth) {
            $this->auth = null;
            return;
        }
        
        $known = [
            self::AUTH_BASIC,
            self::AUTH_DIGEST
        ];
        
        if (! in_array($auth, $known)) {
            throw new Exception\UnknownAuthType("Unknown auth type '$auth'");
        }
        
        $this->auth = $auth;
    }
    
    public function setUsername($username)
    {
        if (strpos($username, ':') !== false) {
            $text = 'The username may not contain a colon (:).';
            throw new Exception\InvalidUsername($text);
        }
        
        $this->username = $username;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    public function getCredentials()
    {
        return $this->username . ':' . $this->password;
    }
    
    // transport should save response content to this stream
    public function setSaveToStream($stream)
    {
        $this->save_to_stream = $stream;
    }
    
    // the stream where response content should be saved to
    public function getSaveToStream()
    {
        return $this->save_to_stream;
    }
}
