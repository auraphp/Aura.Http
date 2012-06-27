Aura HTTP
=========

The Aura HTTP package provides objects to build and send HTTP requests and
responses.


Getting Started With Response
=============================

Instantiation
-------------

The easiest way to get started is to use the `scripts/instance.php` script to
get a new `Response` object.

```php
<?php
$response = include '/path/to/Aura.Http/scripts/instance.php';
```

Alternatively, you can add `'/path/to/Aura.Http/src'` to your autoloader and
build a `Response` object manually:

```php
<?php
use Aura\Http\Response;
use Aura\Http\Headers;
use Aura\Http\Cookies;
use Aura\Http\Factory\Cookie as CookieFactory;
use Aura\Http\Factory\Header as HeaderFactory;

$headers  = new Headers(new HeaderFactory);
$cookies  = new Cookies(new CookieFactory);
$response = new Response($headers, $cookies);
```

Setting Content
---------------

To set the content of the `Response`, use the `setContent()` method.

```php
<?php
$html = '<html>'
      . '<head><title>Test</title></head>'
      . '<body>Hello World!</body>'
      . </html>';
$response->setContent($html);
```

Setting Headers
---------------

To set headers, access the `$headers` property (which itself is a `Headers`
collection object).

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

To set cookies, access the `$cookies` property (which itself is a `Cookies`
collection object). Pass the cookie name, and an array of information about
the cookie (including its value).

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
response to the client using the `send()` method.

```php
<?php
$response->send();
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

The easiest way to manually create a `Request` instance is to use the
`RequestFactory`. `RequestFactory` will setup the dependency's and choose an
adapter. Defaults to using the Curl adapter if the Curl extension is
installed.

```php
<?php
use Aura\Http\Factory\Request as RequestFactory;

$request_factory = new RequestFactory;
$request         = $request_factory->newInstance();
```

Available Adapters
------------------

Curl
:    `Aura\Http\Request\Adapter\Curl`

Stream
:    `Aura\Http\Request\Adapter\Stream`

     Note: Stream is not suitable for uploading large files. When uploading
     files the entire file(s) is loaded into memory, this is due to a
     limitation in PHP HTTP Streams.

Making a Request
----------------

Making a GET request to Github to list Auras repositories in JSON format:

```php
<?php
$response = $request->get('http://github.com/api/v2/json/repos/show/auraphp');
```

The `$response` is a `Aura\Http\Request\ResponseStack` containing all the
responses including redirects, the stack order is last in first out. Each item
in the stack is a `Aura\Http\Request\Response` object.

Listing the repositories as an array:

```php
<?php
$repos = json_decode($response[0]->getContent());
```

Submitting a Request
--------------------

```php
<?php
$response = $request->setContent(['name' => 'value', 'foo' => ['bar']])
                    ->post('http://localhost/submit.php');
```

Downloading a File
------------------

```php
<?php
$response = $request->get('http://localhost/download.ext');
```

In the example above the download is stored in memory. For larger files you
will probably want to save the download to disk as it is received. This is
done using the `saveTo()` method and a full path to a file or directory that
is writeable by PHP as an argument.

```php
<?php
$response = $request->saveTo('/a/path')
                    ->get('http://localhost/download.ext');
```

When you save a file to disk `$response[0]->getContent()` will return a file
resource.

Uploading a File
----------------

```php
<?php
$response = $request->setContent(['name' => 'value', 'file' => ['@/a/path/file.ext', '@/a/path/file2.ext']])
                    ->post('http://localhost/submit.php');
```

Submitting Custom Content
-------------------------

```php
<?php
$json     = json_encode(['hello' => 'world']);
$response = $request->setContent($json)
                    ->setHeader('Content-Type', 'application/json')
                    ->post('http://localhost/submit.php');
```

HTTP Authorization
------------------

HTTP Basic:

```php
    <?php
    $response = $request->setHttpAuth('usr', 'pass') // defaults to Request::BASIC
                        ->get('http://localhost/private/index.php');
```

HTTP Digest:

```php
<?php
$response = $request->setHttpAuth('usr', 'pass', Request::DIGEST)
                    ->get('http://localhost/private/index.php');
```

Cookies and Cookie Authorization
--------------------------------

Note: Currently the `CookieJar` file that `Curl` creates is incompatible with
the `Streams` `CookieJar` file and vis versa.

Logging into a site using cookies (although if the site has CSRF protection
in place this won't work):

```php
<?php
$request->setCookieJar('/path/to/cookiejar')
        ->setContent(['usr_name' => 'name', 'usr_pass' => 'pass'])
        ->post('http://www.example.com/login');


$response = $request->setCookieJar('/path/to/cookiejar')
                    ->get('http://www.example.com/');
```
