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

use Aura\Http\Multipart\PartFactory;

/**
 * 
 * 
 * @package Aura.Http
 * 
 */
class FormData
{
    protected $parts = [];
    
    protected $boundary;
    
    /**
     *
     * @param PartFactory $part_factory 
     * 
     */
    public function __construct(PartFactory $part_factory)
    {
        $this->part_factory = $part_factory;
        $this->boundary = uniqid(null, true);
    }
    
    public function __toString()
    {
        $text = '';
        foreach ($this->parts as $part) {
            $text .= "--{$this->boundary}\r\n"
                   . $part->getHeaders()->__toString()
                   . "\r\n\r\n"
                   . $part->getContent()
                   . "\r\n";
        }
        $text .= "--{$this->boundary}--\r\n";
        return $text;
    }
    
    public function getBoundary()
    {
        return $this->boundary;
    }
    
    public function count()
    {
        return count($this->parts);
    }
    
    public function addFromArray(array $array, $prefix = null)
    {
        foreach ($array as $name => $value) {
            
            // prefix the name if needed
            if ($prefix) {
                $name = $prefix . '[' . $name . ']';
            }
            
            // add parts
            if (is_array($value)) {
                // recursively descend
                $this->addFromArray($value, $name);
            } elseif ($value{0} == '@') {
                // treat as a file upload
                $file = substr($value, 1);
                $this->addFile($name, $file);
            } else {
                // treat as string data
                $this->addString($name, $value);
            }
        }
    }
    
    public function add()
    {
        $part = $this->part_factory->newInstance();
        $this->parts[] = $part;
        return $part;
    }
    
    public function addString($name, $string)
    {
        $part = $this->add();
        $part->setDisposition('form-data', $name);
        $part->setContent($string);
        return $part;
    }
    
    public function addFile($name, $file)
    {
        $part = $this->add();
        $filename = basename($file);
        $part->setDisposition('form-data', $name, $filename);
        $part->setContent(file_get_contents($file));
        return $part;
    }
}
