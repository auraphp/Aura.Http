<?php
namespace Aura\Http\_Config;

use Aura\Di\_Config\AbstractContainerTest;

class CommonTest extends AbstractContainerTest
{
    protected function getConfigClasses()
    {
        return array(
            'Aura\Http\_Config\Common',
        );
    }

    public function provideGet()
    {
        return array(
            array('aura/http:transport', 'Aura\Http\Transport'),
            array('aura/http:http', 'Aura\Http\Http'),
        );
    }

    public function provideNewInstance()
    {
        return array(
            array('Aura\Http\Adapter\CurlAdapter'),
            array('Aura\Http\Adapter\StreamAdapter'),
            array('Aura\Http\Cookie\CookieCollection'),
            array('Aura\Http\Header\HeaderCollection'),
            array('Aura\Http\Http'),
            array('Aura\Http\Message\Message'),
            array('Aura\Http\Message\Response\StackBuilder'),
            array('Aura\Http\Multipart\FormData'),
            array('Aura\Http\Transport'),
        );
    }
}
