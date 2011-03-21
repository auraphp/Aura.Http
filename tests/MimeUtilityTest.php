<?php

namespace aura\http;


class MimeUtilityTest extends \PHPUnit_Framework_TestCase
{
    protected function newUtility()
    {
        return new MimeUtility;
    }

    public function testHeaderLine()
    {
        $mime     = $this->newUtility();
        $label    = 'label-foobar';
        $value    = 'value foobar';
        $actual   = $mime->headerLine($label, $value);
        $expected = 'Label-Foobar: value foobar';
        
        $this->assertEquals($expected, $actual);
    }

    public function testHeaderLabel()
    {
        $mime              = $this->newUtility();
        $unsanitized_label = "LaBel_12\r3 abc + -foo=bar\r\n";
        $expected          = 'Label-123abc-Foobar';
        $actual            = $mime->headerLabel($unsanitized_label);
        
        $this->assertEquals($expected, $actual);
    }

    // todo better testing ~ check rfc
    public function testHeaderValue()
    {
        $mime      = $this->newUtility();
        $text      = 'A value with a quoted "Leö" non ascii char';
        $expected  = 'A value with a quoted=?utf-8?Q?_"Le=C3=B6"?= non ascii char';
        
        $actual    = $mime->headerValue('Label-Foobar', $text);
        
        $this->assertEquals($expected, $actual);
        
        $text      = '"Leö was here"';
        $expected  = '=?utf-8?Q?"Le=C3=B6?= was here"';
        
        $actual    = $mime->headerValue('Label-Foobar', $text);
        
        $this->assertEquals($expected, $actual);
        
        $mime      = $this->newUtility();
        $text      = 'TheBirdIsTheWord-ö-TheBirdIsTheWord-ö-TheBirdIsTheWord-ö-TheBirdIsTheWord-ö-TheBirdIsTheWord';
        $exp_text  = str_replace("ö", '=C3=B6', $text);
        
        $expected  = '=?utf-8?Q?';
        $expected .= substr($exp_text, 0, 62);
        $expected .= "?=\r\n ";
        $expected .= '=?utf-8?Q?'; // prefix
        $expected .= substr($exp_text, 62);
        $expected .= "?="; // suffix 
        
        $actual    = $mime->headerValue('Label-Foobar', $text);
        
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeBase64()
    {
        $mime         = $this->newUtility();
        $text         = str_repeat('Foobar ', 15);
        $encoded_text = base64_encode($text);
        $expected     = substr($encoded_text, 0, 76); 
        $expected    .= "\r\n";
        $expected    .= substr($encoded_text, 76); 
        $actual       = $mime->encodeBase64($text);
        
        $this->assertEquals($expected, $actual);
    }

    public function testEncode()
    {
        $mime   = $this->newUtility();
        $text   = 'foobar'; 
        
        $actual = $mime->encode('base64', $text);
        $this->assertEquals(base64_encode($text), $actual);
        
        $actual = $mime->encode('7bit', $text);
        $this->assertEquals($text, $actual);
        
        $actual = $mime->encode('8bit', $text);
        $this->assertEquals($text, $actual);
        
        $text   = str_repeat('foobar ', 15);
        $actual = $mime->encode('quoted-printable', $text);
        $this->assertEquals(quoted_printable_encode($text), $actual);
        
        $this->setExpectedException('aura\http\Exception');
        $mime->encode('invalid', $text);
    }
}