<?php
namespace Aura\Http\Adapter;

use Aura\Http\Exception;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response\StackBuilder;
use Aura\Http\Transport\Options;

class Curl implements AdapterInterface
{
    protected $stack_builder;
    
    protected $request;
    
    protected $options;
    
    protected $headers;
    
    protected $content;
    
    protected $curl;
    
    protected $save;
    
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
     * @param Request $request The request to send.
     * 
     * @param Options $options The transport options.
     * 
     * @return Aura\Http\Response\Stack
     * 
     * @todo Implement an exception for timeouts.
     * 
     */
    public function exec(Request $request, Options $options)
    {
        $this->request = $request;
        $this->options = $options;
        
        // create the handle, then connect and read
        $this->setCurl();
        $this->connect();
        
        // build a stack
        $stack = $this->stack_builder->newInstance(
            $this->headers,
            $this->content,
            $this->request->uri
        );
        
        // done!
        return $stack;
    }
    
    protected function setCurl()
    {
        $this->curl = curl_init($this->request->uri);
        $this->curlBasicOptions();
        $this->curlSecureOptions();
        $this->curlProxyOptions();
        $this->curlMethod();
        $this->curlHttpVersion();
        $this->curlAuth();
        $this->curlHeaders();
        $this->curlContent();
        $this->curlSave();
    }
    
    protected function connect()
    {
        // send the request via curl and retain the response
        $response = curl_exec($this->curl);
        
        // did we hit any errors?
        if ($response === false || $response === null) {
            $text = 'Connection failed: '
                  . curl_errno($this->curl)
                  . ' '
                  . curl_error($this->curl);
            throw new Exception($text);
        }
        
        // close the connection
        curl_close($this->curl);
        
        // convert headers to an array, removing the trailing blank lines
        $this->headers = explode("\r\n", rtrim($this->headers));
        
        // did we save the response to a file?
        if ($this->save) {
            // close the existing file handle ...
            fclose($this->save);
            // ... and re-open for reading as content
            $this->content = fopen($this->request->getSaveToFile(), 'rb');
        } else {
            // the content is the response text
            $this->content = $response;
        }
    }
    
    protected function curlBasicOptions()
    {
        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        
        // do not include headers in the content ...
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        
        // ... instead, save headers using a callback
        curl_setopt(
            $this->curl,
            CURLOPT_HEADERFUNCTION,
            [$this, 'saveHeaders']
        );
        
        // return the transfer as a string instead of printing it
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        
        // property-name => curlopt-constant
        $this->curlSetopt([
            'max_redirects' => CURLOPT_MAXREDIRS,
            'timeout'       => CURLOPT_TIMEOUT,
        ]);
    }
    
    protected function curlSetopt($var_opt)
    {
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->options->$var !== null) {
                curl_setopt($this->curl, $opt, $this->options->$var);
            }
        }
    }
    
    protected function curlProxyOptions()
    {
        if (! $this->options->proxy) {
            return;
        }
        
        curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        $this->curlSetopt([
            'proxy'         => CURLOPT_PROXY,
            'proxy_port'    => CURLOPT_PROXYPORT,
        ]);
        curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, $this->options->getProxyCredentials());
    }
    
    protected function curlSecureOptions()
    {
        $this->curlSetopt([
            'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
            'ssl_cafile'      => CURLOPT_CAINFO,
            'ssl_capath'      => CURLOPT_CAPATH,
            'ssl_local_cert'  => CURLOPT_SSLCERT,
            'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
        ]);
    }
    
    protected function curlHttpVersion()
    {
        switch ($this->request->version) {
            case '1.0':
                curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
            case '1.1':
                curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
            default:
                // let curl decide
                curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
                break;
        }
    }
    
    protected function curlMethod()
    {
        switch ($this->request->method) {
            case Request::METHOD_GET:
                curl_setopt($this->curl, CURLOPT_HTTPGET, true);
                break;
            case Request::METHOD_POST:
                curl_setopt($this->curl, CURLOPT_POST, true);
                break;
            case Request::METHOD_PUT:
                curl_setopt($this->curl, CURLOPT_PUT, true);
                break;
            case Request::METHOD_HEAD:
                curl_setopt($this->curl, CURLOPT_HEAD, true);
                break;
            default:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->request->method);
                break;
        }
    }
    
    protected function curlAuth()
    {
        $auth = $this->request->auth;
        if (! $auth) {
            return;
        }
        
        switch ($auth) {
            case Request::AUTH_BASIC:
                curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                break;
            case Request::AUTH_DIGEST:
                curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                break;
            default:
                throw new Exception("Unknown auth type '$auth'.");
                break;
        }
        
        $credentials = $this->request->getCredentials();
        curl_setopt($this->curl, CURLOPT_USERPWD, $credentials);
    }
    
    protected function curlHeaders()
    {
        $headers = [];
        foreach ($this->request->getHeaders() as $header) {
            switch ($header->getLabel()) {
                case 'User-Agent':
                    curl_setopt($this->curl, CURLOPT_USERAGENT, $header->getValue());
                    break;
                case 'Referer':
                    curl_setopt($this->curl, CURLOPT_REFERER, $header->getValue());
                    break;
                default:
                    $headers[] = $header->__toString();
                    break;
            }
        }
        
        // set remaining headers
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        
        // set cookies
        $cookies = $this->request->getCookies()->__toString();
        if ($cookies) {
            curl_setopt($this->curl, CURLOPT_COOKIE, $value);
            break;
        }
    }
    
    protected function curlContent()
    {
        curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        
        // get the content
        $content = $this->request->content;
        
        // send only if non-empty
        if (! $content) {
            return;
        }
        
        // send only for POST or PUT
        $method = $this->request->method;
        $post_or_put = $method == Request::METHOD_POST
                    || $method == Request::METHOD_PUT;
        if (! $post_or_put) {
            return;
        }
        
        // what kind of content?
        if (is_resource($content)) {
            // a file resource
            curl_setopt($this->curl, CURLOPT_INFILE, $content);
        } else {
            // anything else
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $content);
        }
    }
    
    protected function curlSave()
    {
        $file = $this->request->getSaveToFile();
        if (! $file) {
            return;
        }
        
        // open a file handle for saving
        $this->save = fopen($file, 'wb');
        
        // callback for saving response content
        curl_setopt(
            $this->curl,
            CURLOPT_WRITEFUNCTION,
            [$this, 'saveContent']
        );
    }
    
    public function saveHeaders($curl, $data)
    {
        $this->headers .= $data;
        return strlen($data);
    }
    
    public function saveContent($curl, $data)
    {
        fwrite($this->save, $data);
        return strlen($data);
    }
}
