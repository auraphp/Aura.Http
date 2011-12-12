<?php

namespace Aura\Http\Exception;

class HeadersSent extends \Aura\Http\Exception
{
    public function __construct($file, $line)
    {
        $message = "Headers have already been sent from '{$file}' at line {$line}.";
        parent::__construct($message);
    }
}
