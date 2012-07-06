<?php
namespace Aura\Http\Manager;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    
    protected function setUp()
    {
        $this->factory = new Factory;
    }
    
    public function testNewInstance_curl()
    {
        $manager = $this->factory->newInstance('curl');
        $this->assertInstanceOf('Aura\Http\Manager', $manager);
        $this->assertInstanceof('Aura\Http\Adapter\Curl', $manager->transport->adapter);
    }
    
    public function testNewInstance_stream()
    {
        $manager = $this->factory->newInstance('stream');
        $this->assertInstanceOf('Aura\Http\Manager', $manager);
        $this->assertInstanceof('Aura\Http\Adapter\Stream', $manager->transport->adapter);
    }
    
    public function testNewInstance_unknown()
    {
        $this->setExpectedException('Aura\Http\Exception');
        $manager = $this->factory->newInstance('unknown');
    }
}
