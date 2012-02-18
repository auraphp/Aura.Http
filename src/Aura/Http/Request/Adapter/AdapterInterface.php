<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request\Adapter;

use Aura\Http\Request;
use Aura\Http\Request\ResponseBuilder;

/**
 * 
 * HTTP Request library.
 * 
 * @package Aura.Http
 * 
 */
interface AdapterInterface
{
    /**
     * 
     * Execute the request.
     * 
     * @param Aura\Http\Request $request
     * 
     * @return Aura\Http\Request\ResponseStack
     * 
     */
    public function exec(Request $request);
}