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
    protected $cookie_jar       = null;
    protected $max_redirects    = 10;
    protected $timeout          = 10;
    
    protected $proxy            = null;
    protected $proxy_port       = null;
    protected $proxy_username   = null;
    protected $proxy_password   = null;
    
    protected $ssl_cafile       = null;
    protected $ssl_capath       = null;
    protected $ssl_local_cert   = null;
    protected $ssl_passphrase   = null;
    protected $ssl_verify_peer  = null;
    
    /**
     *
     * Magic
     * 
     * @param string $key
     * @return mixed 
     */
    public function __get($key)
    {
        return $this->$key;
    }
    
    public function setCookieJar($cookie_jar)
    {
        $this->cookie_jar = $cookie_jar;
    }
    
    /**
     * 
     * When making the request, allow no more than this many redirects. 
     * 
     * @param int $max The max number of redirects to allow. If false the
     * default number of max_redirects is set.
     * 
     * @return Aura\Http\Transport\Options This object.
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
     * @param float $time The timeout in seconds. If false the default timeout
     * is set.
     * 
     * @return Aura\Http\Transport\Options This object.
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
     * @param string $spec The URL for the proxy server.
     * 
     * @return Aura\Http\Transport\Options This object.
     * 
     */
    public function setProxy($proxy)
    {
        $this->proxy  = $proxy;
        return $this;
    }
    
    public function setProxyPort($proxy_port)
    {
        $this->proxy_port = $proxy_port;
        return $this;
    }
    
    public function getProxyHostAndPort()
    {
        $proxy = $this->proxy;
        if ($this->proxy_port) {
            $proxy .= ':' . $this->proxy_port;
        }
        return $proxy;
    }
    
    public function setProxyUsername($proxy_username)
    {
        $this->proxy_username = $proxy_username;
        return $this;
    }
    
    public function setProxyPassword($proxy_password)
    {
        $this->proxy_password = $proxy_password;
        return $this;
    }
    
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
     * @return Aura\Http\Transport\Options This object.
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
     * @return Aura\Http\Transport\Options This object.
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
     * @return Aura\Http\Transport\Options This object.
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
     * @return Aura\Http\Transport\Options This object.
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
     * @return Aura\Http\Transport\Options This object.
     * 
     */
    public function setSslPassphrase($val)
    {
        $this->ssl_passphrase = $val;
        return $this;
    }
}
