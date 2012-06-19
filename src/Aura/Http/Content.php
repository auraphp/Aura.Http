<?php
namespace Aura\Http;

use Aura\Http\Content\ContentInterface;

class Content implements ContentInterface
{
    protected $headers;
    
    protected $content;
    
    protected $eof = false;
    
    public function __construct(
        HeaderCollection $headers,
        $content = null
    ) {
        $this->headers = $headers;
        $this->content = $content;
    }
    
    public function __get($key)
    {
        return $this->$key;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function add($data)
    {
        if (is_resource($this->content)) {
            fwrite($this->content, $data);
            return;
        }
        
        if (is_scalar($this->content)) {
            $this->content .= $data;
            return;
        }
        
        if (is_array($this->content)) {
            $this->content = array_merge($this->content, $data);
            return;
        }
    }
    
    public function get()
    {
        return $this->content;
    }
    
    public function read()
    {
        if (is_resource($this->content)) {
            $data = fread($this->content, 8192);
            $this->eof = feof($this->content);
        }
        
        if (is_scalar($data)) {
            $data = (string) $this->content;
            $this->eof = true;
        }
        
        if (is_array($data)) {
            $data = http_build_query($this->content);
            $this->eof = feof($this->content);
        }
        
        return $data;
    }
    
    public function eof()
    {
        return $this->eof;
    }
    
    public function rewind()
    {
        $this->eof = false;
        if (is_resource($this->content)) {
            rewind($this->content);
        }
    }
}
