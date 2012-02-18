<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Request\Adapter;

use Aura\Http as Http;
use Aura\Http\Request;
use Aura\Http\Headers;
use Aura\Http\Request\ResponseBuilder;

/**
 * 
 * Curl adapter for the Aura Request library.
 * 
 * @package Aura.Http
 * 
 */
class Curl implements AdapterInterface
{
    /**
     * 
     * Curl resource
     * 
     * @var resource
     * 
     */
    protected $ch;
    
    /**
     * 
     * Is the request over ssl.
     * 
     * @var bool
     * 
     */
    protected $is_secure;
    
    /**
     * 
     * @var Aura\Http\Request\ResponseBuilder
     * 
     */
    protected $builder;
    
    /**
     * 
     * List of the default curl options.
     * 
     * @var array
     * 
     */
    protected $curl_opts;

    
    /**
     * 
     * Throws an Http\Exception if the curl extension isn't loaded.
     * 
     * @param \Aura\Http\Request\ResponseBuilder $builder
     * 
     * @param array $options Adapter specific options and defaults.
     * 
     * @throws Aura\Http\Http\Exception If Curl extension is not loaded.
     * 
     */
    public function __construct(ResponseBuilder $builder, array $options = [])
    {
        if (! extension_loaded('curl')) {
            throw new Http\Exception('Curl extension is not loaded.');
        }
        
        $this->curl_opts = $options;
        $this->builder   = $builder;
    }
    
    /**
     * 
     * Execute the request.
     * 
     * @param Aura\Http\Request $request
     * 
     * @return Aura\Http\Request\ResponseStack
     * 
     */
    public function exec(Request $request)
    {
        $this->builder->setRequestUrl($request->url);
        $this->connect($request->url);
        $this->setOptions($request->options); 
        $this->setProxy($request->proxy);
        $this->setMethod($request->method);
        $this->setVersion($request->version);

        if ($this->is_secure) {
            $this->setSslOptions($request->ssl);
        }      

        // only send content if we're POST or PUT
        $send_content = $request->method == Request::POST
                     || $request->method == Request::PUT;
        
        if ($send_content) {
            $has_files = false !== strpos($request->headers->{'Content-Type'}, 
                                        'multipart/form-data');

            if (is_array($request->content) && $has_files) {
                $content = $this->flattenContent($request->content);
            } else if (is_array($request->content)) {
                // content does not contain any files
                $content = http_build_query($request->content);
            } else {
                $content = $request->content;
            }

            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $content);
        }

        $this->setHeaders($request->headers);

        $response = curl_exec($this->ch);
        
        curl_close($this->ch);

        $this->ch = null;
        
        if (null === $response) {
            throw new Http\Exception\ConnectionFailed(
                sprintf('Connection failed: (%s) %s', 
                    curl_errno($this->ch), 
                    curl_error($this->ch)));
        }
        
        $stack = $this->builder->getStack();

        if ($stack->isEmpty()) {
            throw new Http\Exception\EmptyResponse(
                sprintf('The server did not return a response. : (%s) %s', 
                    curl_errno($this->ch), 
                    curl_error($this->ch)));
        }
        
