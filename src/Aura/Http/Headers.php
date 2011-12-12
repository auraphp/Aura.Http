<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * Collection of non-cookie HTTP headers.
 * 
 * @package Aura.Http
 * 
 */
class Headers
{
    protected $list = array();
    
    public function add($label, $value)
    {
        $label = $this->sanitizeLabel($label);
        $this->list[$label][] = $value;
    }
    
    public function set($label, $value)
    {
        $label = $this->sanitizeLabel($label);
        $this->list[$label] = array($value);
    }
    
    public function getAll()
    {
        return $this->list;
    }
    
    public function setAll(array $headers = array())
    {
        $this->store = array();
        foreach ($headers as $label => $values) {
            foreach ((array) $values as $value) {
                $this->add($label, $value);
            }
        }
    }
    
    public function send()
    {
        foreach ($this->list as $label => $values) {
            foreach ($values as $value) {
                header("$label: $value");
            }
        }
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
