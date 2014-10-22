<?php
namespace Aura\Http;

class HttpFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected function setUp()
    {
        $this->factory = new HttpFactory;
    }

    public function testNewInstance_curl()
    {
        $http = $this->factory->newInstance('curl');
        $this->assertInstanceOf('Aura\Http\Http', $http);
        $this->assertInstanceof('Aura\Http\Adapter\CurlAdapter', $http->transport->adapter);
    }

    public function testNewInstance_stream()
    {
        $http = $this->factory->newInstance('stream');
        $this->assertInstanceOf('Aura\Http\Http', $http);
        $this->assertInstanceof('Aura\Http\Adapter\StreamAdapter', $http->transport->adapter);
    }

    public function testNewInstance_unknown()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $manager = $this->factory->newInstance('unknown');
    }
}
