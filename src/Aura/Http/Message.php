<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Header\Collection as Headers;

/**
 * 
 * An HTTP message (either a request or a response).
 * 
 * @package Aura.Http
 * 
 */
class Message
{
    /**
     * 
     * The cookies for this message.
     * 
     * @var Cookies
     * 
     */
    protected $cookies;
    
    /**
     * 
     * The content of this message.
     * 
     * @var mixed
     * 
     */
    protected $content;
    
    /**
     * 
     * The headers for this message.
     * 
     * @var Headers
     * 
     */
    protected $headers;
    
    /**
     * 
     * The HTTP version for this message.
     * 
     * @var string
     * 
     */
    protected $version = '1.1';
    
    /**
     * 
     * Constructor.
     * 
     * @param Headers $headers A Headers object.
     * 
     * @param Cookies $cookies A Cookies object.
     * 
     */
    public function __construct(Headers $headers, Cookies $cookies)
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
    }
    
    /**
     * 
     * Read-only access to properties.
     * 
     * @param string $key The property to retrieve.
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
     * Sets the cookies for the message.
     * 
     * @param Cookies $cookies The cookies object.
     * 
     * @return void
     * 
     */
    public function setCookies(Cookies $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }
    
    /** 
     * 
     * Returns the $cookies object.
     * 
     * @return Cookies
     * 
     */
    public function getCookies()
    {
        return $this->cookies;
    }
    
    /**
     * 
     * Sets the content for the message.
     * 
     * @param mixed The content for the message.
     * 
     * @return void
     * 
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * 
     * Returns the $content object.
     * 
     * @return mixed The content for the message.
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * Sets the headers for the message (not including cookies).
     * 
     * @param Headers $headers A Headers object.
     * 
     * @return void
     * 
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * 
     * Returns the $headers object (not including cookies).
     * 
     * @return Headers
     * 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * Sets the HTTP version for the message.
     * 
     * @param string $version The HTTP version to use for this message.
     * 
     * @return void
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        $this->version = $version;
        return $this;
    }
    
    /**
     * 
     * Returns the HTTP version for the message.
     * 
     * @return string
     * 
     */
    public function getVersion()
    {
        return $this->version;
    }
}