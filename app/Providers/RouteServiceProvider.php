<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * @var string
     */
    public const HOME = '/home'; // Default home route

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Define rate limiting rules
        RateLimiter::for('api', function (Request $request) {
            // Allows 60 requests per minute by default for all API routes
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for IoT devices (more permissive)
        RateLimiter::for('iot', function (Request $request) {
            // Allows 300 requests per minute for IoT devices
            return Limit::perMinute(300)->by($request->ip());
        });

        $this->routes(function () {
            // 1. Load the default routes (Web and other general API routes)
            Route::middleware('web')
                ->group(base_path('routes/web.php')); // If you use web.php

            // The default routes.php is often loaded here too, but we'll focus on API v1.
            Route::middleware('api')->group(base_path('routes/routes.php'));
            
            // 2. Load the specific API V1 routes (Best Practice for versioning)
            $this->mapApiV1Routes();
        });
    }

    /**
     * Define the "api v1" routes for the application.
     * These routes are typically stateless and use the 'api' middleware.
     */
    protected function mapApiV1Routes(): void
    {
        Route::middleware('api')
             ->prefix('api/v1') // The version prefix
             ->group(base_path('routes/api_v1.php')); // The dedicated file
    }
}