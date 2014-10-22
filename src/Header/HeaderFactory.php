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
namespace Aura\Http\Header;

/**
 *
 * Factory to create new Header objects.
 *
 * @package Aura.Http
 *
 */
class HeaderFactory
{

    /**
     *
     * Creates and returns a new Header object.
     *
     * @param string $label Header label.
     *
     * @param string $value Header value.
     *
     * @return Header
     *
     */
    public function newInstance($label, $value)
    {
        return new Header($label, $value);
    }
}
