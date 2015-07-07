<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.Http
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Http\Message;

use Aura\Http\Cookie\CookieCollection;
use Aura\Http\Header\HeaderCollection;

/**
 *
 * An HTTP message (either a request or a response).
 *
 * @package Aura.Http
 *
 */
abstract class AbstractMessage
{
    /**
     *
     * The cookies for this message.
     *
     * @var CookieCollection
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
     * @var HeaderCollection
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
     * @param HeaderCollection $headers A HeaderCollection object.
     *
     * @param CookieCollection $cookies A CookieCollection object.
     *
     */
    public function __construct(HeaderCollection $headers, CookieCollection $cookies)
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
     * @param CookieCollection $cookies The cookies object.
     *
     * @return self
     *
     */
    public function setCookies(CookieCollection $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     *
     * Returns the $cookies object.
     *
     * @return CookieCollection
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
     * @param mixed $content The content for the message.
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
     * @param HeaderCollection $headers A HeaderCollection object.
     *
     * @return void
     *
     */
    public function setHeaders(HeaderCollection $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     *
     * Returns the $headers object (not including cookies).
     *
     * @return HeaderCollection
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
