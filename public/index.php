<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// PHP 8.3 fallback: request_parse_body() only exists in PHP 8.4+.
// Symfony's Request::createFromGlobals() calls it for PUT/PATCH/DELETE.
// Apache runs PHP 8.3, so polyfill it so JSON/form bodies are parsed correctly.
if (!function_exists('request_parse_body')) {
    function request_parse_body(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $post = [];
        $files = [];

        if (stripos($contentType, 'application/json') !== false) {
            $decoded = json_decode(file_get_contents('php://input'), true);
            $post = is_array($decoded) ? $decoded : [];
        } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            parse_str(file_get_contents('php://input'), $post);
        } else {
            $post = $_POST;
            $files = $_FILES;
        }

        return [$post, $files];
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
