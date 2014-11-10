<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @package Aura.Http
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Http\Message;

use Aura\Http\Exception;

/**
 *
 * The Aura Response class.
 *
 * @package Aura.Http
 *
 */
class Response extends AbstractMessage
{
    /**
     * Constants for response status codes and reason phrases.
     */
    const STATUS_100 = 'Continue';
    const STATUS_101 = 'Switching Protocols';
    const STATUS_200 = 'OK';
    const STATUS_201 = 'Created';
    const STATUS_202 = 'Accepted';
    const STATUS_203 = 'Non-Authoritative Information';
    const STATUS_204 = 'No Content';
    const STATUS_205 = 'Reset Content';
    const STATUS_206 = 'Partial Content';
    const STATUS_300 = 'Multiple Choices';
    const STATUS_301 = 'Moved Permanently';
    const STATUS_302 = 'Found';
    const STATUS_303 = 'See Other';
    const STATUS_304 = 'Not Modified';
    const STATUS_305 = 'Use Proxy';
    const STATUS_307 = 'Temporary Redirect';
    const STATUS_400 = 'Bad Request';
    const STATUS_401 = 'Unauthorized';
    const STATUS_402 = 'Payment Required';
    const STATUS_403 = 'Forbidden';
    const STATUS_404 = 'Not Found';
    const STATUS_405 = 'Method Not Allowed';
    const STATUS_406 = 'Not Acceptable';
    const STATUS_407 = 'Proxy Authentication Required';
    const STATUS_408 = 'Request Timeout';
    const STATUS_409 = 'Conflict';
    const STATUS_410 = 'Gone';
    const STATUS_411 = 'Length Required';
    const STATUS_412 = 'Precondition Failed';
    const STATUS_413 = 'Request Entity Too Large';
    const STATUS_414 = 'Request-URI Too Long';
    const STATUS_415 = 'Unsupported Media Type';
    const STATUS_416 = 'Requested Range Not Satisfiable';
    const STATUS_417 = 'Expectation Failed';
    const STATUS_500 = 'Internal Server Error';
    const STATUS_501 = 'Not Implemented';
    const STATUS_502 = 'Bad Gateway';
    const STATUS_503 = 'Service Unavailable';
    const STATUS_504 = 'Gateway Timeout';
    const STATUS_505 = 'HTTP Version Not Supported';

    /**
     *
     * The HTTP status code of the message.
     *
     * @var int
     *
     */
    protected $status_code = 200;

    /**
     *
     * The HTTP status message of the message.
     *
     * @var string
     *
     */
    protected $status_text = 'OK';

    /**
     *
     * Sets the HTTP status code to for the response. Automatically resets the
     * status text to the default for that code, if any.
     *
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     *
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new Exception\UnknownStatus("Status code $code not recognized.");
        }
        $this->status_code = $code;

        $const = "static::STATUS_{$code}";
        if (defined($const)) {
            $this->setStatusText(constant($const));
        } else {
            $this->setStatusText(null);
        }

        return $this;
    }

    /**
     *
     * Returns the HTTP status code for the message.
     *
     * @return int
     *
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     *
     * Sets the HTTP status text for the message.
     *
     * @param string $text The status text.
     *
     * @return void
     *
     */
    public function setStatusText($text)
    {
        $text = trim(str_replace(["\r", "\n"], '', $text));
        $this->status_text = $text;
        return $this;
    }

    /**
     *
     * Returns the HTTP status text for the message.
     *
     * @return string
     *
     */
    public function getStatusText()
    {
        return $this->status_text;
    }
}
