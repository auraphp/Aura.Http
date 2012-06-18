<?php
namespace Aura\Http\Message;

use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;

class Factory
{
    protected $map = array(
        'message'  => 'Aura\Http\Message',
        'request'  => 'Aura\Http\Request',
        'response' => 'Aura\Http\Response',
    );
    
    public function newInstance($type)
    {
        $class = $this->map[$type];
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        return new $class($headers, $cookies);
    }
}
