<?php
namespace Aura\Http\Content;

interface StreamInterface
{
    public function read();
    
    public function eof();
    
    public function rewind();
}