        return $stack;
    }

    /**
     * 
     * Initialize the connection.
     * 
     * @param string $url
     * 
     * @throws Http\Exception\ConnectionFailed
     * 
     */
    protected function connect($url)
    {
        $this->ch = curl_init($url);
        
        if (false === $this->ch) {
            throw new Http\Exception\ConnectionFailed(
                sprintf('Connection failed: (%s) %s', 
                    curl_errno($this->ch), 
                    curl_error($this->ch)));
        }
        
        $this->is_secure = strtolower(substr($url, 0, 5)) == 'https' ||
                           strtolower(substr($url, 0, 3)) == 'ssl';

        // set the curl options provided through the constructor.
        foreach ($this->curl_opts as $opt => $value) {
            curl_setopt($this->ch, $opt, $value);
        }
    }
    
    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    protected function setHeaders(Headers $headers)
    {
        if (isset($headers->{'User-Agent'})) {
            curl_setopt($this->ch, CURLOPT_USERAGENT, $headers->{'User-Agent'});
            unset($headers->{'User-Agent'});
        }
        
        if (isset($headers->{'Referer'})) {
            curl_setopt($this->ch, CURLOPT_REFERER, $headers->{'Referer'});
            unset($headers->{'Referer'});
        }
        
        // all remaining headers
        if (count($headers)) {
            $prepared_headers = [];

            foreach ($headers as $set) {
                foreach ($set as $header) {
                    $prepared_headers[] = $header->toString();
                }
            }

            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $prepared_headers);
        }
    }

    /**
     * 
     * Setup the remaining request options including auth, timeout, max redirects
     * and callbacks.
     * 
     * @param \ArrayObject $options
     * 
     */
    public function setOptions(\ArrayObject $options)
    {
        // save cookie to a file
        if (! empty($options->cookiejar)) {
            curl_setopt($this->ch, CURLOPT_COOKIEJAR,  $options->cookiejar);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $options->cookiejar);
        }

        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        
        // http basic or digest auth
        if (! empty($options->http_auth)) {
            $auth_types = [
                Request::BASIC  => CURLAUTH_BASIC,
                Request::DIGEST => CURLAUTH_DIGEST
            ];

            $auth_type = $auth_types[$options->http_auth[0]];
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, $auth_type);
            curl_setopt($this->ch, CURLOPT_USERPWD,  $options->http_auth[1]);
        }

        if ($options->timeout) {
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $options->timeout);
        }

        if ($options->max_redirects) {
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, $options->max_redirects);
        }

        // output
        
        // don't include the headers in the response
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        
        // return the transfer as a string instead of printing it
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
        $folder = empty($options->save_to_folder) 
                    ? false : $options->save_to_folder;
                    
        curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, 
            // bit of a kludge but we need to tell the content callback
            // where to save the content to.
            function ($ch, $content) use ($folder) {
                return $this->builder
                            ->saveContentCallback($ch, $content, $folder);
            });
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION,
                [$this->builder, 'saveHeaderCallback']);
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    protected function setProxy(\ArrayObject $proxy)
    {
        // property-name => curlopt-constant
        $var_opt = [
            'url'           => CURLOPT_PROXY,
            'port'          => CURLOPT_PROXYPORT,
            'usrpass'       => CURLOPT_PROXYUSERPWD,
        ];
        
        // set other behaviours
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if (isset($proxy->$var) && $proxy->$var !== null) {
                curl_setopt($this->ch, $opt, $proxy->$var);
            }
        }

        curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }

    /**
     *
     *
     * @param 
     *
     * @return 
     *
     */
    protected function setSslOptions(\ArrayObject $ssl)
    {
        // property-name => curlopt-constant
        $var_opt = array(
            'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
            'ssl_cafile'      => CURLOPT_CAINFO,
            'ssl_capath'      => CURLOPT_CAPATH,
            'ssl_local_cert'  => CURLOPT_SSLCERT,
            'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
        );
        
        // set other behaviors
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero
            // values are honored
            if (isset($ssl->$var) && $ssl->$var !== null) {
                curl_setopt($this->ch, $opt, $ssl->$var);
            }
        }
    }
    
    /**
     * 
     * Set the HTTP version.
     *
     * @param string $version
     *
     */
    protected function setVersion($version)
    {
        switch ($version) {

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

    /**
     * 
     * Set the HTTP request method.
     * 
     * @param string $method
     *
     */
    protected function setMethod($method)
    {
        switch ($method) {

        case 'GET':
            curl_setopt($this->ch, CURLOPT_HTTPGET, true);
            break;
            
        case Request::POST:
            curl_setopt($this->ch, CURLOPT_POST, true);
            break;
            
        case 'PUT':
            curl_setopt($this->ch, CURLOPT_PUT, true);
            break;
            
        case 'HEAD':
            curl_setopt($this->ch, CURLOPT_HEAD, true);
            break;
            
        default:
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
            break;
        }
    }

    /**
     * 
     * Flatten a multidimensional array.
     * 
     * [foo => [1,2]] becomes ['foo[0]' => 1, 'foo[1]' => 2]
     * 
     * @param array $array
     * 
     * @param array $return
     * 
     * @param string $prefix
     * 
     * @return array
     * 
     */
    protected function flattenContent(
        array $array, array $return = [], $prefix = '')
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $_prefix = $prefix ? $prefix . '[' . $key . ']' : $key;
                $return  = $this->flattenContent($value, $return, $_prefix); 
            } else {
                $_key          = $prefix ? $prefix . '[' . $key . ']' : $key;
                $return[$_key] = $value;
            }
        }

        return $return;
    }
}