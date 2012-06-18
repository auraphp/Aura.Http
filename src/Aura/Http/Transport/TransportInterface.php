<?php
namespace Aura\Http\Transport;

use Aura\Http\Request;
use Aura\Http\Response;

interface TransportInterface
{
    public function sendRequest(Request $request);
    public function sendResponse(Response $response);
}
