<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http;

/**
 * 
 * A class representing a single header.
 * 
 * @package Aura.Http
 * 
 */
class Header
{
    /**
     * 
     * The header label.
     * 
     * @var string
     * 
     */
    protected $label;

    /**
     * 
     * The header value.
     * 
     * @var string
     * 
     */
    protected $value;

    /**
     * 
     * Constructor.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     */
    public function __construct($label, $value)
    {
        $this->setLabel($label);
        $this->setValue($value);
    }

    /**
     * 
     * Magic get for label and value.
     * 
     * @param string $key The property to get.
     * 
     * @return string
     * 
     */
    public function __get($key)
    {
        if ($key == 'label') {
            return $this->getLabel();
        }

        if ($key == 'value') {
            return $this->getValue();
        }
    }

    /**
     * 
     * Returns this header object as a "label: value" string.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        $label = $this->getLabel();
        $value = $this->getValue();
        return "{$label}: {$value}";
    }

    /**
     * 
     * Sets the header label after sanitizing and normalizing it.
     * 
     * @param string $label The header label.
     * 
     * @return void
     * 
     */
    protected function setLabel($label)
    {
        // sanitize
        $label = preg_replace('/[^a-zA-Z0-9_-]/', '', $label);

        // normalize
        $label = ucwords(
            strtolower(
                str_replace(array('-', '_'), ' ', $label)
            )
        );
        $label = str_replace(' ', '-', $label);

        // set
        $this->label = $label;
    }

    /**
     * 
     * Gets the header label.
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
     * Sets the header value after sanitizing it.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function setValue($value)
    {
        $this->value = str_replace(["\r", "\n"], "", $value);
    }

    /**
     * 
     * Gets the header value.
     * 
     * @return string
     * 
     */
    public function getValue()
    {
        return $this->value;
    }
}
 