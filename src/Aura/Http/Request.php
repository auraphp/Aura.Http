<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Request\Adapter as Adapter;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Request\Options as Options;

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
    const DELETE     = 'DELETE';
    const GET        = 'GET';
    const HEAD       = 'HEAD';
    const OPTIONS    = 'OPTIONS';
    const POST       = 'POST';
    const PUT        = 'PUT';
    const TRACE      = 'TRACE';
    
    /**
     * WebDAV method constants.
     */
    const COPY       = 'COPY';
    const LOCK       = 'LOCK';
    const MKCOL      = 'MKCOL';
    const MOVE       = 'MOVE';
    const PROPFIND   = 'PROPFIND';
    const PROPPATCH  = 'PROPPATCH';
    const UNLOCK     = 'UNLOCK';
    
    /**
     * Auth constants
     */
    const BASIC      = 'BASIC';
    const DIGEST     = 'DIGEST';
    
    /**
     * 
     * HTTP request method to use.
     * 
     * @var string
     *
     */
    protected $method = self::GET;
    
    /**
     * 
     * Request options to use. i.e. max_redirects, ssl_verify_peer, etc.
     * 
     * @var \ArrayObject
     * 
     * @todo list options
     *
     */
    protected $options;
    
    /**
     *
     * The URI to request.
     * 
     * @var string
     * 
     */
    protected $uri;
    
    /**
     * 
     * @param \Aura\Http\Header\Collection $headers
     * 
     * @param \Aura\Http\Cookie\Collection $cookies
     * 
     * @param array $options Options for content type, character set, etc.
     * 
     */
    public function __construct(
        Headers $headers,
        Cookies $cookies,
        Options $options
    ) {
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->options = $options;
    }
    
    /**
     * 
     * Read only access to certain properties.
     *
     * @param string $key
     *
     * @return mixed
     * 
     * @throws Aura\Http\Exception If property does not exist.
     *
     */
    public function __get($key)
    {
        $keys = [
            'method',
            'options',
            'uri',
        ];

        if (in_array($key, $keys)) {
            return $this->$key;
        } else {
            return parent::__get($key);
        }
    }

    /**
     * 
     * Sets the URI for the request.
     * 
     * @param string $uri The URI for the request.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
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
        $allowed = [
            self::GET,
            self::POST,
            self::PUT,
            self::DELETE,
            self::TRACE,
            self::OPTIONS,
            self::TRACE,
            self::COPY,
            self::LOCK,
            self::MKCOL,
            self::MOVE,
            self::PROPFIND,
            self::PROPPATCH,
            self::UNLOCK
        ];
        
        if (! in_array($method, $allowed)) {
            throw new Exception\UnknownMethod("Method '{$method}' is unknown");
        }
        
        $this->method = $method;
        
        return $this;
    }
    
}