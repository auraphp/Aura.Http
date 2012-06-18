<?php
namespace Aura\Http;

use Aura\Http\Message\Factory as MessageFactory;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $http;
    
    protected $message_factory;
    
    protected $transport;
    
    protected function setUp()
    {
        $this->message_factory = new MessageFactory;
        $this->transport = new MockTransport;
        
        $this->http = new Manager(
            $this->message_factory,
            $this->transport
        );
    }
    
    public function test_request()
    {
        $request = $this->http->newRequest();
        $this->assertInstanceOf('Aura\Http\Request', $request);
        $this->http->send($request);
        $transport = $this->http->transport;
        $this->assertSame($request, $transport->request);
    }
    
    public function test_response()
    {
        $response = $this->http->newResponse();
        $this->assertInstanceOf('Aura\Http\Response', $response);
        $this->http->send($response);
        $transport = $this->http->transport;
        $this->assertSame($response, $transport->response);
    }
    
    public function test_unknown()
    {
        $message = $this->message_factory->newInstance('message');
        $this->setExpectedException('Aura\Http\Exception\UnknownMessageType');
        $this->http->send($message);
    }
}
