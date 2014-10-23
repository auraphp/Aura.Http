<?php
namespace Aura\Http;

use Aura\Http\Message\MessageFactory;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    protected $http;

    protected $message_factory;

    protected $transport;

    protected function setUp()
    {
        $this->message_factory = new MessageFactory;
        $this->transport = new MockTransport;

        $this->http = new Http(
            $this->message_factory,
            $this->transport
        );
    }

    public function test_request()
    {
        $request = $this->http->newRequest();
        $this->assertInstanceOf('Aura\Http\Message\Request', $request);
        $this->http->sendRequest($request);
        $transport = $this->http->transport;
        $this->assertSame($request, $transport->request);
    }

    public function test_response()
    {
        $response = $this->http->newResponse();
        $this->assertInstanceOf('Aura\Http\Message\Response', $response);
        $this->http->sendResponse($response);
        $transport = $this->http->transport;
        $this->assertSame($response, $transport->response);
    }
}
