<?php
namespace Aura\Http\Content;

interface ContentInterface
{
    public function get();
    
    public function add();
    
    public function read();
    
    public function eof();
    
    public function rewind();
}
