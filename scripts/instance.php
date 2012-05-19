<?php
namespace Aura\Http;
use Aura\Http\Cookie\Factory as CookieFactory;
use Aura\Http\Header\Factory as HeaderFactory;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
return new Response(new Headers(new HeaderFactory), new Cookies(new CookieFactory));
