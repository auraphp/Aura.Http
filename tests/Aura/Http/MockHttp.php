<?php
namespace Aura\Http;

// mock the function for this namespace
function header($string)
{
    MockHttp::$headers[] = $string;
    MockHttp::$headers_sent = true;
}

// mock the function for this namespace
function headers_sent(&$file, &$line)
{
    if (MockHttp::$headers_sent) {
        $file = __FILE__;
        $line = __LINE__;
        return true;
    } else {
        return false;
    }
}

// mock the function for this namespace
function setcookie($name, $value, $expire = 0, $path = null,
    $domain = null, $secure = false, $httponly = false
) {
    MockHttp::$cookies[] = [
        'name'     => $name,
        'value'    => $value,
        'expire'   => $expire,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => $secure,
        'httponly' => $httponly,
    ];
}

// retains results of mocked functions
class MockHttp
{
    static public $headers = [];
    static public $cookies = [];
    static public $headers_sent = false;
    static public function reset()
    {
        self::$headers = [];
        self::$cookies = [];
        self::$headers_sent = false;
    }
}
