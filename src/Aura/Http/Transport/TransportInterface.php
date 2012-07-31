<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
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
 * 
 * @package Aura.Http
 * 
 */
interface TransportInterface
{
    /**
     * 
     * Aura\Http\Message\Request object
     * 
     * @param Request $request Aura\Http\Message\Response
     * 
     */
    public function sendRequest(Request $request);

    /**
     * 
     * Aura\Http\Message\Response object
     * 
     * @param Response $response Aura\Http\Message\Response
     * 
     */
    public function sendResponse(Response $response);
}
 