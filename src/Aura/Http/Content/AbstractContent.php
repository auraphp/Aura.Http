<?php
namespace Aura\Http\Content;

use Aura\Http\Header\Collection as Headers;

abstract class AbstractContent implements ContentInterface
{
    protected $headers;
    
    protected $eof = false;
    
    public function __construct(Headers $headers)
    {
        $this->headers = $headers;
    }
    
    public function __toString()
    {
        $string = null;
        $this->rewind();
        while (! $this->eof()) {
            $string .= $this->read();
        }
        return $string;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    abstract public function read();
    
    public function eof()
    {
        return $this->eof;
    }
    
    abstract public function rewind();
}