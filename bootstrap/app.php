<?php

use App\Http\Middleware\AuthLivewireApiMiddleware;
use App\Http\Middleware\ForceJsonResponseMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            ForceJsonResponseMiddleware::class,
        ]);

        $middleware->alias([
            'json.response' => ForceJsonResponseMiddleware::class,
            'auth.api.livewire' => AuthLivewireApiMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Selected Record Not Found!',
                        'code' => 404,
                    ], 404);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Api Resource Not Found!',
                    'code' => 404,
                ], 404);
            }

            return null;
        });
    })->create();
