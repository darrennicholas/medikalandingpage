<?php

use App\Http\Middleware\EnsureCmsPermission;
use App\Http\Middleware\EnsureCmsRole;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request): string => route('admin.login', absolute: false));
        $middleware->alias([
            'cms.role' => EnsureCmsRole::class,
            'cms.permission' => EnsureCmsPermission::class,
            'cms.password' => EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (
                $request->routeIs('admin.login.store')
                && $exception instanceof HttpExceptionInterface
                && $exception->getStatusCode() === 419
            ) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'data' => null,
                        'message' => 'Sesi login sudah kedaluwarsa. Muat ulang halaman, lalu coba lagi.',
                        'errors' => [],
                    ], 419);
                }

                return redirect()->to(route('admin.login', absolute: false))->withErrors([
                    'email' => 'Sesi login sudah kedaluwarsa. Muat ulang halaman, lalu coba lagi.',
                ]);
            }

            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                    'meta' => [],
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Autentikasi diperlukan.',
                    'errors' => [],
                    'meta' => [],
                ], 401);
            }

            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = $status === 429 ? 'Terlalu banyak permintaan. Silakan coba lagi nanti.' : ($status >= 500 ? 'Terjadi kesalahan pada server.' : $exception->getMessage());

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $message,
                'errors' => [],
                'meta' => [],
            ], $status, $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);
        });
    })->create();
