<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($url !== '/' && is_file(__DIR__ . $url)) {
    return false;
}

$_GET['url'] = ltrim($url, '/');
require __DIR__ . '/index.php';
