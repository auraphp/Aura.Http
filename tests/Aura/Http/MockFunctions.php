<?php

namespace Aura\Http;

function function_exists($func)
{
    $exists = isset($GLOBALS['function_exists']) ? $GLOBALS['function_exists'] : true;
    return (boolean) $exists;
}

function is_writeable($path)
{
    $writable = isset($GLOBALS['is_writeable']) ? $GLOBALS['is_writeable'] : true;
    return (boolean) $writable;
}