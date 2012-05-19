<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Message;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;

/**
 * 
 * Builds a Message object.
 * 
 * @package Aura.Http
 * 
 */
class Builder
{
    public function newInstance(array $headers, $content = null)
    {
        return new Message(
            new Headers(new HeaderFactory),
            new Cookies(new CookieFactory)
        );
    }
}
