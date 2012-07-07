<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Multipart;

use Aura\Http\Header\Collection as Headers;

class Part
{
    protected $headers;
    
    protected $content;
    
    public function __construct(Headers $headers)
    {
        $this->headers = $headers;
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function getHeaders()
    {
        return $this->headers;
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
}
