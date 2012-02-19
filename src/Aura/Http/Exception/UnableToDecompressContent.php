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
class UnableToDecompressContent extends \Aura\Http\Exception
{
    public $content;
    
    public function __construct($data)
    {
        $this->content = $data;
        $msg = 'Unable to uncompress the response content.';
        parent::__construct($msg);
    }
}