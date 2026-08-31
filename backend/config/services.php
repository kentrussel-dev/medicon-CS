<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ml_service' => [
        'url' => env('ML_SERVICE_URL', 'http://ml-service:8000'),
        'timeout' => (float) env('ML_SERVICE_TIMEOUT_SECONDS', 2.5),
        'high_risk_threshold' => (float) env('ML_HIGH_RISK_THRESHOLD', 0.65),
        'medium_risk_threshold' => (float) env('ML_MEDIUM_RISK_THRESHOLD', 0.35),
    ],

    'retention' => [
        'audit_log_days' => (int) env('RETENTION_AUDIT_LOG_DAYS', 2555), // 7 years HIPAA compliance
        'soft_deleted_days' => (int) env('RETENTION_SOFT_DELETED_DAYS', 365),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'cache_ttl' => (int) env('GEMINI_CACHE_TTL_SECONDS', 86400),
    ],
];
