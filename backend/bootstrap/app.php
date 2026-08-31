<?php

use App\Services\Logging\HipaaPiiScrubber;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sentry Error Monitoring & HIPAA PII Scrubbing
        $exceptions->reportable(function (\Throwable $e) {
            if (app()->bound('sentry') && app()->environment('production')) {
                $event = [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                ];
                $scrubbed = HipaaPiiScrubber::processEvent($event, request());
                Log::channel('single')->info('Sentry report processed with HIPAA scrubbing.', $scrubbed);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated or session expired.',
                    'status' => 401,
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.',
                    'status' => 403,
                ], 403);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Requested resource not found.',
                    'status' => 404,
                ], 404);
            }
        });
    })->create();
