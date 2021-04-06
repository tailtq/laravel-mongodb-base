<?php

namespace Modules\Camera;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class CameraServiceProvider extends RouteServiceProvider
{
    /**
     * @var string
     */
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
