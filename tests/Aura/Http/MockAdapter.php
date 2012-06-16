<?php

namespace Aura\Http;

use Aura\Http\Request;
use Aura\Http\Adapter\AdapterInterface;

class MockAdapter implements AdapterInterface
{
    public static $request;

    public function exec(Request $request)
    {
        self::$request = $request;
    }
}
