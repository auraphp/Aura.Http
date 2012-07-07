<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Http
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Http\Adapter;

use Aura\Http\Cookie\JarFactory;
use Aura\Http\Exception;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response\StackBuilder;
use Aura\Http\Multipart\FormData;
use Aura\Http\Transport\Options;

/**
 * 
 * cURL adapter
 * 
 * @package Aura.Http
 * 
 */
class Stream implements AdapterInterface
{
    /**
     * 
     * @var Aura\Http\Message\Response\StackBuilder
     * 
     */
    protected $stack_builder;
    
    /**
     * 
     * @var Aura\Http\Cookie\JarFactory
     * 
     */
    protected $cookie_jar_factory;

    /**
     * 
     * @var Aura\Http\Cookie\Jar
     * 
     */
    protected $cookie_jar;

    /**
     * 
     * @var Aura\Http\Message\Request
     * 
     */
    protected $request;

    protected $options;

    protected $context;

    protected $context_content = null;

    protected $context_headers = [];

    protected $context_options = [];

    protected $challenge = [];

    protected $headers;

    protected $content;

    /**
     * 
     * @param StackBuilder $stack_builder
     * 
     * @param FormData $form_data
     * 
     * @param JarFactory $cookie_jar_factory
     * 
     */
    public function __construct(
        StackBuilder $stack_builder,
        FormData $form_data,
        JarFactory $cookie_jar_factory
    ) {
        $this->stack_builder      = $stack_builder;
        $this->form_data          = $form_data;
        $this->cookie_jar_factory = $cookie_jar_factory;
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

        // create a cookie jar if needed
        if ($this->options->cookie_jar) {
            $this->cookie_jar = $this->cookie_jar_factory->newInstance(
                $this->options->cookie_jar
            );
        }

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
        $stream = $this->request->getSaveToStream();
        if ($stream) {
            fwrite($stream, $this->content);
            $this->content = $stream;
        }

        // build a stack
        $stack = $this->stack_builder->newInstance(
            $this->headers,
            $this->content,
            $this->request->url
        );

        // done!
        return $stack;
    }

    protected function openStream()
    {
        $this->headers = [];
        $this->content = null;

        // set cookies from the jar. we do this here because we may
        // open two connections, and want to retain them each time.
        if ($this->cookie_jar) {
            $this->request->cookies->setAllFromJar(
                $this->cookie_jar,
                $this->request->url
            );
        }

        // set the context, including authentication
        $this->setContext();

        // connect to the url (suppress errors and deal with them later)
        $url = $this->request->url;
        $level = error_reporting(0);
        $this->stream = fopen($url, 'rb', false, $this->context);
        error_reporting($level);

        // did we hit any errors?
        if ($this->stream === false) {

            // the $http_response_header variable is automatically created
            // by the streams extension
            if (empty($http_response_header)) {
                // no server response, must be some other error
                $info = error_get_last();
                throw new Exception\ConnectionFailed($info['message']);
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
            throw new Exception\ConnectionTimeout($url);
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
        // set content first so we can manipulate headers
        $this->setContextContent();
        $this->setContextHeaders();
        $this->setContextOptions();

        // what scheme are we using?
        $url = parse_url($this->request->url);
        if ($url['scheme'] == 'https') {
            // secure scheme
            $this->setContextOptionsSecure();
        }

        // set context
        $this->context = stream_context_create([
            $url['scheme'] => $this->context_options,
        ]);
    }

    protected function setContextContent()
    {
        // reset content
        $this->context_content = null;

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

        // read from resource
        if (is_resource($content)) {
            while (! feof($content)) {
                $this->context_content .= fread($content, 8192);
            }
            return;
        }

        // convert to multipart/form-data ?
        if (is_array($content)) {
            $boundary = $this->form_data->getBoundary();
            $this->request->headers->set(
                "Content-Type",
                "multipart/form-data; boundary=\"{$boundary}\""
            );
            $this->form_data->addFromArray($content);
            $this->context_content = $this->form_data->__toString();
            return;
        }

        // all other types of content
        $this->context_content = $content;
    }

    protected function setContextHeaders()
    {
        // reset headers
        $this->context_headers = [];

        // headers
        foreach ($this->request->getHeaders() as $header) {
            $this->context_headers[] = $header->__toString();
        }

        // add cookies from the jar to the request
        if ($this->cookie_jar) {
            $this->request->cookies->setAllFromJar(
                $this->cookie_jar,
                $this->request->url
            );
        }

        // cookies
        $cookies = $this->request->getCookies()->__toString();
        if ($cookies) {
            $this->context_headers[] = $cookies;
        }

        // proxy authentication
        $credentials = $this->options->getProxyCredentials();
        if ($credentials) {
            $credentials = base64_encode($credentials);
            $this->headers[] = 'Proxy-Authorization: Basic {$credentials}';
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

        // close the connection or we wait a long time to finish
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
    protected function setContextOptions()
    {
        $this->context_options = [
            'ignore_errors'    => true,
            'protocol_version' => $this->request->version,
            'method'           => $this->request->method,
        ];

        // general options
        $this->setOptions([
            'max_redirects' => 'max_redirects',
            'timeout'       => 'timeout',
        ]);

        // proxy options
        if ($this->options->proxy) {
            $this->context_options['request_fulluri'] = true;
            $this->context_options['proxy'] = $this->options->getProxyHostAndPort();
        }

        // method
        if ($this->request->method != Request::METHOD_GET) {
            $this->context_options['method'] = $this->request->method;
        }

        // headers
        if ($this->context_headers) {
            $this->context_options['header'] = implode("\r\n", $this->context_headers);
        }

        // content
        if ($this->context_content) {
            $this->context_options['content'] = $this->context_content;
        }
    }

    protected function setContextOptionsSecure()
    {
        $this->setOptions([
            'ssl_verify_peer' => 'verify_peer',
            'ssl_cafile'      => 'cafile',
            'ssl_capath'      => 'capath',
            'ssl_local_cert'  => 'local_cert',
            'ssl_passphrase'  => 'passphrase',
        ]);
    }

    protected function setOptions($var_key)
    {
        foreach ($var_key as $var => $key) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->options->$var !== null) {
                $this->context_options[$key] = $this->options->$var;
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
        $path    = parse_url($this->request->url, PHP_URL_PATH);
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
