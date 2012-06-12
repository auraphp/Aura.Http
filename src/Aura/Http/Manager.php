<?php
namespace Aura\Http;

use Aura\Http\Message\Factory as MessageFactory;

class Manager
{
    public function __construct(
        MessageFactory $message_factory,
        Transport $transport
    ) {
        $this->message_factory = $message_factory;
        $this->transport = $transport;
    }
    
    public function __get($key)
    {
        return $this->$key;
    }
    
    public function newRequest()
    {
        return $this->message_factory->newInstance('request');
    }
    
    public function newResponse()
    {
        return $this->message_factory->newInstance('response');
    }
    
    public function send(Message $message)
    {
        if ($message instanceof Request) {
            return $this->transport->sendRequest($message);
        }
        
        if ($message instanceof Response) {
            return $this->transport->sendResponse($message);
        }
        
        throw new Exception('Unknown message type.');
    }
}
