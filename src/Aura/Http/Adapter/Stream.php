<?php
namespace Aura\Http\Adapter;

use Aura\Http\Exception;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response\StackBuilder;
use Aura\Http\Transport\Options;

class Stream implements AdapterInterface
{
    protected $stack_builder;
    
    protected $request;
    
    protected $options;
    
    protected $context;
    
    protected $context_headers = [];
    
    protected $context_http = [];
    
    protected $context_https = [];
    
    protected $challenge = [];
    
    protected $headers;
    
    protected $content;
    
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
        
        // save to file?
        $file = $this->request->getSaveToFile();
        if ($file) {
            file_put_contents($file, $this->content);
            $this->content = fopen($file, 'rb');
        }
        
        // build a stack
        $stack = $this->stack_builder->newInstance(
            $this->headers,
            $this->content,
            $this->request->uri
        );
        
        // done!
        return $stack;
    }
    
    protected function openStream()
    {
        $this->headers = [];
        $this->content = null;
        
        // set the context, including authentication
        $this->setContext();
        
        // connect to the uri (suppress errors and deal with them later)
        $uri = $this->request->uri;
        $level = error_reporting(0);
        $this->stream = fopen($uri, 'rb', false, $this->context);
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
            $this->headers = $http_response_header;
        }
    }
    
    protected function readStream()
    {
        // get the response content
        while (! feof($this->stream)) {
            $this->content .= fread($this->stream, 8192);
        }
        
        // get the metadata
        $meta = stream_get_meta_data($this->stream);
        
        // close the stream
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
            $this->headers = $meta['wrapper_data']['headers'];
        } else {
            $this->headers = $meta['wrapper_data'];
        }
    }
    
    protected function setContext()
    {
        $this->setContextHeaders();
        $this->setContextHttp();
        $this->setContextHttps();
        $this->context = stream_context_create([
            'http'  => $this->context_http,
            'https' => $this->context_https,
        ]);
    }
    
    protected function setContextHeaders()
    {
        // reset headers
        $this->context_headers = [];
        
        // headers
        foreach ($this->request->getHeaders() as $header) {
            $this->context_headers[] = $header->__toString();
        }
        
        // cookies
        $cookies = $this->request->getCookies()->__toString();
        if ($cookies) {
            $this->context_headers[] = $cookies;
        }
        
        // authentication
        $auth = $this->request->auth;
        if ($auth == Request::AUTH_BASIC) {
            // basic auth
            $credentials = base64_encode($this->request->getCredentials());
            $this->context_headers[] = "Authorization: Basic $credentials";
        } elseif ($auth == Request::AUTH_DIGEST && $this->challenge) {
            // digest auth, but only if a challenge was passed
            $credentials = $this->getDigestCredentials();
            $this->context_headers[] = "Authorization: Digest $credentials";
        }
        
        // always close the connection
        $this->context_headers[] = 'Connection: close';
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
    protected function setContextHttp()
    {
        $this->context_http = [
            'ignore_errors'    => true,
            'protocol_version' => $this->request->version,
            'method'           => $this->request->method,
        ];
        
        $this->setContextOptions($this->context_http, [
            'proxy'         => 'proxy',
            'max_redirects' => 'max_redirects',
            'timeout'       => 'timeout',
        ]);
        
        
        // method
        if ($this->request->method != Request::METHOD_GET) {
            $this->context_http['method'] = $this->request->method;
        }
        
        // send headers and cookies?
        if ($this->context_headers) {
            $this->context_http['header'] = implode("\r\n", $this->context_headers);
        }
        
        // get the method
        $method  = $this->request->method;
        
        // get the content.
        // @todo Make this a curl callback so we can stream it out.
        $content = null;
        $this->request->content->rewind();
        while (! $this->request->content->eof()) {
            $content .= $this->request->content->read();
        };
        
        // only send content if we're POST or PUT
        $post_or_put = $method == Request::METHOD_POST
                    || $method == Request::METHOD_PUT;
        
        if ($post_or_put && ! empty($content)) {
            $this->context_http['content'] = $content;
        }
    }
    
    protected function setContextHttps()
    {
        $this->context_https = $this->context_http;
        $this->setContextOptions($this->context_https, [
            'ssl_verify_peer' => 'verify_peer',
            'ssl_cafile'      => 'cafile',
            'ssl_capath'      => 'capath',
            'ssl_local_cert'  => 'local_cert',
            'ssl_passphrase'  => 'passphrase',
        ]);
    }
    
    protected function setContextOptions(&$arr, $var_key)
    {
        foreach ($var_key as $var => $key) {
            if ($this->options->$var) {
                $arr[$key] = $this->options->$var;
            }
        }
    }
    
    protected function mustAuthenticate()
    {
        preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $this->headers[0], $matches);
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
        foreach ($this->headers as $header) {
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

        $template = 'username="%s", '
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
