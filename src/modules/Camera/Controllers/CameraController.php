<?php

namespace Modules\Camera\Controllers;

use Infrastructure\BaseController;
use Modules\Camera\Services\CameraService;

class CameraController extends BaseController
{
    /**
     * CameraController constructor.
     * @param CameraService $service
     */
    public function __construct(CameraService $service)
    {
        parent::__construct('Camera', 'cameras');
        $this->service = $service;
    }
}
