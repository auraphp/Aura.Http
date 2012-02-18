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
use Aura\Http\Request\Multipart;

/**
 * 
 * Stream adapter for the Aura Request library.
 * 
 * @package Aura.Http
 * 
 */
class Stream implements AdapterInterface
{
    
    /**
     * 
     * @var Aura\Http\Request\ResponseBuilder
     * 
     */
    protected $builder;
    
    /**
     * 
     * @var Aura\Http\Request\Multipart
     * 
     */
    protected $multipart;

    /**
     * 
     * @var string
     * 
     */
    protected $scheme;

    /**
     * 
     * @var string
     * 
     */
    protected $url;

    protected $is_new_session = true;

    
    /**
     * 
     * @param \Aura\Http\Request\ResponseBuilder $builder
     * 
     * 
     */
    public function __construct(
        ResponseBuilder $builder, 
        Multipart $multipart)
    {
        if (! ini_get('allow_url_fopen')) {
            $msg = 'The PHP ini setting `allow_url_fopen` is off';
            throw new Http\Exception($msg);
        }

        $this->builder   = $builder;
        $this->multipart = $multipart;
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
        $is_secure    = strtolower(substr($request->url, 0, 5)) == 'https' ||
                        strtolower(substr($request->url, 0, 3)) == 'ssl';
        $this->scheme = $is_secure ? 'https' : 'http';
        $this->url    = $request->url;
        $has_files    = false;

        $this->builder->setRequestUrl($request->url);
        $this->prepareContext($request);

        // Set the content type
        if (! empty($request->headers->{'Content-Type'})) {
            $has_files = false !== strpos($request->headers->{'Content-Type'}, 
                                          'multipart/form-data');

            if ($has_files) {
                $value = 'multipart/form-data; boundary="'. 
                         $this->multipart->getBoundary() . '"';
            } else {
                $value = $request->headers->{'Content-Type'};
            }

            $request->headers->set('Content-Type', $value);
        }

        $this->setContent($request->content, $request->method, $has_files);

        // Close the connection otherwise feof will never return false.
        $request->headers->set('connection', 'close');
        $this->setHeaders($request->headers);

        $stream = $this->connect($request->content);
        
        // Connect did not return a resource 
        if (! is_resource($stream)) {

            // Connect returned an array; save the headers and return the 
            // stack there is no content to be sent or received.
            $callback = [$this->builder, 'saveHeaderCallback'];
            array_map($callback, [null], $stream);
            
            $digest_auth = ! empty($request->options->http_auth) &&
                           Request::DIGEST == $request->options->http_auth[0];

            if ($digest_auth) {
                $challenge = $this->getHttpDigestChallenge($stream);
            }

            // Do not continue if the Authorization header is set as this may
            // result in an indefinite loop.
            if ($digest_auth && $challenge && 
                ! isset($request->headers->Authorization)) {

                $auth = $this->getHttpDigestHeaderValue(
                            $request->options->http_auth, $challenge);

                $request->headers->set('Authorization', $auth);

                return $this->exec($request);

            }

            return $this->builder->getStack();
        }

        // Save the headers from the response
        $folder = empty($request->options->save_to_folder) 
                    ? false : $request->options->save_to_folder;
        $meta   = $this->getResponseHeaders($stream);
        
        array_map([$this->builder, 'saveHeaderCallback'], [null], $meta);

        // Save the content
        while (! feof($stream)) {
            $this->builder->saveContentCallback(
                null, fread($stream, 8192), $folder);
        }

        fclose($stream);
        
        $stack = $this->builder->getStack();

        if ($stack->isEmpty()) {
            throw new Http\Exception\EmptyResponse(
                sprintf('The server did not return a response. : (%s) %s', 
                    'err num', 
                    'err msg')); // todo + time out?
        }

        // Save the response cookies
        if ($request->options->cookiejar) {
            $cookiejar = [];

            foreach ($stack as $response) {
                $cookiejar += $response->getCookies()->getAll();
            }

            // Add the existing cookies
            if (file_exists($request->options->cookiejar)) {
                $cookiejar += unserialize(
                               file_get_contents($request->options->cookiejar));
            }

            $cookiejar = serialize($cookiejar);
            file_put_contents($request->options->cookiejar, $cookiejar);
        }
        
        return $stack;
    }

