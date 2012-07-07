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
namespace Aura\Http\Cookie;

/**
 * 
 * A factory class to create new Aura\Http\Cookie\Jar
 * 
 * @package Aura.Http
 * 
 */
class JarFactory
{
    public function newInstance($file)
    {
        return new Jar(new Factory, $file);
    }
}
