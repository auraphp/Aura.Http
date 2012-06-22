<?php
namespace Aura\Http;

use Aura\Http\Content\ContentInterface;

class Content implements ContentInterface
{
    protected $eof = false;
    
    protected $storage;
    
    public function __toString()
    {
        $string = null;
        $this->rewind();
        while (! $this->eof()) {
            $string .= $this->read();
        }
        return $string;
    }
    
    public function set($storage)
    {
        $this->storage = $storage;
    }
    
    public function get()
    {
        return $this->storage;
    }
    
    public function eof()
    {
        return $this->eof;
    }
    
    public function read()
    {
        if (is_resource($this->storage)) {
            $data = fread($this->storage, 8192);
            $this->eof = feof($this->storage);
        } elseif (is_array($this->storage)) {
            $data = http_build_query($this->storage);
            $this->eof = true;
        } else {
            $data = (string) $this->storage;
            $this->eof = true;
        }
        
        return $data;
    }
    
    public function rewind()
    {
        if (is_resource($this->storage)) {
            rewind($this->storage);
        } elseif (is_array($this->storage)) {
            reset($this->storage);
        }
        $this->eof = false;
    }
}
