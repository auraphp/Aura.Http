<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Message;

use Aura\Http\Message\Factory as MessageFactory;

/**
 * 
 * Builds a message stack from headers and content.
 * 
 * @package Aura.Http
 * 
 */
class StackBuilder
{
    public function __construct(MessageFactory $message_factory)
    {
        $this->message_factory = $message_factory;
    }
    
    /**
     * 
     * Creates and returns a new Stack object with messages in it.
     * 
     * @return \Aura\Http\Request\ResponseStack
     * 
     */
    public function newInstance(array $headers, $content = null)
    {
        // a message stack
        $stack = new Stack;
        
        // have a new message available regardless
        $message = $this->message_factory->newInstance();
        
        // add headers
        foreach ($headers as $header) {
            
            // split on the first colon.
            $pos = strpos($header, ':');
            $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
            
            // look for an HTTP header to start a new message
            if ($pos === false && $is_http) {
                
                // start a new message and add it to the stack
                $message = $this->message_factory->newInstance();
                $stack->push($message);
                
                // set the version, status code, and status text in the response
                preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
                $message->setVersion($matches[1]);
                $message->setStatusCode($matches[2]);
                $message->setStatusText($matches[3]);
                
                // go to the next header line
                continue;
            }
            
            // the header label is before the colon
            $label = substr($header, 0, $pos);
            
            // the header value is the part after the colon,
            // less any leading spaces.
            $value = ltrim(substr($header, $pos+1));
            
            // is this a set-cookie header?
            if (strtolower($label) == 'set-cookie') {
                // add the cookie
                $cookie = $message->cookies->addFromString($value, $default_uri);
            } else {
                // add the header
                $message->headers->add($label, $value, false);
            }
        }
        
        // set the content on the current (last) message in the stack
        $message->content = $content;
        
        // done!
        return $stack;
    }
}
