<?php
namespace Aura\Http;

class MockPhpFunc extends PhpFunc
{
    public $headers = [];
    public $cookies = [];
    public $headers_sent = false;
    public $file;
    public $line;
    
    public function reset()
    {
        $this->headers      = [];
        $this->cookies      = [];
        $this->headers_sent = false;
    }
    
    // mock the function for this namespace
    public function header($string)
    {
        $this->headers[] = $string;
        $this->headers_sent = true;
    }

    // mock the function for this namespace
    function headers_sent(&$file, &$line)
    {
        if ($this->headers_sent) {
            $this->file = $file;
            $this->line = $line;
            return true;
        } else {
            return false;
        }
    }

    // mock the function for this namespace
    function setcookie(
        $name,
        $value,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = false
    ) {
        $this->cookies[] = [
            'name'     => $name,
            'value'    => $value,
            'expire'   => $expire,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httponly,
        ];
    }
}
