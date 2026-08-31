<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!app()->isProduction());

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // General API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiter for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            $key = $request->input('email', '') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many authentication attempts. Please wait 1 minute before trying again.',
                    'status' => 429,
                ], 429);
            });
        });

        // Protected rate limiter for medical record queries
        RateLimiter::for('records', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
