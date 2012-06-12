<?php
namespace Aura\Http\Adapter;

use Aura\Http\Response\StackBuilder;
use Aura\Http\Exception;
use Aura\Http\Request;
use Aura\Http\Transport\Options;

class Stream implements AdapterInterface
{
    protected $stack_builder;
    
    protected $request;
    
    protected $options;
    
    protected $context;
    
    public function __construct(StackBuilder $stack_builder)
    {
        if (! ini_get('allow_url_fopen')) {
            $msg = "PHP setting 'allow_url_fopen' is off.";
            throw new Http\Exception($msg);
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
        $this->setContext();
        
        // connect to the uri (suppress errors and deal with them later)
        $level = error_reporting(0);
        $stream = fopen($this->request->uri, 'r', false, $this->context);
        error_reporting($level);
        
        // did we hit any errors?
        if ($stream === false) {
            
            // the $http_response_header variable is automatically created
            // by the streams extension
            if (empty($http_response_header)) {
                // no server response, must be some other error
                $info = error_get_last();
                throw new Exception\ConnectionFailed($info);
            }
            
            // server responded, but there's no content
            $headers = $http_response_header;
            $content = null;
            
        } else {
            
            // get the response message
            $content = stream_get_contents($stream);
            $meta = stream_get_meta_data($stream);
            fclose($stream);
            
            // did it time out?
            if ($meta['timed_out']) {
                throw new Exception\ConnectionTimeout($uri);
            }
            
            // if php was compiled with --with-curlwrappers, then the field
            // 'wrapper_data' contains two arrays, one with headers and another
            // with readbuf.  cf. <http://darkain.livejournal.com/492112.html>
            $with_curlwrappers = isset($meta['wrapper_type'])
                              && strtolower($meta['wrapper_type']) == 'curl';
                         
            // get the headers
            if ($with_curlwrappers) {
                $headers = $meta['wrapper_data']['headers'];
            } else {
                $headers = $meta['wrapper_data'];
            }
        }
        
        // done!
        $stack = $this->stack_builder->newInstance($headers, $content);
        return $stack;
    }
    
    /**
     * 
     * Builds the stream context from property options for _fetch().
     * 
     * @param array $headers A sequential array of headers.
     * 
     * @param string $content The body content.
     * 
     * @return resource A stream context resource for "http" and "https"
     * protocols.
     * 
     * @see <http://php.net/manual/en/wrappers.http.php>
     * 
     */
    protected function setContext()
    {
        // http options
        $http = [
            'ignore_errors'    => true,
            'protocol_version' => $this->request->version,
        ];
        
        $this->setOptions($http, [
            'proxy'         => 'proxy',
            'max_redirects' => 'max_redirects',
            'timeout'       => 'timeout',
        ]);
        
        
        // method
        if ($this->request->method != Request::METHOD_GET) {
            $http['method'] = $this->request->method;
        }
        
        // collect headers
        $headers = [];
        foreach ($this->request->headers as $header) {
            $headers[] = $header->__toString();
        }
        
        // collect cookies
        $cookies = $this->request->cookies->__toString();
        if ($cookies) {
            $headers[] = $cookies;
        }
        
        // send headers and cookies?
        if ($headers) {
            $http['header'] = implode("\r\n", $headers);
        }
        
        // only send content if we're POST or PUT
        $content = $this->request->content;
        $method  = $this->request->method;
        $send_content = $method == Request::METHOD_POST
                     || $method == Request::METHOD_PUT;
        
        if ($send_content && ! empty($content)) {
            $http['content'] = $content;
        }
        
        // now set https options based on http
        $https = $http;
        $this->setOptions($https, [
            'ssl_verify_peer' => 'verify_peer',
            'ssl_cafile'      => 'cafile',
            'ssl_capath'      => 'capath',
            'ssl_local_cert'  => 'local_cert',
            'ssl_passphrase'  => 'passphrase',
        ]);
        
        // done!
        $this->context = stream_context_create(array(
            'http'  => $http,
            'https' => $https,
        ));
    }
    
    protected function setOptions(&$arr, $var_key)
    {
        foreach ($var_key as $var => $key) {
            if ($this->options->$var) {
                $arr[$key] = $this->options->$var;
            }
        }
    }
}
