<?php
namespace Aura\Http\Content;

interface ContentInterface
{
    public function getHeaders();
    
    public function read();
    
    public function eof();
    
    public function rewind();
}
