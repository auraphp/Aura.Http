<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

/**
 * 
 * Factory to create new ResponseStack objects.
 * 
 * @package Aura.Http
 * 
 */
class ResponseStackFactory
{
    /**
     * 
     * Creates and returns a new ResponseStack object.
     * 
     * @return \Aura\Http\Request\ResponseStack
     * 
     */
    public function newInstance()
    {
        return new ResponseStack;
    }
}