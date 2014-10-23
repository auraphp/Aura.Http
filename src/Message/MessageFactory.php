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
namespace Aura\Http\Message;

use Aura\Http\Cookie\CookieCollection;
use Aura\Http\Cookie\CookieFactory;
use Aura\Http\Header\HeaderCollection;
use Aura\Http\Header\HeaderFactory;

/**
 *
 * Factory class to create new instances of
 *
 * Aura\Http\Message\Message
 * Aura\Http\Message\Request
 * Aura\Http\Message\Response
 *
 * @package Aura.Http
 *
 */
class MessageFactory
{
    /**
     *
     * Creates the object of Aura\Http\Message\Message
     *
     * @return Message An object of Aura\Http\Message\Message
     */
    public function newMessage()
    {
        $headers = new HeaderCollection(new HeaderFactory);
        $cookies = new CookieCollection(new CookieFactory);
        return new Message($headers, $cookies);
    }

    /**
     *
     * Creates an object of Aura\Http\Message\Request
     *
     * @return Request An object of Aura\Http\Message\Request
     */
    public function newRequest()
    {
        $headers = new HeaderCollection(new HeaderFactory);
        $cookies = new CookieCollection(new CookieFactory);
        return new Request($headers, $cookies);
    }

    /**
     *
     * Creates object of Aura\Http\Message\Response
     *
     * @return Response An object of Aura\Http\Message\Response
     */
    public function newResponse()
    {
        $headers = new HeaderCollection(new HeaderFactory);
        $cookies = new CookieCollection(new CookieFactory);
        return new Response($headers, $cookies);
    }
}
