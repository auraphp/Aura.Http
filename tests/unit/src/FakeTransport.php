<?php
namespace Aura\Http;

use Aura\Http\Transport\TransportInterface;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

class FakeTransport implements TransportInterface
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
