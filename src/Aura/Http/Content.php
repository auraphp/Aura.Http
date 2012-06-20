<?php
namespace Aura\Http;

use Aura\Http\Content\StreamInterface;
use Aura\Http\Header\Collection as Headers;

class Content implements StreamInterface
{
    protected $headers;
    
    protected $storage;
    
    protected $eof = false;
    
    public function __construct(
        Headers $headers,
        $storage = null
    ) {
        $this->headers = $headers;
        $this->storage = $storage;
    }
    
    public function __get($key)
    {
        return $this->$key;
    }
    
    public function __toString()
    {
        $text = null;
        $this->rewind();
        while (! $this->eof()) {
            $text .= $this->read();
        }
        return $text;
    }
    
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
    }
    
    public function set($storage)
    {
        $this->storage = $storage;
    }
    
    public function get()
    {
        return $this->storage;
    }
    
    public function read()
    {
        if (is_resource($this->storage)) {
            $data = fread($this->storage, 8192);
            $this->eof = feof($this->storage);
        } elseif (is_array($this->storage)) {
            $data = http_build_query($this->storage);
            $this->eof = true;
        } elseif ($this->storage instanceof StreamInterface) {
            $data = $this->storage->read();
            $this->eof = $this->storage->eof();
        } else {
            $data = (string) $this->storage;
            $this->eof = true;
        }
        
        return $data;
    }
    
    public function eof()
    {
        return $this->eof;
    }
    
    public function rewind()
    {
        if (is_resource($this->storage)) {
            rewind($this->storage);
            $this->eof = false;
        } elseif (is_array($this->storage)) {
            reset($this->storage);
            $this->eof = false;
        } elseif ($this->storage instanceof StreamInterface) {
            $this->storage->rewind();
            $this->eof = $this->storage->eof();
        } else {
            $this->eof = false;
        }
    }
}
