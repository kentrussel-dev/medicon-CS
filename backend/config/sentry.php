<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Capture release as git commit or version
    'release' => env('SENTRY_RELEASE', 'medicon@1.0.0'),

    'environment' => env('APP_ENV', 'local'),

    // Sample rate for error events (100% in production)
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),

    // Performance tracing sample rate
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    'send_default_pii' => false,

    'ignore_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],
];
