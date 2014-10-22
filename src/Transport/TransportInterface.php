<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.Http
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Http\Transport;

use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

/**
 *
 * Transports HTTP requests and responses.
 *
 * @package Aura.Http
 *
 */
interface TransportInterface
{
    /**
     *
     * Sends an HTTP request and gets back a response stack.
     *
     * @param Request $request An HTTP request message.
     *
     * @return Response An HTTP response message stack.
     *
     */
    public function sendRequest(Request $request);

    /**
     *
     * Sends an HTTP response.
     *
     * @param Response $response Response
     *
     * @return void
     *
     */
    public function sendResponse(Response $response);
}
