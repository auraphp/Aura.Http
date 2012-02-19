<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Factory;

use Aura\Http as Http;
use Aura\Http\Request as HttpRequest;

/**
 * 
 * Create a new Request instance.
 * 
 * @package Aura.Http
 * 
 */
class Request
{
    /**
     *
     * Convenience method for creating a Request object. If Curl is installed
     * the Curl adapter is used else the Stream adapter is used.
     * 
     * @return Aura\Http\Request
     *
     */
    public function newInstance()
    {
        $headers          = new Http\Headers(new Header);
        $cookiefactory    = new Cookie;
        $cookies          = new Http\Cookies($cookiefactory
            );
        $response         = new HttpRequest\Response($headers, $cookies);
        $response_builder = new HttpRequest\ResponseBuilder(
                                    $response, new ResponseStack);

        if (extension_loaded('curl')) {
            $adapter   = new HttpRequest\Adapter\Curl($response_builder);
        } else {
            $cookiejar = new HttpRequest\CookieJar($cookiefactory);
            $adapter   = new HttpRequest\Adapter\Stream(
                                $response_builder, 
                                new HttpRequest\Multipart, 
                                $cookiejar
                            );
        }

        return new HttpRequest($adapter, $headers, $cookies);
    }
}