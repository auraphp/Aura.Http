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

use Aura\Http\Message\MessageFactory;
use Aura\Http\Transport\Transport;
use Aura\Http\Transport\TransportInterface;
use Aura\Http\Message\AbstractMessage;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response;

/**
 *
 * A class to create and send request and response objects.
 *
 * @package Aura.Http
 *
 */
class Http
{
    /**
     * Aura\Http\Message\MessageFactory object
     *
     * @var MessageFactory
     *
     */
    protected $message_factory;

    /**
     * An object of type Aura\Http\Transport\TransportInterface
     *
     * @var \Aura\Http\Transport\TransportInterface
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
     * @return Message\Request
     *
     */
    public function newRequest()
    {
        return $this->message_factory->newRequest();
    }

    /**
     *
     * Returns a new Message\Response object
     *
     * @return Message\Response
     *
     */
    public function newResponse()
    {
        return $this->message_factory->newResponse();
    }

    /**
     *
     * Sends a request through the transport.
     *
     * @param Request $request
     *
     * @return Message\ResponseStack
     *
     */
    public function sendRequest(Request $request)
    {
        return $this->transport->sendRequest($request);
    }

    /**
     *
     * Sends a response through the transport.
     *
     * @param Response $response
     *
     * @return Message\ResponseStack
     *
     */
    public function sendResponse(Response $response)
    {
        return $this->transport->sendResponse($response);
    }
}
