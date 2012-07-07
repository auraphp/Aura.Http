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
namespace Aura\Http\Header;

use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Header;

/**
 * 
 * Collection of non-cookie HTTP headers.
 * 
 * @package Aura.Http
 * 
 */
class Collection implements \IteratorAggregate, \Countable
{
    /**
     * 
     * The list of all headers.
     * 
     * @var array
     * 
     */
    protected $list = [];
    
    /**
     * 
     * @var Aura\Http\Header\Factory
     * 
     */
    protected $factory;

    /**
     * 
     * Constructor
     *
     * @param Aura\Http\Header\Factory $factory
     *
     */
    public function __construct(HeaderFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * 
     * Get a header. If a header has multiple values the first value is returned.
     * 
     * @param string $key 
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        $header = $this->factory->newInstance($key, null);
        $key    = $header->getLabel();

        return $this->list[$key][0];
    }
    
    /**
     * 
     * Does a header exist.
     * 
     * @param string $key 
     * 
     * @return bool
     * 
     */
    public function __isset($key)
    {
        $header = $this->factory->newInstance($key, null);
        $key    = $header->getLabel();

        return isset($this->list[$key]);
    }
    
    /**
     * 
     * Unset a header.
     * 
     * @param string $key 
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        $header = $this->factory->newInstance($key, null);
        $key    = $header->getLabel();

        unset($this->list[$key]);
    }
    
    /**
     * 
     * Creates a string from all the headers
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        $list = [];
        foreach ($this->list as $headers) {
            foreach ($headers as $header) {
                $list[] = $header->__toString();
            }
        }
        return implode("\r\n", $list);
    }
    
    /**
     * 
     * Count the number of headers.
     * 
     * @return integer
     * 
     */
    public function count()
    {
        return count($this->list, COUNT_RECURSIVE) - count($this->list);
    }
    
    /**
     * 
     * Returns a header.
     * 
     * @param string $label
     * 
     * @return null|Aura\Http\Header|array
     * 
     */
    public function get($label)
    {
        // get a sanitized label
        $header = $this->factory->newInstance($label, null);
        $label  = $header->getLabel();
        
        // return null, header, or array of headers
        if (! isset($this->list[$label])) {
            return null;
        } elseif (count($this->list[$label]) == 1) {
            return $this->list[$label][0];
        } else {
            return $this->list[$label];
        }
    }
    
    /**
     * 
     * Returns all the headers.
     * 
     * @return array
     * 
     */
    public function getAll()
    {
        return $this->list;
    }
    
    /**
     * 
     * Returns all the headers as an iterator.
     * 
     * @return \ArrayIterator
     * 
     */
    public function getIterator()
    {
        $flat = [];
        foreach ($this->list as $headers) {
            foreach ($headers as $header) {
                $flat[] = $header;
            }
        }
        return new \ArrayIterator($flat);
    }
    
    /**
     * 
     * Adds a header value to an existing header label; if there is more
     * than one, it will append the new value.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function add($label, $value)
    {
        if ($label instanceof Header) {
            $header = $label;
        } else {
            $header = $this->factory->newInstance($label, $value);
        }

        $this->list[$header->getLabel()][] = $header;
    }
    
    /**
     * 
     * Sets a header value, overwriting previous values.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function set($label, $value)
    {
        if ($label instanceof Header) {
            $header = $label;
        } else {
            $header = $this->factory->newInstance($label, $value);
        }

        $this->list[$header->getlabel()] = [$header];
    }
    
    /**
     * 
     * Sets all the headers at once; replaces all previously existing headers.
     * 
     * @param array $headers An array of headers where the key is the header
     * label, and the value is the header value (multiple values are allowed).
     * 
     * @return void
     * 
     */
    public function setAll(array $headers = [])
    {
        foreach ($headers as $label => $values) {
            foreach ((array) $values as $value) {
                $this->add($label, $value);
            }
        }
    }
}
