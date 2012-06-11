<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Adapter;

use Aura\Http\Request;

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
     * @param Aura\Http\Request $request The request.
     * 
     * @return Aura\Http\Response\Stack A stack of responses.
     * 
     */
    public function exec(Request $request);
}
