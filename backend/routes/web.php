<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'platform' => 'Medicon Telehealth and Patient Management Platform',
        'status' => 'online',
        'api_version' => 'v1',
        'docs_url' => '/docs',
    ]);
});
