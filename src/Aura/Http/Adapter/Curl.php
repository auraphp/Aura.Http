<?php
namespace Aura\Http\Adapter;

use Aura\Http\Response\StackBuilder;
use Aura\Http\Exception;
use Aura\Http\Request;
use Aura\Http\Transport\Options;

class Curl implements AdapterInterface
{
    protected $stack_builder;
    
    protected $request;
    
    protected $options;
    
    protected $ch;
    
    public function __construct(StackBuilder $stack_builder)
    {
        if (! extension_loaded('curl')) {
            throw new Exception("Extension 'curl' not loaded");
        }
        $this->stack_builder = $stack_builder;
    }
    
    /**
     * 
     * Make the request, then return an array of headers and content.
     * 
     * @param Request The request to send.
     * 
     * @return array A sequential array where element 0 is a sequential array of
     * header lines, and element 1 is the body content.
     * 
     * @todo Implement an exception for timeouts.
     * 
     */
    public function exec(Request $request, Options $options)
    {
        $this->request = $request;
        $this->options = $options;
        
        $this->ch = curl_init($this->request->uri);
        
        $this->setBasicOptions();
        $this->setSecureOptions();
        $this->setProxyOptions();
        $this->setMethod();
        $this->setHttpVersion();
        $this->setAuth();
        $this->setHeaders();
        $this->setContent();
        
        // send the request via curl and retain the response
        $response = curl_exec($this->ch);
        
        // did we hit any errors?
        if ($response === false || $response === null) {
            $text = 'Connection failed: '
                  . curl_errno($this->ch)
                  . ' '
                  . curl_error($this->ch);
            throw new Exception($text);
        }
        
        // get the metadata and close the connection
        $meta = curl_getinfo($this->ch);
        curl_close($this->ch);
        
        // get the header lines from the response
        $headers = explode(
            "\r\n",
            substr($response, 0, $meta['header_size'])
        );
        
        // get the content portion from the response
        $content = substr($response, $meta['header_size']);
        
        // done!
        $stack = $this->stack_builder->newInstance($headers, $content);
        return $stack;
    }
    
    protected function setBasicOptions()
    {
        // convert Unix newlines to CRLF newlines on transfers.
        curl_setopt($this->ch, CURLOPT_CRLF, true);
        
        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        
        // include the headers in the response
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        
        // return the transfer as a string instead of printing it
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
        // property-name => curlopt-constant
        $this->setOptions([
            'max_redirects' => CURLOPT_MAXREDIRS,
            'timeout'       => CURLOPT_TIMEOUT,
        ]);
    }
    
    protected function setOptions($var_opt)
    {
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->options->$var !== null) {
                curl_setopt($this->ch, $opt, $this->options->$var);
            }
        }
    }
    
    protected function setProxyOptions()
    {
        if (! $this->options->proxy) {
            return;
        }
        
        curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        $this->setOptions([
            'proxy'         => CURLOPT_PROXY,
            'proxy_port'    => CURLOPT_PROXYPORT,
        ]);
        curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->options->getProxyUserPass());
    }
    
    protected function setSecureOptions()
    {
        $this->setOptions([
            'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
            'ssl_cafile'      => CURLOPT_CAINFO,
            'ssl_capath'      => CURLOPT_CAPATH,
            'ssl_local_cert'  => CURLOPT_SSLCERT,
            'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
        ]);
    }
    
    protected function setHttpVersion()
    {
        switch ($this->request->version) {
            case '1.0':
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
            case '1.1':
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
            default:
                // let curl decide
                curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
                break;
        }
    }
    
    protected function setMethod()
    {
        switch ($this->request->method) {
            case Request::METHOD_GET:
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
            case Request::METHOD_POST:
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
            case Request::METHOD_PUT:
                curl_setopt($this->ch, CURLOPT_PUT, true);
                break;
            case Request::METHOD_HEAD:
                curl_setopt($this->ch, CURLOPT_HEAD, true);
                break;
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->request->method);
                break;
        }
    }
    
    protected function setAuth()
    {
        $auth = $this->request->auth;
        if (! $auth) {
            return;
        }
        
        switch ($auth) {
            case Request::AUTH_BASIC:
                curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                break;
            case Request::AUTH_DIGEST:
                curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                break;
            default:
                throw new Exception("Unknown auth type '$auth'.");
                break;
        }
        
        $credentials = $this->request->getCredentials();
        curl_setopt($this->ch, CURLOPT_USERPWD, $credentials);
    }
    
    protected function setHeaders()
    {
        $headers = [];
        foreach ($this->request->getHeaders() as $header) {
            switch ($header->getLabel()) {
                case 'User-Agent':
                    curl_setopt($this->ch, CURLOPT_USERAGENT, $header->getValue());
                    break;
                case 'Referer':
                    curl_setopt($this->ch, CURLOPT_REFERER, $header->getValue());
                    break;
                default:
                    $headers[] = $header->__toString();
                    break;
            }
        }
        
        // set remaining headers
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        
        // set cookies
        $cookies = $this->request->getCookies()->__toString();
        if ($cookies) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $value);
            break;
        }
    }
    
    protected function setContent()
    {
        $method  = $this->request->method;
        $content = $this->request->content;
        
        // only send content if we're POST or PUT
        $send_content = $method == Request::METHOD_POST
                     || $method == Request::METHOD_PUT;
        
        if ($send_content && ! empty($content)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
        }
    }
}
