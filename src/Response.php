<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\http;

/**
 * 
 * Generic HTTP response object for sending headers, cookies, and content.
 * 
 * This is a fluent class; the set() methods can be chained together like so:
 * 
 * {{code: php
 *     $response->setStatusCode(404)
 *              ->setHeader('X-Foo', 'Bar')
 *              ->setCookie('baz', 'dib')
 *              ->setContent('Page not found.')
 *              ->display();
 * }}
 * 
 * @package aura.web
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @todo Add charset param so that headers get sent with right encoding?
 * 
 */
class Response extends AbstractResponse
{
    /**
     * 
     * Whether or not cookies should default being sent by HTTP only.
     * 
     * @var bool
     * 
     */
    protected $cookies_httponly = true;
    
    
    /**
     * 
     * Sends all headers and cookies, then returns the body.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        // __toString cannot throw exceptions
        try {
            $this->sendHeaders();
        } catch (Exception $e) {
            return ''; // todo what to do? trigger_error? let the fatal error happen?
        }
        
        // cast to string to avoid fatal error when returning nulls
        return (string) $this->content;
    }
    
    /**
     * 
     * By default, should cookies be sent by HTTP only?
     * 
     * @param bool $flag True to send by HTTP only, false to send by any
     * method.
     * 
     * @return aura\http\Response This response object.
     * 
     */
    public function setCookiesHttponly($flag)
    {
        $this->cookies_httponly = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Should the response disable HTTP caching?
     * 
     * When true, the response will send these headers:
     * 
     * {{code:
     *     Pragma: no-cache
     *     Cache-Control: no-store, no-cache, must-revalidate
     *     Cache-Control: post-check=0, pre-check=0
     *     Expires: 1
     * }}
     * 
     * @param bool $flag When true, disable browser caching. Default is true.
     * 
     * @see redirectNoCache()
     * 
     * @return void
     * 
     */
    public function setNoCache($flag = true)
    {
        if ($flag) {
            $this->headers['Pragma']        = 'no-cache';
            $this->headers['Cache-Control'] = array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
            );
            $this->headers['Expires']       = '1';
        } else {
            unset($this->headers['Pragma']);
            unset($this->headers['Cache-Control']);
            unset($this->headers['Expires']);
        }
        
        return $this;
    }
    
    /**
     * 
     * Sends all headers and cookies, then prints the response content.
     * 
     * @return void
     * 
     */
    public function display()
    {
        $this->sendHeaders();
        echo $this->content;
    }
    
    /**
     * 
     * Issues an immediate "Location" redirect.  Use instead of display()
     * to perform a redirect.  You should die() or exit() after calling this.
     * 
     * @param string $href The URI to redirect.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '302 Found'.
     * 
     * @param boolean   $cocheable Is this redirect cacheable. If false HTTP 
     * caching is disabled. Defaults to false.
     * 
     * @return void
     * 
     * @throws aura\http\Exception Missing or incomplete URI.
     * 
     */
    public function redirect($href, $code = '302', $cacheable = false)
    {
        if ($cacheable) {
            $this->setNoCache();
        }
        
        // make sure there's actually an href
        $href = trim($href);
        if (! $href || false === strpos($href, '://')) {
            throw new Exception('Missing or incomplete URI cannot redirect.');
        }

        // external link, protect against header injections
        $href = str_replace(array("\r", "\n"), '', $href);
        
        // kill off all output buffers
        while(@ob_end_clean());
        
        
        // set the status code
        $this->setStatusCode($code);
        
        // set the redirect location 
        $this->setHeader('Location', $href);
        
        // clear the response body
        $this->content = null;
        
        // save the session
        session_write_close();
        
        // send the response directly -- done.
        $this->display();
    }
    
    /**
     * 
     * Sends all headers and cookies.
     * 
     * @return void
     * 
     * @throws aura\http\Exception_HeadersSent if headers have
     * already been sent.
     * 
     */
    protected function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new Exception_HeadersSent($file, $line);
        }
        
        // build the full status header string. The values have already been
        // sanitized by setStatus() and setStatusText().
        $status = "HTTP/{$this->version} {$this->status_code}";
        if ($this->status_text) {
            $status .= " {$this->status_text}";
        }
        
        // send the status header
        header($status, true, $this->status_code);
        
        // send each of the remaining headers
        foreach ($this->headers as $key => $list) {
            
            // skip empty keys, keys have been sanitized by setHeader.
            if (! $key) {
                continue;
            }
            
            // send each value for the header
            foreach ((array) $list as $val) {
                // we don't need full MIME escaping here, just sanitize the
                // value by stripping CR and LF chars
                $val = str_replace(array("\r", "\n"), '', $val);
                header("$key: $val");
            }
        }
        
        // send each of the cookies
        foreach ($this->cookies as $key => $val) {
            
            // was httponly set for this cookie?  if not, use the default.
            $httponly = ($val['httponly'] === null)
                ? $this->cookies_httponly
                : (bool) $val['httponly'];
            
            // try to allow for times not in unix-timestamp format
            if (! is_numeric($val['expires'])) {
                $val['expires'] = strtotime($val['expires']);
            }
            
            // actually set the cookie
            setcookie(
                $key,
                $val['value'],
                (int) $val['expires'],
                $val['path'],
                $val['domain'],
                (bool) $val['secure'],
                (bool) $httponly
            );
        }
    }
}