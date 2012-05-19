<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Cookie\Collection as Cookies;

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
     * @var string
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
     * The HTTP status code of the message.
     * 
     * @var int
     * 
     */
    protected $status_code;
    
    /**
     * 
     * The HTTP status message of the message.
     * 
     * @var string
     * 
     */
    protected $status_text;
    
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
     * Read-only access to $headers and $cookies objects.
     * 
     * @param string $key The property to retrieve.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($key == 'headers') {
            return $this->headers;
        }
        
        if ($key == 'cookies') {
            return $this->cookies;
        }
        
        throw new Exception("No such property '$key'");
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
     * Sets the content of the message.
     * 
     * @param mixed $content The body content of the message. Note that this
     * may be a resource, in which case it will be streamed out when sending.
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
     * Gets the content of the message.
     * 
     * @return mixed The body content of the message.
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
     * Returns the headers for the message (not including cookies).
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
     * Sets the HTTP status code to for the message.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new Exception\UnknownStatus("Status code $code not recognized.");
        }
        $this->status_code = $code;
        return $this;
    }
    
    /**
     * 
     * Returns the HTTP status code for the message.
     * 
     * @return int
     * 
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
    
    /**
     * 
     * Sets the HTTP status text for the message.
     * 
     * @param string $text The status text.
     * 
     * @return void
     * 
     */
    public function setStatusText($text)
    {
        $text = trim(str_replace(["\r", "\n"], '', $text));
        $this->status_text = $text;
        return $this;
    }
    
    /**
     * 
     * Returns the HTTP status text for the message.
     * 
     * @return string
     * 
     */
    public function getStatusText()
    {
        return $this->status_text;
    }
    
    /**
     * 
     * Sets the HTTP version for the message to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this message.
     * 
     * @return void
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw new Exception\UnknownVersion("HTTP version '$version' not recognized.");
        } else {
            $this->version = $version;
        }
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