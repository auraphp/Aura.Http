---
title: Aura for PHP; HTTP Responses
layout: default
---

Aura HTTP
=========

The Aura HTTP package provides objects to build and send HTTP responses from the server to the client.


Getting Started
===============

Instantiation
-------------

The easiest way to get started is to use the `scripts/instance.php` script to get a new `Response` object.

    <?php
    $response = include '/path/to/Aura.Http/scripts/instance.php';

Alternatively, you can add `'/path/to/Aura.Http/src'` to your autoloader and build a `Response` object manually:

    <?php
    use Aura\Http\Response;
    use Aura\Http\Headers;
    use Aura\Http\Cookies;
    $response = new Response(new Headers, new Cookies);


Setting Content
---------------

To set the content of the `Response`, use the `setContent()` method.

    <?php
    $html = '<html>'
          . '<head><title>Test</title></head>'
          . '<body>Hello World!</body>
          . </html>';
    $response->setContent($html);


Setting Headers
---------------

To set headers, access the `$headers` property (which itself is a `Headers` collection object).

    <?php
    $response->headers->set('Header-Label', 'header value');

You can also set all the headers at once by passing an array of key-value pairs where the key is the header label and the value is one or more header values.

    <?php
    $response->headers->setAll([
        'Header-One' => 'header one value',
        'Header-Two' => [
            'header two value A',
            'header two value B',
            'header two value C',
        ],
    ]);

Note that header labels are sanitized and normalized, so if you enter a label `header_foo` it will be retained as `Header-Foo`.


Setting Cookies
---------------

To set cookies, access the `$cookies` property (which itself is a `Cookies` collection object).  Pass the cookie name, and an array of information about the cookie (including its value).

    <?php
    $response->cookies->set('cookie_name', [
        'value'    => 'cookie value', // cookie value
        'expire'   => time() + 3600,  // expiration time in unix epoch seconds
        'path'     => '/path',        // server path for the cookie
        'domain'   => 'example.com',  // domain for the cookie
        'secure'   => false,          // send by ssl only?
        'httponly' => true,           // send by http/https only?
    ]);

The information array mimics the [setcookies()](http://php.net/setcookies) parameter names.  You only need to provide the parts of the array that you need; the remainder will be filled in with `null` defaults for you.

You can also set all the cookies at once by passing an array of key-value pairs, where the key is the cookie name and the value is a cookie information array.

    <?php
    $response->cookies->setAll([
        'cookie_foo' => [
            'value' => 'value for cookie foo',
        ],
        'cookie_bar' => [
            'value' => 'value for cookie bar',
        ],
    ]);

Setting the Status
------------------

To set the HTTP response status, use `setStatusCode()` and `setStatusText()`. The `setStatusCode()` method automatically sets the text for known codes.

    <?php
    // automatically sets the status text to 'Not Modified'
    $response->setStatusCode(304);
    
    // change the status text to something else
    $response->setStatusText('Same As It Ever Was');

By default, a new `Response` starts with a status of `'200 OK'`.


Sending the Response
--------------------

Once you have set the content, headers, cookies, and status, you can send the response to the client using the `send()` method.

    <?php
    $response->send();

This will send all the headers using [header()](http://php.net/header), all the cookies using [setcookie()](http://php.net/setcookie), and then `echo` the content.

Note that you can only send the `Response` once. If you try to send it again, or if you try to send another response of any sort with headers on it, you will get an `Exception\HeadersSent` exception.
