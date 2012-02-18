<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http\Factory\ResponseStack as ResponseStackFactory;

/**
 * 
 * 
 * 
 * @package Aura.Http
 * 
 */
class ResponseBuilder
{
    protected $response;
    protected $factory;
    protected $stack;
    protected $file;
    protected $file_handle;
    protected $request_url;


    /**
     *
     * @param 
     *
     */
    public function __construct(
        Response $response, 
        ResponseStackFactory $factory
    )
    {
        $this->response = $response;
        $this->factory  = $factory;
        $this->stack    = $this->factory->newInstance();
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    public function setRequestUrl($url)
    {
        $this->request_url = $url;
    }

    /**
     *
     * Return the stack of Responses and internally reset the stack 
     * for new Responses.
     * 
     * @return Aura\Http\Request\ResponseStack
     *
     */
    public function getStack()
    {
        if (is_resource($this->file_handle)) {
            fclose($this->file_handle);
            $this->file_handle = null;
        }

        $stack       = $this->stack;
        $this->stack = $this->factory->newInstance();

        return $stack;
    }

    /**
     * Callbacks for Request Transports
     */
    
    /**
     * 
     * Callback method for saving the content.
     * 
     * @param resource $ch
     * 
     * @param string $content
     * 
     * @param string $save_to_folder
     * 
     * @return integer Length of the content received.
     * 
     */
    public function saveContentCallback($ch, $content, $save_to_folder)
    {
        if ($save_to_folder) {
            
            $is_resource = is_resource($this->file_handle);
            
            // file_handle is not a resource, extract a filename from the
            // headers else generate a name, then open the file.
            if (! $is_resource) {
                
                if (isset($this->response->headers->{'Content-Disposition'})) {
                    
                    $filename = $this->response->headers->{'Content-Disposition'};
                    preg_match('/filename=[\'|"]([^\'"]*)/', $filename, $m);
                    
                    if (empty($m[1])) {
                        $filename = 'content.' . microtime() . '.out';
                    } else {
                        $filename = basename($m[1]);// found a filename in the content disposition header
                    }
                }
                
                $this->file        = $save_to_folder . DIRECTORY_SEPARATOR . $filename;
                $this->file_handle = fopen($this->file, 'w');
            }
            
            $this->response->setContent($this->file, false, true);
            fwrite($this->file_handle, $content);
            
        } else {
            $this->response->setContent($content);
        }
        
        return strlen($content);
    }
    
    /**
     * 
     * Callback method for saving a single header.
     * 
     * @param resource $ch
     * 
     * @param string $header
     * 
     * @return integer Length of the header received.
     * 
     */
    public function saveHeaderCallback($ch, $header)
    {
        $length = strlen($header);
        
        // remove line endings
        $header = trim($header);
        
        // blank header (double line endings)
        if (! $header) {
            return $length;
        }
        
        // not an HTTP header, must be a "real" header for the current
        // response number.  split on the first colon.
        $pos     = strpos($header, ':');
        $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
        
        // look for an HTTP header to start a new response object.
        if ($pos === false && $is_http) {
            
            $this->response = clone $this->response;
            $this->stack->push($this->response);
            
            // set the version, status code, and status text in the response
            preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
            $this->response->setVersion($matches[1]);
            $this->response->setStatusCode($matches[2]);
            $this->response->setStatusText($matches[3]);
            
            // go to the next header line
            return $length;
        }
        
        // the header label is before the colon
        $label = substr($header, 0, $pos);
        
        // the header value is the part after the colon,
        // less any leading spaces.
        $value = ltrim(substr($header, $pos+1));
        
        // is this a set-cookie header?
        if (strtolower($label) == 'set-cookie') {
            
            $this->response->cookies->setFromString($value, $this->request_url);
        } elseif ($label) {
            // set the header, allow multiples
            $this->response->headers->add($label, $value);
        }
        
        return $length;
    }
}