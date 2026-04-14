<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Vercel's filesystem is read-only — redirect writable paths to /tmp
if (isset($_ENV['VERCEL'])) {
    $storagePath = '/tmp/storage';
    $bootstrapPath = '/tmp/bootstrap';

    foreach ([
        $storagePath.'/app/public',
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/framework/views',
        $storagePath.'/logs',
        $bootstrapPath.'/cache',
    ] as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($bootstrapPath);
}

return $app;
