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
namespace Aura\Http\Adapter;

use Aura\Http\Message\Request;
use Aura\Http\Transport\Options;

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
     * @param Request $request The request.
     * 
     * @param Options $options The transport options.
     * 
     * @return Aura\Http\Response\Stack A stack of responses.
     * 
     */
    public function exec(Request $request, Options $options);
}
 