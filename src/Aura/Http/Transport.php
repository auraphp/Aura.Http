<?php
namespace Aura\Http;

use Aura\Http\Adapter\AdapterInterface;
use Aura\Http\Transport\Options;

class Transport
{
    // used so we can intercept native php function calls for testing
    protected $phpfunc;
    
    protected $adapter;
    
    protected $options;
    
    protected $cgi;
    
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
        foreach ($response->getHeaders() as $header) {
            $this->phpfunc->header($header->__toString());
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
        
        // get the content
        $content = $response->getContent();
        
        // is the content a stream?
        $is_stream = $this->phpfunc->is_resource($content)
                  && $this->phpfunc->get_resource_type($content) == 'stream';
        
        // send the content
        if ($is_stream) {
            while (! $this->phpfunc->feof($content)) {
                $text = $this->phpfunc->fread($content, 8192);
                $this->phpfunc->output($text);
            }
        } else {
            $this->phpfunc->output($content);
        }
    }
    
    public function sendRequest(Request $request)
    {
        return $this->adapter->exec($request, $this->options);
    }
}
