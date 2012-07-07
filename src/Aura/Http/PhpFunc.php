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
namespace Aura\Http;

/**
 * 
 * An object-oriented interface to native PHP functions.  This allows us to
 * override those functions for testing.
 * 
 * @package Aura.Http
 * 
 */
class PhpFunc
{
    public function __call($func, $args)
    {
        return call_user_func_array($func, $args);
    }
    
    // not a php function; used in place of `echo` and `print`
    public function output($text)
    {
        echo $text;
    }
    
    public function headers_sent(&$file, &$line)
    {
        return headers_sent($file, $line);
    }
}
