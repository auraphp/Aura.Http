<?php
namespace Aura\Http\Message;

use Aura\Http\Content;
use Aura\Http\Cookie\Collection as Cookies;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Collection as Headers;
use Aura\Http\Header\Factory as HeaderFactory;

class Factory
{
    protected $map = array(
        'message'  => 'Aura\Http\Message',
        'request'  => 'Aura\Http\Message\Request',
        'response' => 'Aura\Http\Message\Response',
    );
    
    public function newInstance($type)
    {
        $class = $this->map[$type];
        $headers = new Headers(new HeaderFactory);
        $cookies = new Cookies(new CookieFactory);
        $content = new Content(new Headers(new HeaderFactory));
        return new $class($headers, $cookies, $content);
    }
}
