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
namespace Aura\Http\Multipart;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;

/**
 * 
 * A factory to create message parts.
 * 
 * @package Aura.Http
 * 
 */
class PartFactory
{
    /**
     * 
     * Returns a new part.
     * 
     * @return Part
     * 
     */
    public function newInstance()
    {
        return new Part(new Headers(new HeaderFactory));
    }
}
 