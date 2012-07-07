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
namespace Aura\Http;

use Aura\Http\Adapter\AdapterInterface;
use Aura\Http\Transport\Options;
use Aura\Http\Transport\TransportInterface;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

/**
 * 
 * A Transport class
 * 
 * @package Aura.Http
 * 
 */
class Transport implements TransportInterface
{
    // used so we can intercept native php function calls for testing
    protected $phpfunc;
    
    protected $adapter;
    
    protected $options;
    
    protected $cgi;
    
    /**
     * 
     * Constructor
     *
     * @param PhpFunc $phpfunc
     * @param Options $options
     * @param AdapterInterface $adapter 
     */
    public function __construct(
        PhpFunc             $phpfunc,
        Options             $options,
        AdapterInterface    $adapter
    ) {
        $this->phpfunc = $phpfunc;
        $this->options = $options;
        $this->adapter = $adapter;
        
        $cgi = (strpos(php_sapi_name(), 'cgi') !== false);
        $this->setCgi($cgi);
    }
    
    /**
     * 
     * Magic get.
     * 
     */
    public function __get($key)
    {
        return $this->$key;
    }
    
    /**
     * 
     * Optionally send responses as if in CGI mode. (This changes how the 
     * status header is sent.)
     * 
     * @param bool $cgi True to force into CGI mode, false to not do so.
     * 
     * @return void
     * 
     */
    public function setCgi($cgi)
    {
        $this->cgi = (bool) $cgi;
    }
    
    /**
     * 
     * Is the transport sending responses in CGI mode?
     * 
     * @return bool
     * 
     */
    public function isCgi()
    {
        return (bool) $this->cgi;
    }
    
    public function sendRequest(Request $request)
    {
        return $this->adapter->exec($request, $this->options);
    }
    
    public function sendResponse(Response $response)
    {
        if ($this->phpfunc->headers_sent($file, $line)) {
            throw new Exception\HeadersSent($file, $line);
        }
        
        // determine status header type
        // cf. <http://www.php.net/manual/en/function.header.php>
        if ($this->isCgi()) {
            $status = "Status: {$response->status_code}";
        } else {
            $status = "HTTP/{$response->version} {$response->status_code}";
        }
        
        // add status text
        $status_text = $response->getStatusText();
        if ($status_text) {
            $status .= " {$status_text}";
        }
        
        // send the status
        $this->phpfunc->header($status, true, $response->status_code);
        
        // send the headers
        foreach ($response->getHeaders() as $header) {
            $this->phpfunc->header($header->__toString());
        }
        
        // send the cookies
        foreach ($response->getCookies() as $cookie) {
            $this->phpfunc->setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }
        
        // send the content
        $content = $response->getContent();
        if (is_resource($content)) {
            while (! feof($content)) {
                $this->phpfunc->output(fread($content, 8192));
            }
        } else {
            $this->phpfunc->output($content);
        }
    }
}
