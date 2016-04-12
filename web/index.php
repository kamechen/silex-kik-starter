<?php
require_once __DIR__ . '/../vendor/autoload.php';

// For php native web server
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app = new Newsletter\Application(getenv('SILEX_ENV') ?: 'dev');
$app->run();
