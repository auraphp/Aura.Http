<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Factory;

use Aura\Http\Headers;
use Aura\Http\Cookies;
use Aura\Http\Request as HttpRequest;
use Aura\Http\Request\Response as RequestResponse;
use Aura\Http\Request\CookieJar;
use Aura\Http\Request\Multipart;
use Aura\Http\Request\ResponseBuilder;
use Aura\Http\Request\Adapter\Curl;
use Aura\Http\Request\Adapter\Stream;

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
        $headers          = new Headers(new Header);
        $cookiefactory    = new Cookie;
        $cookies          = new Cookies($cookiefactory);
        $response         = new RequestResponse($headers, $cookies);
        $response_builder = new ResponseBuilder($response, new ResponseStack);

        if (extension_loaded('curl')) {
            $adapter   = new Curl($response_builder);
        } else {
            $cookiejar = new CookieJar($cookiefactory);
            $adapter   = new Stream(
                                $response_builder, 
                                new Multipart, 
                                $cookiejar
                            );
        }

        return new HttpRequest($adapter, $headers, $cookies);
    }
}