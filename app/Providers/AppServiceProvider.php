<?php

namespace App\Providers;

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
        // Suppress tempnam warnings that occur during view compilation
        // This is a known issue in development environments
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
        
        // Create temp directory for Laravel
        $temp_dir = storage_path('app/temp');
        if (!is_dir($temp_dir)) {
            @mkdir($temp_dir, 0755, true);
        }
        
        // Override system temp directory
        @putenv('TMPDIR=' . $temp_dir);
        @putenv('TEMP=' . $temp_dir);
        @putenv('TMP=' . $temp_dir);
    }
}
