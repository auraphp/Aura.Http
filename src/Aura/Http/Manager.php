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

use Aura\Http\Message\Factory as MessageFactory;
use Aura\Http\Transport\TransportInterface;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

/**
 * 
 * A class to create request and response objects
 * 
 * @package Aura.Http
 * 
 */
class Manager
{
    /**
     * Aura\Http\Message\Factory object
     * 
     * @var MessageFactory
     *  
     */
    protected $message_factory;
    
    /**
     * An object of type Aura\Http\Transport\TransportInterface
     * 
     * @var Aura\Http\Transport\TransportInterface 
     * 
     */
    protected $transport;
    
    /**
     * 
     * Constructor
     *
     * @param MessageFactory $message_factory MessageFactory object
     * 
     * @param TransportInterface $transport A TransportInterface object
     * 
     */
    public function __construct(
        MessageFactory $message_factory,
        TransportInterface $transport
    ) {
        $this->message_factory = $message_factory;
        $this->transport = $transport;
    }
    
    /**
     * 
     * Magic method
     *
     * @param string $key
     * 
     * @return mixed 
     * 
     */
    public function __get($key)
    {
        return $this->$key;
    }
    
    /**
     * 
     * Return object of type Aura\Http\Message\Request
     *
     * @return Aura\Http\Message\Request
     * 
     */
    public function newRequest()
    {
        return $this->message_factory->newRequest();
    }
    
    /**
     * 
     * Return Aura\Http\Message\Response object
     *
     * @return Aura\Http\Message\Response 
     * 
     */
    public function newResponse()
    {
        return $this->message_factory->newResponse();
    }
    
    /**
     * 
     * Send Request or Response depending on the transport
     *
     * @param Message $message
     * 
     * @return type 
     * 
     */
    public function send(Message $message)
    {
        if ($message instanceof Request) {
            return $this->transport->sendRequest($message);
        }
        
        if ($message instanceof Response) {
            return $this->transport->sendResponse($message);
        }
        
        throw new Exception\UnknownMessageType;
    }
}
