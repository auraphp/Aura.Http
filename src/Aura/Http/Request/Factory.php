<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Request as Request;

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
     * @param array $options Adapter specific options and defaults. Currently 
     * only used by Curl.
     * 
     * @return Aura\Http\Request
     *
     */
    public function newInstance(array $options = [])
    {
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        return new Request($headers, $cookies, $options);
    }
}
