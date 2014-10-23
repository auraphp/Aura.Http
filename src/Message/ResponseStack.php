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
namespace Aura\Http\Message;

use SplStack;

/**
 *
 * A stack of messages, typically the responses from a request.
 *
 * @package Aura.Http
 *
 */
class ResponseStack extends SplStack
{
}
