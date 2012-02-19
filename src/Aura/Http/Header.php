<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * A class representing a single header.
 * 
 * @package Aura.Header
 * 
 */
class Header
{
    /**
     * 
     * @var string The header label.
     * 
     */
    protected $label;

    /**
     * 
     * @var string The header value.
     * 
     */
    protected $value;


    public function __construct($label, $value)
    {
        $this->label = $this->sanitizeLabel($label);
        $this->value = $value;
    }

    /**
     * 
     * Magic __get
     * 
     * @return string
     * 
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * 
     * Get the header label.
     * 
     * @return string
     * 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * 
     * Get the header value.
     * 
     * @return string
     * 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 
     * Return the label and value in the HTTP header format:  `label: value`.
     * 
     * @return string
     * 
     */
    public function toString()
    {
        return sprintf('%s: %s', $this->label, $this->value);
    }

    /**
     * 
     * Return the label and value in the HTTP header format:  `label: value`.
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
     * Sanitizes header labels by removing all characters besides [a-zA-z0-9_-].
     * 
     * Underscores are converted to dashes, and word case is normalized.
     * 
     * Converts "foo \r bar_ baz-dib \n 9" to "Foobar-Baz-Dib9".
     * 
     * @param string $label The header label to sanitize.
     * 
     * @return string The sanitized header label.
     * 
     */
    protected function sanitizeLabel($label)
    {
        $label = preg_replace('/[^a-zA-Z0-9_-]/', '', $label);
        $label = ucwords(strtolower(str_replace(array('-', '_'), ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }
}