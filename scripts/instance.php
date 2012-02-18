<?php
namespace Aura\Http;
use Aura\Http\Factory\Cookie as CookieFactory;
use Aura\Http\Factory\Header as HeaderFactory;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
return new Response(new Headers(new HeaderFactory), new Cookies(new CookieFactory));
