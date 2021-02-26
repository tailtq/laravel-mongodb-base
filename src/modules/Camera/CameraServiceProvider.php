<?php

namespace Modules\Camera;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class CameraServiceProvider extends RouteServiceProvider
{
    protected $namespace = 'Modules\Camera\Controllers';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::prefix('cameras')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('modules/Camera/routes.php'));
    }
}
