<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentityCreateRequest;
use App\Models\Camera;
use App\Models\Identity;
use App\Traits\RequestAPI;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

/**
 * Apply BaseModule for syncing data between 2 servers
 * Class CameraController
 * @package App\Http\Controllers
 */
class CameraController extends CRUDController
{
    use RequestAPI;

    protected $model = Camera::class;

    protected $viewDirectory = 'cameras';

    /**
     * @param string $mongoId
     * @return string
     */
    protected function getAIUrl($mongoId = '')
    {
        return config('app.ai_server') . '/cameras' . ($mongoId ? "/$mongoId" : '');
    }
}
