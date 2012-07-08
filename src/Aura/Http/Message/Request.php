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

    /**
     * 
     * The type of authentication to use.
     * 
     * @var string
     * 
     */
    protected $auth;

    /**
     * 
     * The username for authentication.
     * 
     * @var string
     * 
     */
    protected $username;

    /**
     * 
     * The password for authentication.
     * 
     * @var string
     * 
     */
    protected $password;

    /**
     * 
     * Save the response to this stream resource.
     * 
     * @var resource
     * 
     */
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

    /**
     * 
     * Sets the authentication type.
     * 
     * @param string $auth A `Request::AUTH_*` constant.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
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

        return $this;
    }

    /**
     * 
     * Sets the username for authentication.
     * 
     * @param string $username The username.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setUsername($username)
    {
        if (strpos($username, ':') !== false) {
            $text = 'The username may not contain a colon (:).';
            throw new Exception\InvalidUsername($text);
        }

        $this->username = $username;

        return $this;
    }

    /**
     * 
     * Sets the password for authentication.
     * 
     * @param string $password The password.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * 
     * Returns the "username:password" credentials.
     * 
     * @return string
     * 
     */
    public function getCredentials()
    {
        return $this->username . ':' . $this->password;
    }

    /**
     * 
     * The response content from the request should be saved to this stream
     * resource.
     * 
     * @param resource $stream The stream resource to save to.
     * 
     * @return Aura\Http\Request This object.
     * 
     */
    public function setSaveToStream($stream)
    {
        $this->save_to_stream = $stream;
        return $this;
    }

    /**
     * 
     * Returns the stream resource where response content should be saved to.
     * 
     * @return resource
     * 
     */
    public function getSaveToStream()
    {
        return $this->save_to_stream;
    }
}
 