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
namespace Aura\Http;

use Aura\Http\Adapter\CurlAdapter;
use Aura\Http\Adapter\StreamAdapter;
use Aura\Http\Cookie\CookieJarFactory;
use Aura\Http\Exception;
use Aura\Http\Message\MessageFactory;
use Aura\Http\Message\ResponseStackBuilder;
use Aura\Http\Multipart\FormData;
use Aura\Http\Multipart\PartFactory;
use Aura\Http\PhpFunc;
use Aura\Http\Transport\Transport;
use Aura\Http\Transport\TransportOptions as TransportOptions;

/**
 *
 * Factory class to create instance of manager easily
 *
 * @package Aura.Http
 *
 */
class HttpFactory
{
    /**
     *
     * Creates a new manager instance.
     *
     * @param string $type The adapter type to use: `'curl'` or `'stream'`.
     *
     * @return Http
     *
     */
    public function newInstance($type)
    {
        if ($type == 'curl') {
            $adapter = new CurlAdapter(
                new ResponseStackBuilder(new MessageFactory)
            );
        } elseif ($type == 'stream') {
            $adapter = new StreamAdapter(
                new ResponseStackBuilder(new MessageFactory),
                new FormData(new PartFactory),
                new CookieJarFactory
            );
        } else {
            throw new Exception\UnknownAdapterType;
        }

        return new Http(
            new MessageFactory,
            new Transport(
                new PhpFunc,
                new TransportOptions,
                $adapter
            )
        );
    }
}
