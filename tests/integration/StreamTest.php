<?php
namespace Aura\Http\Integration;

use Aura\Http\HttpFactory;

class StreamTest extends AbstractTest
{
    protected function setUp()
    {
        parent::setUp();
        $factory = new HttpFactory;
        $this->http = $factory->newInstance('stream');
    }
}
