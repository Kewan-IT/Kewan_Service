<?php
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($url, '/storage/')) {
    $caminho = dirname(__DIR__) . '/storage/' . substr($url, 9);
    if (is_file($caminho)) {
        $mime = mime_content_type($caminho) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($caminho));
        readfile($caminho);
        exit;
    }
    http_response_code(404);
    exit;
}

if ($url !== '/' && is_file(__DIR__ . $url)) {
    return false;
}

$_GET['url'] = ltrim($url, '/');
require __DIR__ . '/index.php';
