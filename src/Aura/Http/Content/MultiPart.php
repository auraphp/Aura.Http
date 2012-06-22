<?php
namespace Aura\Http\Content;

use Aura\Http\Content\AbstractContent;
use Aura\Http\Content\Factory as ContentFactory;
use Aura\Http\Header\Collection as Headers;

class MultiPart extends AbstractContent
{
    protected $parts = [];
    
    protected $boundary;
    
    protected $current;
    
    public function __construct(
        Headers $headers,
        ContentFactory $content_factory
    ) {
        $this->headers = $headers;
        $this->content_factory = $content_factory;
        
        $this->setBoundary(uniqid(null, true));
        
        $this->headers->set(
            "Content-Type",
            "multipart/form-data; boundary={$this->boundary}"
        );
    }
    
    public function getBoundary()
    {
        return $this->boundary;
    }
    
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
    }
    
    public function add()
    {
        $part = $this->content_factory->newSinglePart();
        $this->parts[] = $part;
        return $part;
    }
    
    public function count()
    {
        return count($this->parts);
    }
    
    public function addData($name, $value)
    {
        $part = $this->add();
        $part->setDisposition('form-data', $name);
        $part->set($value);
        return $part;
    }
    
    public function addFile($name, $filename, $value, $type = null, $encoding = null)
    {
        $part = $this->add();
        $part->setDisposition('form-data', $name, $filename);
        $part->set($value);
        
        if ($type) {
            $part->setType($type);
        }
        
        if ($encoding) {
            $part->setEncoding($encoding);
        }
        
        return $part;
    }
    
    public function read()
    {
        // do we have a current part?
        if (! $this->current) {
            // we have not started reading yet.
            // pick the current part.
            $this->current = current($this->parts);
            
            // now return the prolog for the part (includes boundary
            // and headers)
            $text = "--{$this->boundary}\r\n"
                  . $this->current->getHeaders()->__toString()
                  . "\r\n\r\n";
            return $text;
        }
        
        // read from the current part until it ends
        if (! $this->current->eof()) {
            return $this->current->read();
        }
        
        // this part is ended
        $this->current = null;
        
        // move to the next part, if there is one
        $this->eof = ! (bool) next($this->parts);
        
        // are we at the end of parts?
        if ($this->eof) {
            // there is no next part
            return "\r\n--{$this->boundary}--\r\n";
        } else {
            // there is a next part
            return "\r\n";
        }
    }
    
    public function rewind()
    {
        $this->eof = false;
        foreach ($this->parts as $part) {
            $part->rewind();
        }
        reset($this->parts);
    }
}
