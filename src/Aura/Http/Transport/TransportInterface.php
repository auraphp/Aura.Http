<?php
namespace Aura\Http\Transport;

use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

interface TransportInterface
{
    public function sendRequest(Request $request);
    public function sendResponse(Response $response);
}
