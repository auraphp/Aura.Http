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
 * Builds multipart/form-data message content.
 * 
 * @package Aura.Http
 * 
 */
class FormData
{
    /**
     * 
     * The list of content parts.
     * 
     * @param array
     * 
     */
    protected $parts = [];

    /**
     * 
     * The boundary used between parts.
     * 
     * @var string
     * 
     */
    protected $boundary;

    /**
     * 
     * A factory to create message parts.
     * 
     * @var PartFactory
     * 
     */
    protected $part_factory;

    /**
     * 
     * Consructor.
     * 
     * @param PartFactory $part_factory A factory to create message parts.
     * 
     */
    public function __construct(PartFactory $part_factory)
    {
        $this->part_factory = $part_factory;
        $this->boundary = uniqid(null, true);
    }

    /**
     * 
     * Returns this object as a string suitable for message content.
     * 
     * @return string
     * 
     */
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

    /**
     * 
     * Returns the boundary used between message parts.
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
     * Returns the number of message parts.
     * 
     * @return int
     * 
     */
    public function count()
    {
        return count($this->parts);
    }

    /**
     * 
     * Adds message parts from an array of key-value pairs; recursively
     * descends into the array.
     * 
     * @param array $array An array of key-value pairs where the key is the
     * field name and the value is the field value.
     * 
     * @param string $prefix The prefix, if any, to use on the field name.
     * 
     * @return void
     * 
     */
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

    /**
     * 
     * Adds, and then returns, a new message part.
     * 
     * @return Part
     * 
     */
    public function add()
    {
        $part = $this->part_factory->newInstance();
        $this->parts[] = $part;
        return $part;
    }

    /**
     * 
     * Adds, and then returns, a new message part for a string field and value.
     * 
     * @param string $name The field name.
     * 
     * @param string $string The field value.
     * 
     * @return Part
     * 
     */
    public function addString($name, $string)
    {
        $part = $this->add();
        $part->setDisposition('form-data', $name);
        $part->setContent($string);
        return $part;
    }

    /**
     * 
     * Adds, and then returns, a new message part for a file upload.
     * 
     * @param string $name The field name.
     * 
     * @param string $file The file name for upload.
     * 
     * @return Part
     * 
     */
    public function addFile($name, $file)
    {
        $part = $this->add();
        $filename = basename($file);
        $part->setDisposition('form-data', $name, $filename);
        $part->setContent(file_get_contents($file));
        return $part;
    }
}
 