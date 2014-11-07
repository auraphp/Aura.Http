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

    protected function getAutoResolve()
    {
        return false;
    }

    public function provideGet()
    {
        return array(
            array('aura/http:transport', 'Aura\Http\Transport\Transport'),
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
            array('Aura\Http\Message\Request'),
            array('Aura\Http\Message\Response'),
            array('Aura\Http\Message\ResponseStackBuilder'),
            array('Aura\Http\Multipart\FormData'),
            array('Aura\Http\Transport\Transport'),
        );
    }
}
