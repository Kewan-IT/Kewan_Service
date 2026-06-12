<?php
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($url !== '/' && is_file(__DIR__ . $url)) {
    return false;
}

$_GET['url'] = ltrim($url, '/');
require __DIR__ . '/index.php';
