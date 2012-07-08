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
    /**
     * 
     * Forwards all calls to PHP functions.
     * 
     * @param string $func The PHP function name.
     * 
     * @param array $args The arguments to pass to the function.
     * 
     * @return mixed
     * 
     */
    public function __call($func, $args)
    {
        return call_user_func_array($func, $args);
    }

    /**
     * 
     * A replacement function to use instead of `echo` and `print` (since
     * they are keywords, not functions per se).
     * 
     * @param string $text The text to echo/print.
     * 
     * @return void
     * 
     */
    public function output($text)
    {
        echo $text;
    }

    // FIXME Public method name "PhpFunc::headers_sent" is not in camel caps format
    /**
     * 
     * Override for `headers_sent()` since it needs parameter references.
     * 
     * @param string &$file The file where headers were sent.
     * 
     * @param int &$line The line in that file where headers were sent.
     * 
     * @return bool
     * 
     */
    public function headers_sent(&$file, &$line)
    {
        return headers_sent($file, $line);
    }
}
 