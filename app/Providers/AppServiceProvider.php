<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('attendance-scan', function (Request $request): Limit {
            $identity = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)
                ->by((string) $identity)
                ->response(function (Request $request, array $headers) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Terlalu banyak pemindaian dalam waktu singkat. Coba lagi nanti.',
                        ], 429, $headers);
                    }

                    return response()->view('errors.429', status: 429, headers: $headers);
                });
        });
    }
}
