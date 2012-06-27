<?php
namespace Aura\Http\Cookie;

class JarFactory
{
    public function newInstance($file)
    {
        return new Jar(new Factory, $file);
    }
}
