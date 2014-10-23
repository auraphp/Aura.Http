<?php
namespace Aura\Http\Header;

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

    // public function testSetHeaderSanitizesLabel()
    // {
    //     $this->message->setHeader("key\r\n-=foo", 'value');
    //     $this->transport->sendRequest($req);
    //     $this->assertTrue(array_key_exists('Key-Foo', Fake::$request->headers->getAll()));;
    // }
    //
    // public function testSetHeaderDeleteHeaderWithNullOrFalseValue()
    // {
    //     $req     = $this->newRequest();
    //
    //     // false
    //     $this->message->setHeader("key", 'value');
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertTrue(isset(Fake::$request->headers->Key));
    //
    //     $this->message->setHeader("key", false);
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertFalse(isset(Fake::$request->headers->Key));
    //
    //     // null
    //     $this->message->setHeader("key", 'value');
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertTrue(isset(Fake::$request->headers->Key));
    //
    //     $this->message->setHeader("key", null);
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertFalse(isset(Fake::$request->headers->Key));
    // }
    //
    // public function testSetHeaderReplaceValue()
    // {
    //     $req     = $this->newRequest();
    //
    //     $this->message->setHeader("key", 'value');
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertSame('value', Fake::$request->headers->Key->getValue());
    //
    //     $this->message->setHeader("key", 'value2');
    //
    //     $this->transport->sendRequest($req);
    //
    //     $this->assertSame('value2', Fake::$request->headers->Key->getValue());
    // }
    //
    // public function testSetHeaderMultiValue()
    // {
    //     $req     = $this->newRequest();
    //
    //     $this->message->setHeader("key", 'value', false);
    //     $this->message->setHeader("key", 'value2', false);
    //     $this->transport->sendRequest($req);
    //
    //     $expected = ['value', 'value2'];
    //
    //     foreach (Fake::$request->headers->get('Key') as $i => $value) {
    //         $this->assertSame('Key', $value->getLabel());
    //         $this->assertSame($expected[$i], $value->getValue());
    //     }
    // }
}