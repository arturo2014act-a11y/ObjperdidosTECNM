<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // RF-62: Alias para protección estricta entre roles con 404 Not Found
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Enlace de verificación de correo vencido o con firma inválida (RF-69, RF-70)
        $exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, $request) {
            return redirect()->route('verification.notice')
                ->with('error', 'El enlace de verificación ha caducado o es inválido. Solicita un nuevo enlace.');
        });

        // Sesión expirada por inactividad o formulario vencido (HTTP 419)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->route('login')
                    ->with('warning', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente para continuar.');
            }
        });

        // Petición a ruta protegida con sesión vencida
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->route('login')
                    ->with('warning', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente para continuar.');
            }
        });
    })->create();