    /**
     *
     * Prepare and create the stream context.
     * 
     * @param Aura\Http\Request
     *
     */
    protected function prepareContext(Request $request)
    {
        // http options
        $http = [];

        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // max_redirects is set).
        $http['follow_location']  = true;
        $http['method']           = $request->method;
        $http['protocol_version'] = $request->version;

        // HTTP Basic Authorization
        if (!empty($request->options->http_auth)) {
            list($type, $usrpass) = $request->options->http_auth;
                    
            if (Request::BASIC == $type) {
                $value = 'Basic ' . base64_encode("$usrpass");
                $this->request->headers->set('Authorization',  $value);
            } 
        }

        // Load the contents of the cookie jar
        if ($request->options->cookiejar && 
            file_exists($request->options->cookiejar)) {

            $cookies = file_get_contents($request->options->cookiejar);
            $cookies = unserialize($cookies);
            $url     = parse_url($request->url);
            $path    = isset($url['path']) ? $url['path'] : '/';
            $list    = [];

            foreach ($cookies as $cookie) {
                if ($cookie->isMatch($url['scheme'], $url['host'], $path) &&
                    ! $cookie->isExpired($this->is_new_session)) {

                    $this->is_new_session = false;
                    $list[] = "{$cookie->getName()}={$cookie->getValue()}";
                }
            }
            if ($list) {
                // Add the cookies set through Request
                if (isset($request->headers->Cookie)) {
                    $list[] = $request->headers->Cookie;
                }

                $request->headers->set('Cookie', implode('; ', $list));
            }
        }
        
        if (isset($request->options->timeout)) {
            $http['timeout'] = $request->options->timeout;
        }

        if ($request->options->max_redirects) {
            $http['max_redirects'] = $request->options->max_redirects;
        }
        
        if (isset($request->proxy->url)) {
            $proxy_url  = $request->proxy->url;
            $proxy_url .= empty($request->proxy->port) ? 
                                '' : ':' . $request->proxy->port;

            $http['request_fulluri'] = true;
            $http['proxy']           = $proxy_url;

            if (! empty($request->proxy->usrpass)) { // todo digest auth
                $this->headers[] = 'Proxy-Authorization: Basic ' . 
                                        base64_encode($request->proxy->usrpass);
            }
        }

        /**
         * HTTPS options
         */
        if ('https' == $this->scheme) {
            // property-name => context-key
            $var_key = array(
                'ssl_verify_peer'       => 'verify_peer',
                'ssl_cafile'            => 'cafile',
                'ssl_capath'            => 'capath',
                'ssl_local_cert'        => 'local_cert',
                'ssl_passphrase'        => 'passphrase',
            );
            
            // set ssl options
            foreach ($var_key as $var => $key) {
                if (isset($request->ssl->$var)) {
                    $http[$key] = $request->ssl->$var;
                }
            }
        }

        $this->context = stream_context_create([$this->scheme => $http]);
    }

    /**
     * 
     * Initialize the connection.
     * 
     * @return array|resource
     * 
     * @throws Exception\ConnectionFailed
     * 
     */
    protected function connect()
    {
        // connect to the uri (suppress errors and deal with them later)
        $stream = @fopen($this->url, 'rb', false, $this->context);
        
        // did we hit any errors?
        if ($stream === false) {
            // the $http_response_header variable is automatically created
            // by the streams extension
            if (! empty($http_response_header)) {
                // server responded, but there's no content
                return $http_response_header;
            } else {
                // no server response, must be some other error
                $info = error_get_last();

                throw new Exception\ConnectionFailed(
                    sprintf('Connection failed: (%s) %s', 
                        $info['type'], $info['message']));
            }
        }

        return $stream;
    }

    /**
     *
     * Retrieves the headers.
     * 
     * @param resource $stream
     *
     * @return array
     *
     */
    protected function getResponseHeaders($stream)
    {
        $meta = stream_get_meta_data($stream);
        
        // if php was compiled with --with-curlwrappers, then the field
        // 'wrapper_data' contains two arrays, one with headers and another
        // with readbuf.  cf. <http://darkain.livejournal.com/492112.html>
        $with_curlwrappers = isset($meta['wrapper_type'])
                          && strtolower($meta['wrapper_type']) == 'curl';
                         
        // return headers and content.
        if ($with_curlwrappers) {
            // compiled --with-curlwrappers
            return $meta['wrapper_data']['headers'];
        } else {
            // the "normal" case
            return $meta['wrapper_data'];
        }
    }

