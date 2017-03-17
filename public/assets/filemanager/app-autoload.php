<?php

require_once __DIR__ . '/../../../bootstrap/autoload.php';

require_once __DIR__ . '/../../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$middlewares = [
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
];

foreach ($middlewares as $middleware) {
    $kernel->pushMiddleware($middleware);
}

$response = $kernel->handle(Illuminate\Http\Request::capture());