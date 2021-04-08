<?php

namespace Modules\Camera\Services;

use Infrastructure\BaseService;
use Modules\Camera\Repositories\CameraRepository;

class CameraService extends BaseService
{
    /**
     * CameraService constructor.
     * @param CameraRepository $repository
     */
    public function __construct(CameraRepository $repository)
    {
        $this->repository = $repository;
    }
}
