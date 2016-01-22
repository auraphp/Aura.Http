<?php
namespace Aura\Http\Adapter;

use Aura\Http\Message\MessageFactory;
use Aura\Http\Message\ResponseStackBuilder;
use org\bovigo\vfs\vfsStream;

class CurlAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    protected function setUp()
    {
        $this->adapter = new CurlAdapter(
            new ResponseStackBuilder(new MessageFactory)
        );
    }

    public function testRebuildWithCurlFile()
    {
        $expected = $values = array(
            'foo' => 'bar',
            'baz' => '@/hello.txt',
            'files' => array(
                'another' => 'value',
                'andfile' => '@/anotherfile'
            )
        );
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $this->assertNotSame($expected, $this->adapter->rebuildWithCurlFile($values));
        } else {
            $this->assertSame($expected, $this->adapter->rebuildWithCurlFile($values));
        }
    }
}
