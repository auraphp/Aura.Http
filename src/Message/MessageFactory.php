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
 * Factory class to create new Request and Response instances.
 *
 * @package Aura.Http
 *
 */
class MessageFactory
{
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
