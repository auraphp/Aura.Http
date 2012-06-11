<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Response;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Response;

/**
 * 
 * Builds a Response object.
 * 
 * @package Aura.Http
 * 
 */
class Factory
{
    public function newInstance()
    {
        return new Response(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
    }
}
