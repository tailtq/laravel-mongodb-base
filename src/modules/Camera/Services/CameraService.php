<?php

namespace Modules\Camera\Services;

use Infrastructure\BaseService;
use Modules\Camera\Repositories\CameraRepository;

class CameraService extends BaseService
{
    /**
     * CameraService constructor.
     * @param \Modules\Camera\Repositories\CameraRepository $repository
     */
    public function __construct(CameraRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $mongoId
     * @return string
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return config('app.ai_server') . '/cameras' . ($mongoId ? "/$mongoId" : '');
    }
}
