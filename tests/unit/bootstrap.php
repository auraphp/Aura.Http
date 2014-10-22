<?php
// turn on all errors
error_reporting(E_ALL);

// autoloader
require dirname(dirname(__DIR__)) . '/autoload.php';

// default globals
if (is_readable(__DIR__ . '/globals.dist.php')) {
    require __DIR__ . '/globals.dist.php';
}

// override globals
if (is_readable(__DIR__ . '/globals.php')) {
    require __DIR__ . '/globals.php';
}

// autoloader for bovigo
spl_autoload_register(function ($class) {
    $ns = 'org\\bovigo\\vfs\\';
    $len = strlen($ns);
    if (substr($class, 0, $len) == $ns) {
        $file = substr($class, $len) . '.php';
        require __DIR__ . '/org.bovigo.vfs/' . $file;
    }
});
