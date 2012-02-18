<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Exception;

/**
 * 
 * @package Aura.Http
 * 
 */
class FullUrlExpected extends \Aura\Http\Exception
{
    public function __construct()
    {
        $msg = 'A full URL is required. The scheme and/or host name is missing.';
        parent::__construct($msg);
    }
}