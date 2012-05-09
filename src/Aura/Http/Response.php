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
 * The Aura Response class.
 * 
 * @package Aura.Http
 * 
 */
class Response extends Message
{
    /**
     * 
     * List of default HTTP status messages.
     * 
     * @var array
     * 
     */
    protected $status_text_default = [
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
    ];
    
    protected $is_cgi;
    
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
        parent::__construct($headers, $cookies);
        $this->setStatusCode(200);
        $is_cgi = (strpos(php_sapi_name(), 'cgi') !== false);
        $this->setCgi($is_cgi);
    }
    
    /**
     * 
     * Optionally force the response to act as if it is in CGI mode. (This
     * changes how the status header is sent.)
     * 
     * @param bool $is_cgi True to force into CGI mode, false to not do so.
     * 
     * @return void
     * 
     */
    public function setCgi($is_cgi)
    {
        $this->is_cgi = (bool) $is_cgi;
    }
    
    /**
     * 
     * Is the response currently acting in CGI mode?
     * 
     * @return bool
     * 
     */
    public function isCgi()
    {
        return (bool) $this->is_cgi;
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
        $this->sendContent();
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
        
        // determine status header type
        // cf. <http://www.php.net/manual/en/function.header.php>
        if ($this->isCgi()) {
            $status = "Status: {$this->status_code}";
        } else {
            $status = "HTTP/{$this->version} {$this->status_code}";
        }
        
        // add status text
        if ($this->status_text) {
            $status .= " {$this->status_text}";
        }
        
        // send the status header
        header($status, true, $this->status_code);
        
        // send the non-cookie headers
        $this->headers->send();
        
        // send the cookie headers
        $this->cookies->send();
    }
    
    /**
     * 
     * Sends the HTTP content; if the content is a resource, it streams out
     * the resource 8192 bytes at a time.
     * 
     * @return void
     * 
     */
    public function sendContent()
    {
        $content = $this->getContent();
        if (is_resource($content)) {
            while (! feof($content)) {
                echo fread($content, 8192);
            }
            fclose($content);
        } else {
            echo $content;
        }
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
        parent::setStatusCode($code);
        
        if (isset($this->status_text_default[$code])) {
            $this->setStatusText($this->status_text_default[$code]);
        } else {
            $this->setStatusText(null);
        }
        
        return $this;
    }
}