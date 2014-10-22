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
namespace Aura\Http\Multipart;

use Aura\Http\Header\HeaderCollection;

/**
 *
 * Represent one part of a multipart message.
 *
 * @package Aura.Http
 *
 */
class Part
{
    /**
     *
     * Headers for this part.
     *
     * @var HeaderCollection
     *
     */
    protected $headers;

    /**
     *
     * Content for this part.
     *
     * @var string
     *
     */
    protected $content;

    /**
     *
     * Constructor.
     *
     * @param HeaderCollection $headers Headers for this part.
     *
     */
    public function __construct(HeaderCollection $headers)
    {
        $this->headers = $headers;
    }

    /**
     *
     * Sets the content for this part.
     *
     * @param string $content
     *
     * @return void
     *
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     *
     * Gets the content for this part.
     *
     * @return string
     *
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     *
     * Gets the headers for this part.
     *
     * @return HeaderCollection
     *
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * Sets the Content-Type header for this part.
     *
     * @param string $type The content type.
     *
     * @param string $charset The charset to use.
     *
     * @return void
     *
     */
    public function setType($type, $charset = null)
    {
        if ($charset) {
            $type .= "; charset={$charset}";
        }
        $this->headers->set('Content-Type', $type);
    }

    /**
     *
     * Sets the Disposition header for this part.
     *
     * @param string $disposition The disposition; e.g., `'form-data'`.
     *
     * @param string $name The field name to use for this part, if any.
     *
     * @param string $filename The filename to use for this part, if any.
     *
     * @return void
     *
     */
    public function setDisposition(
        $disposition,
        $name = null,
        $filename = null
    ) {
        if ($name) {
            $disposition .= "; name=\"{$name}\"";
        }
        if ($filename) {
            $disposition .= "; filename=\"{$filename}\"";
        }
        $this->headers->set('Content-Disposition', $disposition);
    }

    /**
     *
     * Sets the Content-Encoding header for this part.
     *
     * > HTTP, unlike MIME, does not use Content-Transfer-Encoding, and
     * > does use Transfer-Encoding and Content-Encoding.
     * -- <http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.15>,
     *    the "Note" at the end.
     *
     * @param string $encoding The encoding used.
     *
     * @return void
     *
     */
    public function setEncoding($encoding)
    {
        $this->headers->set('Content-Encoding', $encoding);
    }
}
