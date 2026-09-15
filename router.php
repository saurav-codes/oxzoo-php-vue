<?php
// Router for PHP's built-in server. nginx only proxies /api and /health to
// this process; static SPA files are served by nginx from dist/.

header('Content-Type: text/plain; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/api/greeting') {
    echo 'hello world oxzoo-php-vue_' . getenv('GREETING_TAG');
    return;
}

if ($path === '/health') {
    echo 'ok';
    return;
}

http_response_code(404);
