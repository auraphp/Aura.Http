<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Exception;

/**
 * 
 * Throws HeaderSent exception
 * 
 * @package Aura.Http
 * 
 */
class HeadersSent extends \Aura\Http\Exception
{
    /**
     * 
     * Constructor.
     * 
     * @param string $file The file from which headers were first sent.
     * 
     * @param int $line The line number in that file where headers were sent.
     * 
     */
    public function __construct($file, $line)
    {
        $message = "Headers have already been sent from '{$file}' at line {$line}.";
        parent::__construct($message);
    }
}
