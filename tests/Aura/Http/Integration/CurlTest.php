<?php
namespace Aura\Http\Integration;

use Aura\Http\Manager\Factory;

class CurlTest extends AbstractTest
{
    protected function setUp()
    {
        $factory = new Factory;
        $this->manager = $factory->newInstance('curl');
    }
}
