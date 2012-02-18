<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http as Http;
use Aura\Http\Headers;
use Aura\Http\Cookies;

/**
 * 
 * The results of a request.
 * 
 * @package Aura.Http
 * 
 */
class Response
{
    /**
     * 
     * Response content
     *
     * @var string|resource 
     *
     */
    protected $content = '';

    /**
     * 
     * Has the content been save to a file. If true `$content` will contain 
     * the full path to the saved file.
     *
     * @var bool 
     *
     */
    protected $is_saved_to_file;

    /**
     * 
     * Response cookies
     *
     * @var Aura\Http\ResponseCookies 
     *
     */
    protected $cookies;

    /**
     * 
     * Response headers excluding the cookies
     *
     * @var Aura\Http\Headers
     *
     */
    protected $headers;
    
    /**
     * 
     * HTTP status code
     *
     * @var int
     *
     */
    protected $status_code;
    
    /**
     * 
     * HTTP status text
     *
     * @var string 
     *
     */
    protected $status_text;
    
    /**
     * 
     * List of the HTTP status text default values.
     * 
     * @var array
     * 
     */
    protected $status_text_default = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',

        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',

        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request Uri Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',

        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );
    
    /**
     * 
     * HTTP version
     *
     * @var string 
     *
     */
    protected $version = '1.1';
    

    /**
     * 
     * @param Aura\Http\Headers $headers
     * 
     * @param Aura\Http\Cookies $cookies
     *
     */
    public function __construct(
        Headers $headers,
        Cookies $cookies
    )
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
    }
    
    public function __clone()
    {
        $this->content          = null;
        $this->headers          = clone $this->headers;
        $this->cookies          = clone $this->cookies;
        $this->status_code      = 200;
        $this->status_text      = null;
        $this->version          = '1.1';
        $this->is_saved_to_file = false;
    }
    
    /**
     *
     * Access to the headers and cookies properties.
     *
     * @return mixed
     *
     * @throws Aura\Http\Exception
     *
     */
    public function __get($key)
    {
        if ($key == 'headers') {
            return $this->headers;
        } else if ($key == 'cookies') {
            return $this->cookies;
        }
        
        throw new Http\Exception("No such property '$key'");
    }
    
    /**
     * 
     * Set the cookies from the response.
     *
     * @param Aura\Http\Cookies
     *
     * @return void
     *
     */
    public function setCookies(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }
    
    /**
     * 
     * Return the cookies from the response.
     *
     * @return Aura\Http\ResponseCookies
     *
     */
    public function getCookies()
    {
        return $this->cookies;
    }
    
    /**
     * 
     * Set the response content.
     *
     * @param string $content
     *
     * @param bool $append
     * 
     * @param bool $saved_to_file Has the content been saved to a file.
     * 
     * @return void
     * 
     */
    public function setContent($content, $append = true, $save_to_file = false)
    {
        if ($append && ! $save_to_file) {
            $this->content .= $content;
        } else {
            $this->content  = $content;
        }
        
        $this->is_saved_to_file = $save_to_file;
    }
    
    /**
     * 
     * Gets the content of the response.
     * 
     * @return string|resource The body content of the response or a file resource.
     * 
     * @throws Aura\Http\Exception\UnableToDecompressContent
     * 
     */
    public function getContent()
    {
        if ($this->is_saved_to_file) {
            return fopen($this->content, 'r');
        }

        if (isset($this->headers->{'Content-Encoding'})) {
            $encoding = $this->headers->{'Content-Encoding'}->getValue();
        } else {
            $encoding = false;
        }

        if ('gzip' == $encoding) {
            $content = @gzinflate(substr($this->content, 10));
        } else if ('inflate' == $encoding) {
            $content = @gzinflate($this->content);
        } else {
            return $this->content;
        }

        if (false === $content) {
            throw new Http\Exception\UnableToDecompressContent($this->content);
        }
        
        return $content;
    }
    
    /**
     * 
     * Sets the headers form the response (excluding cookies).
     * 
     * @param Aura\Http\Headers $headers
     * 
     * @return void
     * 
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * 
     * Returns the headers for the response (not including cookies).
     * 
     * @return array An array of headers.
     * 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * Sets the HTTP status code to for the response.
     * 
     * Automatically resets the status text to null.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     *
     * @throws Exception\UnknownStatus
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new Http\Exception\UnknownStatus("Status code '$code' not recognized.");
        }
        
        $this->status_code = $code;
        
        if (isset($this->status_text_default[$code])) {
            $this->setStatusText($this->status_text_default[$code]);
        } else {
            $this->setStatusText(null);
        }
    }
    
    /**
     * 
     * Returns the HTTP status code for the response.
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
     * Sets the HTTP status text for the response. Set the status code before
     * calling setStatusText.
     * 
     * @param string $text The status text.
     * 
     * @return void
     * 
     */
    public function setStatusText($text)
    {
        $text = trim(str_replace(array("\r", "\n"), '', $text));
        $this->status_text = $text;
    }
    
    /**
     * 
     * Returns the HTTP status text for the response.
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
     * Sets the HTTP version for the response to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this response.
     * 
     * @return void
     *
     * @throws Exception\UnknownVersion
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            $msg = "HTTP version '$version' not recognized.";
            throw new Http\Exception\UnknownVersion($msg);
        } else {
            $this->version = $version;
        }
    }
    
    /**
     * 
     * Returns the HTTP version for the response.
     * 
     * @return string
     * 
     */
    public function getVersion()
    {
        return $this->version;
    }
}