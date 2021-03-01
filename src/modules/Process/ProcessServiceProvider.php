<?php

namespace Modules\Process;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class ProcessServiceProvider extends RouteServiceProvider
{
    protected $namespace = 'Modules\Process\Controllers';

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
        Route::prefix('processes')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('modules/Process/routes.php'));

        Route::prefix('objects')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('modules/Process/routes-object.php'));
    }
}
