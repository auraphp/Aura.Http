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
    
    public function __toString()
    {
        $text = null;
        $this->rewind();
        while (! $this->eof()) {
            $text .= $this->read();
        }
        return $text;
    }
    
    public function set($storage)
    {
        $this->storage = $storage;
    }
    
    public function get()
    {
        return $this->storage;
    }
    
    public function setType($type, $charset = null)
    {
        if ($charset) {
            $type .= "; charset={$charset}";
        }
        $this->headers->set('Content-Type', $type);
    }
    
    public function setDisposition(
        $disposition,
        $name = null,
        $filename = null
    ) {
        if ($name) {
            $disposition .= "; name=\"{$name}\"";
        }
        if ($filename) {
            $disposition .= "; filename=\"{$filename}\"";
        }
        $this->headers->set('Content-Disposition', $disposition);
    }
    
    // HTTP, unlike MIME, does not use Content-Transfer-Encoding, and
    // does use Transfer-Encoding and Content-Encoding.
    // -- <http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.15>,
    //    the "Note" at the end.
    public function setEncoding($encoding)
    {
        $this->headers->set('Content-Encoding', $encoding);
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function read()
    {
        if (is_resource($this->storage)) {
            $data = fread($this->storage, 8192);
            $this->eof = feof($this->storage);
        } elseif (is_array($this->storage)) {
            $data = http_build_query($this->storage);
            $this->eof = true;
        // } elseif ($this->storage instanceof StreamInterface) {
        //     $data = $this->storage->read();
        //     $this->eof = $this->storage->eof();
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
        // } elseif ($this->storage instanceof StreamInterface) {
        //     $this->storage->rewind();
        //     $this->eof = $this->storage->eof();
        } else {
            $this->eof = false;
        }
    }
}
