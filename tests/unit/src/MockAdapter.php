<?php
namespace Aura\Http;

use Aura\Http\Adapter\AdapterInterface;
use Aura\Http\Message\Request;
use Aura\Http\Transport\Options;

class MockAdapter implements AdapterInterface
{
    public $request;
    public $options;
    
    public function exec(Request $request, Options $options)
    {
        $this->request = $request;
        $this->options = $options;
    }
}
