<?php
namespace Aura\Http;

use Aura\Http\Transport\TransportInterface;

class MockTransport implements TransportInterface
{
    public $response;
    
    public $request;
    
    public function sendResponse(Response $response)
    {
        $this->response = $response;
    }
    
    public function sendRequest(Request $request)
    {
        $this->request = $request;
    }
}
