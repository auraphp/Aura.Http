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
namespace Aura\Http\Transport;

/**
 * 
 * The Aura Response class.
 * 
 * @package Aura.Http
 * 
 */
class Options
{
    /**
     * 
     * The file where cookie jar storage is located.
     * 
     * @param string
     * 
     */
    protected $cookie_jar = null;

    /**
     * 
     * The max number of redirects allowed.
     * 
     * @var int
     * 
     */
    protected $max_redirects = 10;

    /**
     * 
     * The connection timeout in seconds.
     * 
     * @var float
     * 
     */
    protected $timeout = 10;

    /**
     * 
     * The proxy hostname.
     * 
     * @var string
     * 
     */
    protected $proxy = null;

    /**
     * 
     * The proxy port number.
     * 
     * @var int
     * 
     */
    protected $proxy_port = null;

    /**
     * 
     * The proxy username.
     * 
     * @var string
     * 
     */
    protected $proxy_username = null;

    /**
     * 
     * The proxy password.
     * 
     * @var string
     * 
     */
    protected $proxy_password = null;

    /**
     * 
     * The SSL certificate authority file.
     * 
     * @var string
     * 
     */
    protected $ssl_cafile = null;

    /**
     * 
     * The SSL certificate authority path.
     * 
     * @var string
     * 
     */
    protected $ssl_capath = null;

    /**
     * 
     * The SSL local certificate.
     * 
     * @var string
     * 
     */
    protected $ssl_local_cert = null;

    /**
     * 
     * The passphrase for the local certificate.
     * 
     * @var string
     * 
     */
    protected $ssl_passphrase = null;

    /**
     * 
     * Require verification of the certificate?
     * 
     * @var bool
     * 
     */
    protected $ssl_verify_peer = null;

    /**
     *
     * Magic get to return property values.
     * 
     * @param string $key
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
     * Sets the cookie jar property.
     * 
     * @param string
     * 
     * @return Options This object.
     * 
     */
    public function setCookieJar($cookie_jar)
    {
        $this->cookie_jar = $cookie_jar;
        return $this;
    }

    /**
     * 
     * When making the request, allow no more than this many redirects. 
     * 
     * @param int $max_redirects The max number of redirects to allow.
     * 
     * @return Options This object.
     * 
     */
    public function setMaxRedirects($max_redirects)
    {
        $this->max_redirects = (int) $max_redirects;
        return $this;
    }

    /**
     * 
     * Sets the request timeout in seconds.
     * 
     * @param float $timeout The timeout in seconds.
     * 
     * @return Options This object.
     * 
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (float) $timeout;
        return $this;
    }

    /**
     * 
     * Send all requests through this proxy server.
     * 
     * @param string $proxy The hostname for the proxy server.
     * 
     * @return Options This object.
     * 
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * 
     * Set the proxy port number.
     * 
     * @param int $proxy_port The proxy port number.
     * 
     * @return Options This object.
     * 
     */
    public function setProxyPort($proxy_port)
    {
        $this->proxy_port = $proxy_port;
        return $this;
    }

    /**
     * 
     * Gets the proxy host and port.
     * 
     * @return string
     * 
     */
    public function getProxyHostAndPort()
    {
        $proxy = $this->proxy;
        if ($this->proxy_port) {
            $proxy .= ':' . $this->proxy_port;
        }
        return $proxy;
    }

    /**
     * 
     * Set the proxy port username.
     * 
     * @param string $proxy_username The proxy username.
     * 
     * @return Options This object.
     * 
     */
    public function setProxyUsername($proxy_username)
    {
        $this->proxy_username = $proxy_username;
        return $this;
    }

    /**
     * 
     * Set the proxy password.
     * 
     * @param string $proxy_password The proxy password.
     * 
     * @return Options This object.
     * 
     */
    public function setProxyPassword($proxy_password)
    {
        $this->proxy_password = $proxy_password;
        return $this;
    }

    /**
     * 
     * Gets the proxy "username:password" credentials.
     * 
     * @return string
     * 
     */
    public function getProxyCredentials()
    {
        if ($this->proxy_username || $this->proxy_password) {
            return $this->proxy_username . ':' . $this->proxy_password;
        }
    }

    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @param bool $flag True or false.
     * 
     * @return Options This object.
     * 
     */
    public function setSslVerifyPeer($flag)
    {
        $this->ssl_verify_peer = (bool) $flag;
        return $this;
    }

    /**
     * 
     * Location of Certificate Authority file on local filesystem which should
     * be used with the $ssl_verify_peer option to authenticate the identity
     * of the remote peer.              
     * 
     * @param string $val The CA file.
     * 
     * @return Options This object.
     * 
     */
    public function setSslCafile($val)
    {
        $this->ssl_cafile = $val;
        return $this;
    }

    /**
     * 
     * If $ssl_cafile is not specified or if the certificate is not
     * found there, this directory path is searched for a suitable certificate.
     * 
     * The path must be a correctly hashed certificate directory.              
     * 
     * @param string $val The CA path.
     * 
     * @return Options This object.
     * 
     */
    public function setSslCapath($val)
    {
        $this->ssl_capath = $val;
        return $this;
    }

    /**
     * 
     * Path to local certificate file on filesystem. This must be a PEM encoded
     * file which contains your certificate and private key. It can optionally
     * contain the certificate chain of issuers.              
     * 
     * @param string $val The local certificate file path.
     * 
     * @return Options This object.
     * 
     */
    public function setSslLocalCert($val)
    {
        $this->ssl_local_cert = $val;
        return $this;
    }

    /**
     * 
     * Passphrase with which the $ssl_local_cert file was encoded.
     * 
     * @param string $val The passphrase.
     * 
     * @return Options This object.
     * 
     */
    public function setSslPassphrase($val)
    {
        $this->ssl_passphrase = $val;
        return $this;
    }
}
 