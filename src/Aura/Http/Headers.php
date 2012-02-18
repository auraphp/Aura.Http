<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

use Aura\Http\Factory\Header as HeaderFactory;

/**
 * 
 * Collection of non-cookie HTTP headers.
 * 
 * @package Aura.Http
 * 
 */
class Headers implements \IteratorAggregate, \Countable
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
     * @var Aura\Http\Factory\Header
     * 
     */
    protected $factory;

    /**
     *
     * @param Aura\Http\Factory\Header $factory
     *
     */
    public function __construct(HeaderFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * 
     * Reset the list of headers.
     * 
     */
    public function __clone()
    {
        $this->list = [];
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
     * @param boolean $list If true an array of `Aura\Http\Header` is returned 
     * else a `Aura\Http\Header` is return with the first result.
     * 
     * @return Aura\Http\Header|array
     * 
     */
    public function get($label, $list = true)
    {
        $header = $this->factory->newInstance($label, null);
        $label  = $header->getLabel();

        if ($list) {
            return $this->list[$label];
        }

        return $this->list[$label][0];
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
        return new \ArrayIterator($this->list);
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
    
    /**
     * 
     * Sends all the headers using `header()`.
     * 
     * @return void
     * 
     */
    public function send()
    {
        foreach ($this->list as $values) {
            foreach ($values as $header) {
                header($header->toString());
            }
        }
    }
}
