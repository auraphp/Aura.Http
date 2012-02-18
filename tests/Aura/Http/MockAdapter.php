<?php

namespace Aura\Http\Request\Adapter;

use Aura\Http\Request;

class MockAdapter implements \Aura\Http\Request\Adapter\AdapterInterface
{
    public static $request;

    public function __construct()
    {
        self::$request = [];
    }

    public function exec(Request $request)
    {
        self::$request = $request;
    }
}
