<?php
namespace Aura\Http;

use Aura\Http\Adapter\AdapterInterface;
use Aura\Http\Message\Request;
use Aura\Http\Transport\TransportOptions;

class FakeAdapter implements AdapterInterface
{
    public $request;
    public $options;

    public function exec(Request $request, TransportOptions $options)
    {
        $this->request = $request;
        $this->options = $options;
    }
}
