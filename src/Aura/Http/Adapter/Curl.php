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

use Aura\Http\Exception;
use Aura\Http\Message\Request;
use Aura\Http\Message\Response\StackBuilder;
use Aura\Http\Transport\Options;

/**
 * 
 * cURL adapter
 * 
 * @package Aura.Http
 * 
 */
class Curl implements AdapterInterface
{
    /**
     * 
     * Builds a stack of response messages.
     * 
     * @var StackBuilder
     * 
     */
    protected $stack_builder;

    /**
     * 
     * The HTTP request to be sent.
     * 
     * @var Request
     * 
     */
    protected $request;

    /**
     * 
     * The transport options.
     * 
     * @var Options
     * 
     */
    protected $options;

    /**
     * 
     * The response headers.
     * 
     * @var array
     * 
     */
    protected $headers;

    /**
     * 
     * The response content.
     * 
     * @var string
     * 
     */
    protected $content;

    /**
     * 
     * The curl handle for request/response communication.
     * 
     * @var resource
     * 
     */
    protected $curl;

    /**
     * 
     * File handle for saving content.
     * 
     * @var resource
     * 
     */
    protected $save;

    /**
     *
     * Constructor.
     * 
     * @param StackBuilder $stack_builder Builds a stack of response messages.
     * 
     */
    public function __construct(StackBuilder $stack_builder)
    {
        $this->stack_builder = $stack_builder;
    }

    /**
     * 
     * Executes the request and assembles the response stack.
     * 
     * @param Request $request The request to send.
     * 
     * @param Options $options The transport options.
     * 
     * @return Aura\Http\Response\Message\Stack
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
            $this->request->url
        );

        // done!
        return $stack;
    }

    /**
     * 
     * Sets the curl handle and its options.
     * 
     * @return void
     * 
     */
    protected function setCurl()
    {
        $this->curl = curl_init($this->request->url);
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

    /**
     * 
     * Makes the curl connection, then retrieves headers and content.
     * 
     * @return void
     * 
     */
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
            throw new Exception\ConnectionFailed($text);
        }

        // close the connection
        curl_close($this->curl);

        // convert headers to an array, removing the trailing blank lines
        $this->headers = explode("\r\n", rtrim($this->headers));

        // did we save the response to a file?
        if ($this->save) {
            // retain the file handle
            $this->content = $this->save;
        } else {
            // the content is the response text
            $this->content = $response;
        }
    }

    /**
     * 
     * Sets basic options on the curl handle.
     * 
     * @return void
     * 
     */
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

        // cookie jar: read from and save to this file
        $cookie_jar = $this->options->cookie_jar;
        if ($cookie_jar) {
            curl_setopt($this->curl, CURLOPT_COOKIEJAR,  $cookie_jar);
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $cookie_jar);
        }

        // property-name => curlopt-constant
        $this->curlSetopt([
            'max_redirects' => CURLOPT_MAXREDIRS,
            'timeout'       => CURLOPT_TIMEOUT,
        ]);
    }

    /**
     * 
     * Helper method to set curl options.
     * 
     * @param array $var_opt An array of key-value pairs where the key is
     * a request variable, and the value is a curl option constant.
     * 
     * @return void
     * 
     */
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

    /**
     * 
     * Sets proxy options on the curl handle.
     * 
     * @return void
     * 
     */
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

        $credentials = $this->options->getProxyCredentials();
        if ($credentials) {
            curl_setopt(
                $this->curl,
                CURLOPT_PROXYUSERPWD,
                $this->options->getProxyCredentials()
            );
        }
    }

    /**
     * 
     * Sets secure/ssl/tls options on the curl handle.
     * 
     * @return void
     * 
     */
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

    /**
     * 
     * Sets the HTTP version on the curl handle.
     * 
     * @return void
     * 
     */
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

    /**
     * 
     * Sets the HTTP method on the curl handle.
     * 
     * @return void
     * 
     */
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
                curl_setopt($this->curl, CURLOPT_NOBODY, true);
                break;
            default:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->request->method);
                break;
        }
    }

    /**
     * 
     * Sets authorization options on the curl handle.
     * 
     * @return void
     * 
     */
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

    /**
     * 
     * Sets headers and cookies on the curl handle.
     * 
     * @return void
     * 
     */
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
            curl_setopt($this->curl, CURLOPT_COOKIE, $cookies);
        }
    }

    /**
     * 
     * Sets content on the curl handle.
     * 
     * @return void
     * 
     */
    protected function curlContent()
    {
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

    /**
     * 
     * Sets a "writefunction" callback on the curl handle to stream response
     * content to a file handle.
     * 
     * @return void
     * 
     */
    protected function curlSave()
    {
        $this->save = $this->request->getSaveToStream();
        if (! $this->save) {
            return;
        }

        // callback for saving response content
        curl_setopt(
            $this->curl,
            CURLOPT_WRITEFUNCTION,
            [$this, 'saveContent']
        );
    }

    /**
     * 
     * A callback to retain headers in the $headers property.
     * 
     * @param resource $curl The curl handle.
     * 
     * @param string $data The header string to be retained.
     * 
     * @return void
     * 
     */
    public function saveHeaders($curl, $data)
    {
        $this->headers .= $data;
        return strlen($data);
    }

    /**
     * 
     * A callback to save content to the $save file handle.
     * 
     * @param resource $curl The curl handle.
     * 
     * @param string $data The content to be saved.
     * 
     * @return void
     * 
     */
    public function saveContent($curl, $data)
    {
        fwrite($this->save, $data);
        return strlen($data);
    }
}
 