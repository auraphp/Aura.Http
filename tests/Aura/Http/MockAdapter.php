<?php

namespace Aura\Http;

use Aura\Http\Request;
use Aura\Http\Transport\\AdapterInterface;

class MockAdapter implements AdapterInterface
{
    public $request;

    public function exec(Request $request)
    {
        $this->request = $request;
    }
}
