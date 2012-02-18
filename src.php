<?php

require_once __DIR__ . '/src/Aura/Http/Exception.php';
require_once __DIR__ . '/src/Aura/Http/Exception/InvalidHandle.php';
require_once __DIR__ . '/src/Aura/Http/Exception/NotWriteable.php';
require_once __DIR__ . '/src/Aura/Http/Exception/FileDoesNotExist.php';
require_once __DIR__ . '/src/Aura/Http/Exception/FullUrlExpected.php';
require_once __DIR__ . '/src/Aura/Http/Exception/HeadersSent.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnableToDecompressContent.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownAuthType.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownMethod.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownStatus.php';
require_once __DIR__ . '/src/Aura/Http/Exception/UnknownVersion.php';

require_once __DIR__ . '/src/Aura/Http/Response.php';
require_once __DIR__ . '/src/Aura/Http/Request.php';
require_once __DIR__ . '/src/Aura/Http/Request/Adapter/AdapterInterface.php';
require_once __DIR__ . '/src/Aura/Http/Request/Adapter/Curl.php';
require_once __DIR__ . '/src/Aura/Http/Request/Adapter/Stream.php';
require_once __DIR__ . '/src/Aura/Http/Request/Multipart.php';
require_once __DIR__ . '/src/Aura/Http/Request/Response.php';
require_once __DIR__ . '/src/Aura/Http/Request/ResponseStack.php';
require_once __DIR__ . '/src/Aura/Http/Request/ResponseBuilder.php';
require_once __DIR__ . '/src/Aura/Http/Cookie.php';
require_once __DIR__ . '/src/Aura/Http/Header.php';
require_once __DIR__ . '/src/Aura/Http/Cookies.php';
require_once __DIR__ . '/src/Aura/Http/Headers.php';
require_once __DIR__ . '/src/Aura/Http/Factory/Cookie.php';
require_once __DIR__ . '/src/Aura/Http/Factory/Header.php';
require_once __DIR__ . '/src/Aura/Http/Factory/ResponseStack.php';