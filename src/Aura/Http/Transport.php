<?php
namespace Aura\Http;

use Aura\Http\Adapter\AdapterInterface;

class Transport
{
    // used so we can intercept native php function calls for testing
    protected $phpfunc;
    
    protected $adapter;
    
    protected $is_cgi;
    
    public function __construct(PhpFunc $phpfunc, AdapterInterface $adapter)
    {
        $this->phpfunc = $phpfunc;
        $this->adapter = $adapter;
        $is_cgi = (strpos(php_sapi_name(), 'cgi') !== false);
        $this->setCgi($is_cgi);
    }
    
    /**
     * 
     * Optionally send responses as if in CGI mode. (This changes how the 
     * status header is sent.)
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
     * Is the transport sending responses in CGI mode?
     * 
     * @return bool
     * 
     */
    public function isCgi()
    {
        return (bool) $this->is_cgi;
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
        
        // send the status header
        $this->phpfunc->header($status, true, $response->status_code);
        
        // send the non-cookie headers
        foreach ($response->getHeaders() as $label => $values) {
            foreach ($values as $header) {
                $this->phpfunc->header($header->__toString());
            }
        }
        
        // send the cookie headers
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
        if ($this->phpfunc->is_resource($content)) {
            while (! $this->phpfunc->feof($content)) {
                $text = $this->phpfunc->fread($content, 8192);
                $this->phpfunc->output($text);
            }
            $this->phpfunc->fclose($content);
        } else {
            $this->phpfunc->output($content);
        }
    }
    
    public function sendRequest(Request $request)
    {
        return $this->adapter->exec($request);
    }
}
