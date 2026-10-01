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
        // Set temp directory to Laravel's storage directory to avoid warnings
        // This prevents tempnam() from using system temp directory
        if (function_exists('sys_get_temp_dir')) {
            $storage_path = storage_path('app/temp');
            if (!is_dir($storage_path)) {
                @mkdir($storage_path, 0755, true);
            }
            @ini_set('upload_tmp_dir', $storage_path);
            putenv('TMPDIR=' . $storage_path);
        }
    }
}
