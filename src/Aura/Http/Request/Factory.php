<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Request as HttpRequest;
use Aura\Http\Request\Response as RequestResponse;
use Aura\Http\Cookie\Jar as CookieJar;
use Aura\Http\Request\Multipart;
use Aura\Http\Request\ResponseStackFactory;
use Aura\Http\Request\Adapter\Curl;
use Aura\Http\Request\Adapter\Stream;

/**
 * 
 * Create a new Request instance.
 * 
 * @package Aura.Http
 * 
 */
class Factory
{
    /**
     *
     * Convenience method for creating a Request object. 
     * 
     * @param string $adapter Use this adapter. Defaults to `auto` if Curl is 
     * installed the Curl adapter is used else the Stream adapter is used.
     * 
     * @param array $options Adapter specific options and defaults. Currently 
     * only used by Curl.
     * 
     * @return Aura\Http\Request
     *
     */
    public function newInstance($adapter = 'auto', array $options = [])
    {
        $headers          = new Headers(new HeaderFactory);
        $cookies          = new Cookies(new CookieFactory);
        $response         = new RequestResponse($headers, $cookies);
        $response_builder = new ResponseBuilder($response, new ResponseStackFactory);

        if ('curl' == $adapter ||
            ('auto' == $adapter && extension_loaded('curl'))) {
            
            $adapter   = new Curl($response_builder, $options);
        } else {
            $cookiejar = new CookieJar(new CookieFactory);
            $adapter   = new Stream(
                                $response_builder, 
                                new Multipart, 
                                $cookiejar
                            );
        }

        return new HttpRequest($headers, $cookies, $adapter);
    }
}