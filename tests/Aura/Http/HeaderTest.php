<?php

namespace Aura\Http;

class HeaderTest extends \PHPUnit_Framework_TestCase
{

    public function testLabelIsSanitized()
    {
        $header = new Header("A-\rMessed-\nUP_+=@LaBEl", 'value');

        $this->assertEquals('A-Messed-Up-Label', $header->getLabel());
    }

    public function test__get()
    {
        $header = new Header("label", 'value');

        $this->assertEquals('Label', $header->label);
        $this->assertEquals('value', $header->value);
        $this->assertNull($header->no_such_property);
    }

    public function testGetLabel()
    {
        $header = new Header("label", 'value');

        $this->assertEquals('Label', $header->getLabel());
    }

    public function testGetValue()
    {
        $header = new Header("label", 'value');

        $this->assertEquals('value', $header->getValue());
    }

    public function test__toString()
    {
        $header = new Header("label", 'value');

        $this->assertEquals('Label: value', $header->__toString());
    }
}