    /**
     *
     * Set the headers
     *
     * @param Aura\Http\Request\Headers $headers
     *
     */
    protected function setHeaders(Headers $headers)
    {
        $return = '';

        // Headers from Request
        foreach ($headers as $set) {
            foreach ($set as $header) {
                $return .= $header->toString() . "\r\n";
            }
        }

        stream_context_set_option($this->context, $this->scheme, 
                                  'header', $return);

    }

    /**
     *
     * Set the content for the request. Content is only set on POST and PUT
     * requests.
     *
     * @param array|string $content
     * 
     * @param string $method 
     * 
     * @param boolean $has_files
     *
     */
    protected function setContent($content, $method, $has_files)
    {
        // only send content if we're POST or PUT
        if (! $method == Request::POST && 
            ! $method == Request::PUT) {
            return;
        }

        if (is_array($content) && $has_files) {
            $multipart = $this->multipart;
            $multipart->add($content);
            $content   = $multipart->toString();
            // reset multipart in case of multiple requests
            $multipart->reset();

        } else if (is_array($content)) {
            // content does not contain any files
            $content = http_build_query($content);
        } else {
            settype($content, 'string');
        }
        
        stream_context_set_option($this->context, $this->scheme, 
                                  'content', $content);
    }

    /**
     *
     * Check the response for a HTTP digest challenge.
     * 
     * To return true the response must contain the HTTP status code 401
     * and the WWW-Authenticate header.
     *
     * @param array $response_headers
     *
     * @return array
     *
     */
    protected function getHttpDigestChallenge(array $response_headers)
    {
        preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $response_headers[0], $matches);
        
        // The response did not return a 401 status code 
        if (401 != $matches[2]) {
            return false;
        }

        $auth = false;
        
        // Look for a `WWW-Authenticate` header.
        foreach ($response_headers as $header) {
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
        $auth  = [
            'realm'  => null,
            'domain' => null,
            'nonce'  => null,
            'opaque' => null
        ];

        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part, 2);
            $auth[trim($key)]  = trim($value);
        }

        return $auth;
    }
    
    /**
     * 
     * Creates the "Digest" authorization header.
     * 
     * @param string $user_pass
     * 
     * @param array $challenge
     * 
     */
    public function getHttpDigestHeaderValue($user_pass, array $challenge)
    {
        $qop = false;

        // Find the quality of protection value; `auth-ini` is preferred.
        if (! empty($challenge['qop'])) {
            $qop_challenage = explode(',', $challenge['qop']);

            foreach ($qop_challenage as $value) {
                if ('auth-int' == $value) {
                    $qop = $value;
                    break;
                } else if ('auth' == $value) {
                    $qop = $value;
                }
            }
        }

        list($user, $pass) = explode(':', $user_pass[1], 2);
        $digest_uri        = parse_url($this->url, PHP_URL_PATH);

        $a1     = sprintf('%s:%s:%s', $user, $challenge['realm'], $pass);
        $method = stream_context_get_options($this->context)['http']['method'];

        if ('auth-int' == $qop) {
            throw new Exception('`auth-int` is not implemented');
            //$a2 = sprintf('%s:%s:%s', $method, $digest_uri, md5(entitybody)); // todo 
        } else {
            $a2 = sprintf('%s:%s', $method, $digest_uri);
        }

        $ha1    = md5($a1);
        $ha2    = md5($a2);
        $cnonce = md5(rand());

        if ($qop && in_array($qop, ['auth', 'auth-int'])) {
            $concat = sprintf('%s:%s:%08d:%s:%s:%s', 
                            $ha1, $challenge['nonce'], 1, $cnonce, $qop, $ha2);
        } else {
            $concat = sprintf('%s:%s:%s', $ha1, $challenge['nonce'], $ha2);
        }

        $header = 'Digest username="%s", '.
                  'realm="%s", ' .
                  'nonce="%s", ' . 
                  'uri="%s", ' .
                  'qop=%s, ' .
                  'nc=00000001, ' .
                  'cnonce="%s", ' .
                  'response="%s"';

        if ($challenge['opaque']) {
            $header .= ', opaque="%s"';
        }

        return sprintf($header, 
                       $user, 
                       $challenge['realm'],
                       $challenge['nonce'],
                       $digest_uri, 
                       $qop, 
                       $cnonce, 
                       md5($concat), 
                       $challenge['opaque']);
    }
}