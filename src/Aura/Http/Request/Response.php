<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use Aura\Http as Http;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Message;

/**
 * 
 * The results of a request.
 * 
 * @package Aura.Http
 * 
 */
class Response extends Message
{
    /**
     * 
     * Has the content been save to a file. If true `$content` will contain 
     * the full path to the saved file.
     *
     * @var bool 
     *
     */
    protected $is_saved_to_file;

    public function __clone()
    {
        $this->content          = null;
        $this->headers          = clone $this->headers;
        $this->cookies          = clone $this->cookies;
        $this->status_code      = 200;
        $this->status_text      = null;
        $this->version          = '1.1';
        $this->is_saved_to_file = false;
    }
    
    /**
     * 
     * Set the response content.
     *
     * @param string $content
     *
     * @param bool $append
     * 
     * @param bool $saved_to_file Has the content been saved to a file.
     * 
     * @return void
     * 
     */
    public function setContent($content, $append = true, $save_to_file = false)
    {
        if ($append && ! $save_to_file) {
            $this->content .= $content;
        } else {
            $this->content  = $content;
        }
        
        $this->is_saved_to_file = $save_to_file;
        return $this;
    }
    
    /**
     * 
     * Gets the content of the response.
     * 
     * @return string|resource The body content of the response or a file resource.
     * 
     * @throws Aura\Http\Exception\UnableToDecompressContent
     * 
     */
    public function getContent()
    {
        if ($this->is_saved_to_file) {
            return fopen($this->content, 'r');
        }

        if (isset($this->headers->{'Content-Encoding'})) {
            $encoding = $this->headers->{'Content-Encoding'}->getValue();
        } else {
            $encoding = false;
        }

        if ('gzip' == $encoding) {
            $content = @gzinflate(substr($this->content, 10));
        } else if ('inflate' == $encoding) {
            $content = @gzinflate($this->content);
        } else {
            return $this->content;
        }

        if (false === $content) {
            throw new Http\Exception\UnableToDecompressContent($this->content);
        }
        
        return $content;
    }
}
