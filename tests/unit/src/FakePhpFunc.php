<?php
namespace Aura\Http;

class FakePhpFunc extends PhpFunc
{
    public $headers = [];
    public $cookies = [];
    public $headers_sent = false;
    public $file;
    public $line;
    public $content = null;

    public function reset()
    {
        $this->headers      = [];
        $this->cookies      = [];
        $this->content      = null;
        $this->headers_sent = false;
    }

    public function header($string)
    {
        $this->headers[] = $string;
        $this->headers_sent = true;
    }

    public function headers_sent(&$file, &$line)
    {
        if ($this->headers_sent) {
            $this->file = $file;
            $this->line = $line;
            return true;
        } else {
            return false;
        }
    }

    public function setcookie(
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

    public function output($text)
    {
        $this->content .= $text;
    }
}
