<?php
namespace Aura\Http\Content;

interface ContentInterface
{
    public function read();
    
    public function eof();
    
    public function rewind();
}
