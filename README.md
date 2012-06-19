Aura HTTP
=========

The Aura HTTP package provides objects to build and transport HTTP requests
and responses.


Getting Started With Response
=============================

Instantiation
-------------

The easiest way to get started is to use the `scripts/instance.php` script to
get an HTTP `Manager` object, and use that to create a `Response` object.

```php
<?php
$http = include '/path/to/Aura.Http/scripts/instance.php';
$response = $http->newResponse();
```


Setting Content
---------------

To set the content of the `Response`, use the `setContent()` method.

```php
<?php
$html = '<html>'
      . '<head><title>Test</title></head>'
      . '<body>Hello World!</body>'
      . '</html>';
$response->setContent($html);
```

Setting Headers
---------------

To set headers, access the `$headers` property.

```php
<?php
$response->headers->set('Header-Label', 'header value');
```

You can also set all the headers at once by passing an array of key-value
pairs where the key is the header label and the value is one or more header
values.

```php
<?php
$response->headers->setAll([
    'Header-One' => 'header one value',
    'Header-Two' => [
        'header two value A',
        'header two value B',
        'header two value C',
    ],
]);
```

Note that header labels are sanitized and normalized, so if you enter a label
`header_foo` it will be retained as `Header-Foo`.


Setting Cookies
---------------

To set cookies, access the `$cookies` property . Pass the cookie name and an
array of information about the cookie (including its value).

```php
<?php
$response->cookies->set('cookie_name', [
    'value'    => 'cookie value', // cookie value
    'expire'   => time() + 3600,  // expiration time in unix epoch seconds
    'path'     => '/path',        // server path for the cookie
    'domain'   => 'example.com',  // domain for the cookie
    'secure'   => false,          // send by ssl only?
    'httponly' => true,           // send by http/https only?
]);
```

The information array mimics the [setcookies()](http://php.net/setcookies)
parameter names. You only need to provide the parts of the array that you
need; the remainder will be filled in with `null` defaults for you.

You can also set all the cookies at once by passing an array of key-value
pairs, where the key is the cookie name and the value is a cookie information
array.

```php
<?php
$response->cookies->setAll([
    'cookie_foo' => [
        'value' => 'value for cookie foo',
    ],
    'cookie_bar' => [
        'value' => 'value for cookie bar',
    ],
]);
```

Setting the Status
------------------

To set the HTTP response status, use `setStatusCode()` and `setStatusText()`.
The `setStatusCode()` method automatically sets the text for known codes.

```php
<?php
// automatically sets the status text to 'Not Modified'
$response->setStatusCode(304);

// change the status text to something else
$response->setStatusText('Same As It Ever Was');
```

By default, a new `Response` starts with a status of `'200 OK'`.


Sending the Response
--------------------

Once you have set the content, headers, cookies, and status, you can send the
response to the client using the HTTP `Manager` object.

```php
<?php
$http->send($response);
```

This will send all the headers using [header()](http://php.net/header), all
the cookies using [setcookie()](http://php.net/setcookie), and then `echo` the
content.

Note that you can only send the `Response` once. If you try to send it again,
or if you try to send another response of any sort with headers on it, you
will get an `Exception\HeadersSent` exception.



Getting Started With Request
============================
 
Instantiation
-------------

It takes two steps to build and send a request. First, you create a `Request`
object and manipulate it, then you send it via the `Manager` (which itself
uses a `Transport` and `Adapter` under-the-hood).


```php
<?php

$request = $http->newRequest();
$request->setUrl('http://example.com');
$stack = $http->send($request);
```

You can then read the stack of request responses.


Making a Request
----------------

Making a GET request to Github to list Aura's repositories in JSON format:

```php
<?php
// build the request
$request->setUrl('http://github.com/api/v2/json/repos/show/auraphp');

// send the request and get back a stack of responses
$stack = $http->send($request);

// decode the most-recent response
$repos = json_decode($stack[0]->getContent());
```

The `$stack` is a `Aura\Http\Request\ResponseStack` containing all the
responses including redirects, the stack order is last in first out. Each item
in the stack is a `\Aura\Http\Request\Response` object.


Submitting a Request
--------------------

```php
<?php    
$stack = $request->setContent(['name' => 'value', 'foo' => ['bar']])
                 ->setUrl('http://example.com/submit.php')
                 ->setMethod('post');

$stack = $http->send($request);
```
 
Downloading a File
------------------    

```php
<?php
$request->setUrl('http://example.com/download.ext');
$stack = $http->send($request);
```

In the example above the download is stored in memory. For larger files you
will probably want to save the download to disk as it is received. This is
done using the `saveTo()` method and a full path to a file or directory that
is writeable by PHP as an argument.

```php
<?php
$request->saveTo('/a/path')
        ->setUrl('http://example.com/download.ext');
                    
// send the request and get back a stack of responses
$stack = $http->send($request);
```

When you save a file to disk `$stack[0]->getContent()` will return a file
resource.

Uploading a File
----------------

```php
<?php
$content = [
    'name' => 'value',
    'file' => [
        '@/a/path/file.ext',
        '@/a/path/file2.ext',
    ],
];
            
$response = $request->setContent($content)
                    ->setUrl('http://example.com/submit.php');

$stack = $http->send($request);
```

Submitting Custom Content
-------------------------

```php
<?php
$json     = json_encode(['hello' => 'world']);

$response = $request->setContent($json)
                    ->setHeader('Content-Type', 'application/json')
                    ->setUrl('http://example.com/submit.php')
                    ->setMethod('post');

$stack = $http->send($request);
```

HTTP Authorization
------------------

HTTP Basic:

```php
<?php
$request->setAuth(Request::AUTH_BASIC)
        ->setUsername('username')
        ->setPassword('password')
        ->setUrl('http://example.com/private/index.php');

$stack = $http->send($request);
```

HTTP Digest:

```php
<?php
$request->setAuth(Request::AUTH_DIGEST)
        ->setUsername('username')
        ->setPassword('password')
        ->setUrl('http://example.com/private/index.php');

$stack = $http->send($request);
```

Cookies and Cookie Authorization
--------------------------------

> Note: Currently the `CookieJar` file that `Curl` creates is incompatible
> with the `Streams` `CookieJar` file and vice versa.

Logging into a site using cookies (although if the site has CSRF protection
in place this won't work):

```php
<?php
$request->setCookieJar('/path/to/cookiejar')
        ->setContent(['usr_name' => 'name', 'usr_pass' => 'pass'])
        ->setUrl('http://www.example.com/login')
        ->setMethod('post');

$stack = $http->send($request);

$request->setCookieJar('/path/to/cookiejar')
        ->setUrl('http://www.example.com/');
```


Transport
=========

Available Adapters
------------------

Curl
:    `Aura\Http\Request\Adapter\Curl`

Stream
:    `Aura\Http\Request\Adapter\Stream`   

     Note: Stream is not suitable for uploading large files. When uploading
     files the entire file(s) is loaded into memory, this is due to a
     limitation in PHP HTTP Streams.

