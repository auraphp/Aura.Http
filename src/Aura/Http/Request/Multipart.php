<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request;

use \Aura\Http\Exception as Exception;

/**
 * 
 * A simple HTTP multipart generator.
 * 
 * @package Aura.Http
 * 
 */
class Multipart
{
    /**
     * 
     * @var array
     * 
     */
    protected $content = [];

    /**
     * 
     * @var string
     * 
     */
    protected $boundary;

    /**
     * 
     * @var integer
     * 
     */
    protected $length = 0;

    
    /**
     * 
     * Setup the object.
     * 
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * 
     * Clone the object.
     * 
     * @see reset()
     * 
     */
    public function __clone()
    {
        $this->reset();
    }
    
    /**
     * 
     * Build the multipart content and return as a string.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * 
     * Build the multipart content and return as a string.
     * 
     * @return string
     * 
     */
    public function toString()
    {
        // the end boundary
        $this->content[] = "--{$this->getBoundary()}--\r\n";
        $return          = '';

        foreach ($this->content as $content) {
            if (is_resource($content)) {
                while (! feof($content)) {
                    $return .= fread($content, 8192);
                }
            } else {
                $return .= $content;
            }
        }

        return $return;
    }

    /**
     * 
     * Reset the object.
     * 
     */
    public function reset()
    {
        $this->boundary = 'AURAHTTPREQUEST-' . sha1(microtime(true));

        foreach ($this->content as $content) {
            if (is_resource($content)) {
                fclose($content);
            }
        }

        $this->content  = [];
        $this->length   = 0;
    }
    
    /**
     * 
     * Get the multipart boundary.
     * 
     * @return string
     * 
     */
    public function getBoundary()
    {
        return $this->boundary;
    }
    
    /**
     * 
     * Get the length.
     * 
     * @return integer
     * 
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     *
     * Add files and parameters.
     * 
     * Files must be readable by PHP and start with a `@`.
     * 
     * Format: ['variable_name' => 'value', 'variable_name' => '@/path/to/file']
     *
     * @param array $list
     * 
     */
    public function add(array $list)
    {
        $list  = $this->flattenParameters($list);
        $files = $params = [];

        foreach ($list as $name => $value) {
            if ('@' == $value[0]) {
                // remove the `@`
                $files[$name]  = substr($value, 1);
            } else {
                $params[$name] = $value;
            }
        }

        $this->addFiles($files);
        $this->addParameters($params);
    }
    
    /**
     * 
     * Add parameters.
     * 
     * Format: ['variable_name' => 'value']
     * 
     * @param array $params
     * 
     */
    protected function addParameters(array $params)
    {
        $encoded = '';

        foreach ($params as $name => $value) {
            $encoded .= "--{$this->getBoundary()}\r\n";
            $encoded .= "Content-Disposition: form-data; name=\"{$name}\"";
            $encoded .= "\r\n\r\n{$value}\r\n";
        }
        
        $this->length   += strlen($encoded);
        $this->content[] = $encoded;
    }
    
    /**
     * 
     * Add files.
     * 
     * Format : ['variable_name' => '/path/to/file.ext']
     * 
     * @param array $files
     * 
     * @throws Aura\Http\Exception If the files does not exist.
     * 
     */
    protected function addFiles(array $files)
    {
        foreach ($files as $name => $file) {
            if (! file_exists($file) || ! is_file($file)) {
                throw new Exception\FileDoesNotExist("File does not exist `$file`.");
            }
            
            $filename = basename($file);
            $info     = new \finfo;
            $type     = $info->file($file, FILEINFO_MIME_TYPE);

            if (! $type) {
                $type = 'application/octet-stream';
            }

            $encoded  = "--{$this->getBoundary()}\r\n";
            $encoded .= "Content-Disposition: form-data; name=\"{$name}\"; ";
            $encoded .= "filename=\"{$filename}\"\r\n";
            $encoded .= "Content-Type: {$type}\r\n\r\n";
            
            $this->content[] = $encoded;
            $this->length   += strlen($encoded);
            $this->content[] = fopen($file, 'r');
            $this->length   += filesize($file);
            $this->content[] = "\r\n";
            $this->length   += 2; // for \r\n
        }
    }

    /**
     * 
     * Flatten a multidimensional array.
     * 
     * [foo => [1,2]] becomes ['foo[0]' => 1, 'foo[1]' => 2]
     * 
     * @param array $array
     * 
     * @param array $return
     * 
     * @param string $prefix
     * 
     * @return array
     * 
     */
    protected function flattenParameters(
        array $array, array $return = [], $prefix = '')
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $_prefix = $prefix ? $prefix . '[' . $key . ']' : $key;
                $return  = $this->flattenParameters($value, $return, $_prefix); 
            } else {
                $_key          = $prefix ? $prefix . '[' . $key . ']' : $key;
                $return[$_key] = $value;
            }
        }

        return $return;
    }
}