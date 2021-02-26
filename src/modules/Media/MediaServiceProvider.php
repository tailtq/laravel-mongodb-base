<?php

namespace Modules\Media;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class MediaServiceProvider extends RouteServiceProvider
{
    protected $namespace = 'Modules\Media\Controllers';

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
        Route::prefix('medias')
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('modules/Media/routes.php'));
    }
}
