<?php

namespace Modules\Camera\Services;

use Infrastructure\BaseService;
use Modules\Camera\Repositories\CameraRepository;

class CameraService extends BaseService
{
    /**
     * CameraService constructor.
     */
    public function __construct()
    {
        $this->repository = app(CameraRepository::class);
    }
}
