<?php
namespace Aura\Http\Content;

// ========================
// POST /path/to/script.php HTTP/1.0
// Host: example.com
// Content-Type: multipart/form-data, boundary=AaB03x
// Content-Length: $requestlen
// 
// --AaB03x
// Content-Disposition: form-data; name="field1"
// 
// $field1
// --AaB03x
// Content-Disposition: form-data; name="field2"
// 
// $field2
// --AaB03x
// Content-Disposition: form-data; name="userfile"; filename="$filename"
// Content-Type: $mimetype
// Content-Transfer-Encoding: binary
// 
// $binarydata
// --AaB03x--
// ==========================

class Multipart implements ContentInterface
{
    protected $parts;
    
    protected $current;
    
    protected $boundary;
    
    protected $content_factory;
    
    protected $eof = false;
    
    public function __construct(ContentFactory $content_factory)
    {
        $this->content_factory = $content_factory;
        $this->parts = new \ArrayObject([]);
        $this->boundary = "--" . md5(uniqid());
    }
    
    public function __get($key)
    {
        return $this->$key;
    }
    
    public function get()
    {
        return $this->parts;
    }
    
    public function add()
    {
        $part = $this->content_factory->newInstance();
        $this->parts[] = $part;
        return $part;
    }
    
    public function read()
    {
        // do we have a current part?
        if (! $this->current) {
            $this->current = current($this->parts);
            return $this->getCurrentHeaders();
        }
        
        // read from the current part until it ends
        while (! $this->current->eof()) {
            return $this->current->read();
        }
        
        // current part ended, get the next one
        $this->current = next($this->parts);
        
        // are we at the end of parts?
        if ($this->current) {
            // not done yet.
            // add a boundary and get the current headers
            return "\r\n{$this->boundary}\r\n"
                 . $this->getCurrentHeaders()
                 . "\r\n\r\n";
        } else {
            // done!
            $this->eof = true;
            // ending boundary
            return "\r\n{$this->boundary}--\r\n";
        }
    }
    
    public function eof()
    {
        return $this->eof;
    }
    
    public function rewind()
    {
        $this->eof = false;
        foreach ($this->parts as $part) {
            $part->rewind();
        }
        rewind($this->parts);
    }
}
