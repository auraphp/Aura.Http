<?php
namespace Aura\Http;

class PhpFuncTest extends \PHPUnit_Framework_TestCase
{
    protected $phpfunc;
    
    protected function setUp()
    {
        $this->phpfunc = new PhpFunc;
    }
    
    public function test__call()
    {
        $expect = '1 2 3';
        $actual = $this->phpfunc->implode(' ', [1, 2, 3]);
    }
    
    public function testOutput()
    {
        $expect = 'Hello World!';
        ob_start();
        $this->phpfunc->output($expect);
        $actual = ob_get_clean();
        $this->assertSame($expect, $actual);
    }
    
    public function testHeadersSent()
    {
        // why the hell is this true?
        $this->assertTrue($this->phpfunc->headers_sent($file, $line));
    }
}
