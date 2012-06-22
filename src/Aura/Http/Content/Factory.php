<?php
namespace Aura\Http\Content;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;

class Factory
{
    public function newSinglePart()
    {
        return new SinglePart(new Headers(new HeaderFactory));
    }
    
    public function newMultiPart()
    {
        return new MultiPart(new Headers(new HeaderFactory), new Factory);
    }
}
