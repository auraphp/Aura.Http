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
    
    protected $headers = [];
    
    protected $http = [];
    
    protected $https = [];
    
    protected $challenge = [];
    
    protected $response_headers;
    
    protected $response_content;
    
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
        
        // open and read the stream
        $this->openStream();
        if ($this->stream) {
            $this->readStream();
        }
        
        // do we need to authenticate?
        if ($this->mustAuthenticate()) {
            $this->setChallenge();
            $this->openStream();
            if ($this->stream) {
                $this->readStream();
            }
        }
        
        // build a stack
        $stack = $this->stack_builder->newInstance(
            $this->response_headers,
            $this->response_content,
            $this->request->uri
        );
        
        // done!
        return $stack;
    }
    
    protected function openStream()
    {
        $this->response_headers = [];
        $this->response_content = null;
        
        // set the context, including authentication
        $this->setContext();
        
        // connect to the uri (suppress errors and deal with them later)
        $uri = $this->request->uri;
        $level = error_reporting(0);
        $this->stream = fopen($uri, 'r', false, $this->context);
        error_reporting($level);
        
        // did we hit any errors?
        if ($this->stream === false) {
            
            // the $http_response_header variable is automatically created
            // by the streams extension
            if (empty($http_response_header)) {
                // no server response, must be some other error
                $info = error_get_last();
                throw new Exception\ConnectionFailed($info);
            }
            
            // server responded, but there's no content
            $this->response_headers = $http_response_header;
        }
    }
    
    protected function readStream()
    {
        // get the response content
        $this->response_content = stream_get_contents($this->stream);
        $meta = stream_get_meta_data($this->stream);
        fclose($this->stream);
        
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
            $this->response_headers = $meta['wrapper_data']['headers'];
        } else {
            $this->response_headers = $meta['wrapper_data'];
        }
    }
    
    protected function setContext()
    {
        $this->setHeaders();
        $this->setHttp();
        $this->setHttps();
        $this->context = stream_context_create([
            'http'  => $this->http,
            'https' => $this->https,
        ]);
    }
    
    protected function setHeaders()
    {
        // reset headers
        $this->headers = [];
        
        // headers
        foreach ($this->request->headers as $header) {
            $this->headers[] = $header->__toString();
        }
        $this->headers[] = 'Connection: close';
        
        // cookies
        $cookies = $this->request->cookies->__toString();
        if ($cookies) {
            $this->headers[] = $cookies;
        }
        
        // authentication
        $auth = $this->request->auth;
        if ($auth == Request::AUTH_BASIC) {
            // basic auth
            $credentials = base64_encode($this->request->getCredentials());
            $this->headers[] = "Authorization: Basic $credentials";
        } elseif ($auth == Request::AUTH_DIGEST && $this->challenge) {
            // digest auth, but only if a challenge was passed
            $credentials = $this->getDigestCredentials();
            $this->headers[] = "Authorization: $credentials";
        }
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
    protected function setHttp()
    {
        $this->http = [
            'ignore_errors'    => true,
            'protocol_version' => $this->request->version,
            'method'           => $this->request->method,
        ];
        
        $this->setOptions($this->http, [
            'proxy'         => 'proxy',
            'max_redirects' => 'max_redirects',
            'timeout'       => 'timeout',
        ]);
        
        
        // method
        if ($this->request->method != Request::METHOD_GET) {
            $this->http['method'] = $this->request->method;
        }
        
        // send headers and cookies?
        if ($this->headers) {
            $this->http['header'] = implode("\r\n", $this->headers);
        }
        
        // only send content if we're POST or PUT
        $content = $this->request->content;
        $method  = $this->request->method;
        $send_content = $method == Request::METHOD_POST
                     || $method == Request::METHOD_PUT;
        
        if ($send_content && ! empty($content)) {
            $this->http['content'] = $content;
        }
    }
    
    protected function setHttps()
    {
        $this->https = $this->http;
        $this->setOptions($this->https, [
            'ssl_verify_peer' => 'verify_peer',
            'ssl_cafile'      => 'cafile',
            'ssl_capath'      => 'capath',
            'ssl_local_cert'  => 'local_cert',
            'ssl_passphrase'  => 'passphrase',
        ]);
    }
    
    protected function setOptions(&$arr, $var_key)
    {
        foreach ($var_key as $var => $key) {
            if ($this->options->$var) {
                $arr[$key] = $this->options->$var;
            }
        }
    }
    
    protected function mustAuthenticate()
    {
        preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $this->response_headers[0], $matches);
        return $matches[2] == 401;
    }
    
    /**
     *
     * Check the response for a HTTP digest challenge.
     * 
     * To return true the response must contain the HTTP status code 401
     * and the WWW-Authenticate header.
     *
     * @return array
     *
     */
    protected function setChallenge()
    {
        $auth = false;
        
        // Look for a `WWW-Authenticate` header.
        foreach ($this->response_headers as $header) {
            if (false !== strpos($header, 'WWW-Authenticate')) {
                // Get the auth value and remove the double quotes
                $auth = str_replace('"', '', trim(substr($header,18)));
                break;
            }
        }

        // The Authenticate header was not found.
        if (! $auth) {
            return false;
        }

        // Remove Digest from the start of the header.
        $auth = substr($auth, 7);
        
        // Break up the header into key => value pairs.
        $parts = explode(',', $auth);
        $this->challenge  = [
            'realm'  => null,
            'domain' => null,
            'nonce'  => null,
            'opaque' => null
        ];

        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part, 2);
            $this->challenge[trim($key)] = trim($value);
        }
    }
    
    protected function getDigestCredentials()
    {
        $user    = $this->request->username;
        $pass    = $this->request->password;
        $path    = parse_url($this->request->uri, PHP_URL_PATH);
        if (! $path) {
            $path = '/';
        }
        $options = stream_context_get_options($this->context);
        $method  = $options['http']['method'];
        $a1      = sprintf('%s:%s:%s', $user, $this->challenge['realm'], $pass);
        
        $qop = false;
        if (! empty($this->challenge['qop'])) {
            $qop_challenge = explode(',', $this->challenge['qop']);
            foreach ($qop_challenge as $value) {
                if ($value == 'auth-int') {
                    $qop = $value;
                    break;
                } elseif ($value == 'auth') {
                    $qop = $value;
                }
            }
        }
        
        if ('auth-int' == $qop) {
            throw new Exception('`auth-int` is not implemented');
        } else {
            $a2 = sprintf('%s:%s', $method, $path);
        }

        $ha1    = md5($a1);
        $ha2    = md5($a2);
        $cnonce = md5(rand());
        
        if ($qop && in_array($qop, ['auth', 'auth-int'])) {
            $concat = sprintf(
                '%s:%s:%08d:%s:%s:%s', 
                $ha1,
                $this->challenge['nonce'],
                1,
                $cnonce,
                $qop,
                $ha2
            );
        } else {
            $concat = sprintf(
                '%s:%s:%s',
                $ha1,
                $this->challenge['nonce'],
                $ha2
            );
        }

        $template = 'Digest username="%s", '
                  . 'realm="%s", '
                  . 'nonce="%s", '
                  . 'uri="%s", '
                  . 'qop=%s, '
                  . 'nc=00000001, '
                  . 'cnonce="%s", '
                  . 'response="%s"';

        if ($this->challenge['opaque']) {
            $template .= ', opaque="%s"';
        }

        return sprintf(
            $template, 
            $user, 
            $this->challenge['realm'],
            $this->challenge['nonce'],
            $path, 
            $qop, 
            $cnonce, 
            md5($concat), 
            $this->challenge['opaque']
        );
    }
}
