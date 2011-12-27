<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * The Aura Response class
 * 
 * @package Aura.Http
 * 
 */
class Response
{
    /**
     * 
     * The cookies for this response.
     * 
     * @var Cookies
     * 
     */
    protected $cookies;
    
    /**
     * 
     * The content of this response.
     * 
     * @var string
     * 
     */
    protected $content;
    
    /**
     * 
     * The headers for this response.
     * 
     * @var Headers
     * 
     */
    protected $headers;
    
    /**
     * 
     * The HTTP status code of the response.
     * 
     * @var int
     * 
     */
    protected $status_code;
    
    /**
     * 
     * The HTTP status message of the response.
     * 
     * @var string
     * 
     */
    protected $status_text;
    
    /**
     * 
     * List of default HTTP status messages.
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
     * The HTTP version for this response.
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
        $this->setStatusCode(200);
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
     * Sends the full HTTP response.
     * 
     * @return void
     * 
     */
    public function send()
    {
        $this->sendHeaders();
        $content = $this->getContent();
        if (is_resource($content)) {
            while (! feof($content)) {
                echo fread($content, 8192);
            }
            fclose($content);
        } else {
            echo $this->getContent();
        }
    }
    
    /**
     * 
     * Sends the HTTP status code, status test, headers, and cookies.
     * 
     * @return void
     * 
     */
    public function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new Exception\HeadersSent($file, $line);
        }
        // build and send the status
        $status = "HTTP/{$this->version} {$this->status_code}";
        if ($this->status_text) {
            $status .= " {$this->status_text}";
        }
        header($status, true, $this->status_code);
        
        // send the non-cookie headers
        $this->headers->send();
        
        // send the cookie headers
        $this->cookies->send();
    }
    
    /** 
     * 
     * Sets the cookies for the response.
     * 
     * @param Cookies $cookies The cookies object.
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
     * Sets the content of the response.
     * 
     * @param mixed $content The body content of the response. Note that this
     * may be a resource, in which case it will be streamed out when sending.
     * 
     * @return void
     * 
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * 
     * Gets the content of the response.
     * 
     * @return mixed The body content of the response.
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * Sets the headers for the response (not including cookies).
     * 
     * @param Headers $headers A Headers object.
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
     * @return Headers
     * 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * Sets the HTTP status code to for the response. Automatically resets the
     * status text to the default for that code, if any.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new Exception("Status code $code not recognized.");
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
     * Sets the HTTP status text for the response.
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
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw new Exception("HTTP version '$version' not recognized.");
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