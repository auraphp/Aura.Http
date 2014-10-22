<?php
namespace Aura\Http;

use Aura\Framework\Test\WiringAssertionsTrait;

class WiringTest extends \PHPUnit_Framework_TestCase
{
    use WiringAssertionsTrait;

    protected function setUp()
    {
        $this->loadDi();
    }

    public function testServices()
    {
        $this->assertGet('http_transport', 'Aura\Http\Transport');
        $this->assertGet('http_manager', 'Aura\Http\Manager');
    }

    public function testInstances()
    {
        $this->assertNewInstance('Aura\Http\Adapter\CurlAdapter');
        $this->assertNewInstance('Aura\Http\Adapter\StreamAdapter');
        $this->assertNewInstance('Aura\Http\Cookie\CookieCollection');
        $this->assertNewInstance('Aura\Http\Header\Collection');
        $this->assertNewInstance('Aura\Http\Manager');
        $this->assertNewInstance('Aura\Http\Message');
        $this->assertNewInstance('Aura\Http\Message\Response\StackBuilder');
        $this->assertNewInstance('Aura\Http\Multipart\FormData');
        $this->assertNewInstance('Aura\Http\Transport');
    }
}
