<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'spring.auth' => \App\Http\Middleware\SpringAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token invalide'], 403);
            }

            if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token expiré, veuillez vous reconnecter'], 401);
            }

            if ($e instanceof JWTException) {
                return response()->json(['error' => 'Token absent'], 401);
            }
        });
    })->create();
