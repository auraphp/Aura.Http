<?php
namespace Aura\Http\Transport;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->options = new Options;
    }
    
    public function testSetAndGetMaxRedirects()
    {
        $expect = 99;
        $this->options->setMaxRedirects($expect);
        $this->assertSame($expect, $this->options->max_redirects);
    }
    
    public function testSetAndGetTimeout()
    {
        $expect = 9.9;
        $this->options->setTimeout($expect);
        $this->assertSame($expect, $this->options->timeout);
    }
    
    public function testSetAndGetProxy()
    {
        $expect = 'http://proxy.example.com';
        $this->options->setProxy($expect);
        $this->assertSame($expect, $this->options->proxy);
    }
    
    public function testSetAndGetProxyPort()
    {
        $expect = '12345';
        $this->options->setProxyPort($expect);
        $this->assertSame($expect, $this->options->proxy_port);
    }
    
    public function testSetAndGetProxyUsername()
    {
        $expect = 'user';
        $this->options->setProxyUsername($expect);
        $this->assertSame($expect, $this->options->proxy_username);
    }
    
    public function testSetAndGetProxyPassword()
    {
        $expect = 'pass';
        $this->options->setProxyPassword($expect);
        $this->assertSame($expect, $this->options->proxy_password);
    }
    
    public function testGetProxyCredentials()
    {
        $this->options->setProxyUsername('user');
        $this->options->setProxyPassword('pass');
        $expect = 'user:pass';
        $this->assertSame($expect, $this->options->getProxyCredentials());
    }
    
    public function testSetAndGetSslVerifyPeer()
    {
        $expect = true;
        $this->options->setSslVerifyPeer($expect);
        $this->assertSame($expect, $this->options->ssl_verify_peer);
    }
    
    public function testSetAndGetSslCafile()
    {
        $expect = '/path/to/cafile';
        $this->options->setSslCafile($expect);
        $this->assertSame($expect, $this->options->ssl_cafile);
    }
    
    public function testSetAndGetSslCapath()
    {
        $expect = '/path/to/capath';
        $this->options->setSslCapath($expect);
        $this->assertSame($expect, $this->options->ssl_capath);
    }
    
    public function testSetAndGetSslLocalCert()
    {
        $expect = '/path/to/local_cert';
        $this->options->setSslLocalCert($expect);
        $this->assertSame($expect, $this->options->ssl_local_cert);
    }
    
    public function testSetAndGetSslPassphrase()
    {
        $expect = 'passphrase';
        $this->options->setSslPassphrase($expect);
        $this->assertSame($expect, $this->options->ssl_passphrase);
    }
}
