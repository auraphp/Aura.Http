<?php
namespace Aura\Http;

use Aura\Http\Transport\AdapterInterface;

class Transport
{
    protected $phpfunc;
    
    protected $adapter;
    
    public function __construct(PhpFunc $phpfunc, AdapterInterface $adapter)
    {
        $this->phpfunc = $phpfunc;
        $this->adapter = $adapter;
    }
    
    // @todo Replace echo, is_resource, feof, et. al with phpfunc calls
    public function sendResponse(Response $response)
    {
        if ($this->phpfunc->headers_sent($file, $line)) {
            throw new Exception\HeadersSent($file, $line);
        }
        
        // determine status header type
        // cf. <http://www.php.net/manual/en/function.header.php>
        if ($response->isCgi()) {
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
        if (is_resource($content)) {
            while (! feof($content)) {
                echo fread($content, 8192);
            }
            fclose($content);
        } else {
            echo $content;
        }
    }
    
    public function sendRequest(Request $request)
    {
        if (! $request->url) {
            throw new Exception('The request has no URL.');
        }

        // turn off encoding if we are saving the content to a file.
        if (isset($request->options->save_to_folder) && 
            $request->options->save_to_folder) {
            $request->setEncoding(false);
        }

        $request->prepareContent();
        
        // force the content-type header if needed
        if ($request->content_type) { 
            if ($request->charset) {
                $request->content_type .= "; charset={$request->charset}";
            }
            $request->headers->set('Content-Type', $request->content_type);
        }
        
        // bake cookies
        if (count($request->cookies)) {
            $list = [];

            foreach ($request->cookies as $cookie) {
                $list[] = "{$cookie->getName()}={$cookie->getValue()}";
            }

            $request->headers->add('Cookie', implode('; ', $list));
        }
        
        return $this->adapter->exec($request);
    }
}
