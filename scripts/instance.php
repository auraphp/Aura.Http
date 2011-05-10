<?php
namespace aura\http;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';
return new Response(new Headers, new Cookies);
