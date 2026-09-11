<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path === '/robots.txt') {
    require __DIR__ . '/robots.php';
    return true;
}

if ($path !== '/' && $path !== '' && file_exists($file) && !is_dir($file)) {
    return false;
}

if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
    return true;
}

http_response_code(404);
require __DIR__ . '/404.php';
