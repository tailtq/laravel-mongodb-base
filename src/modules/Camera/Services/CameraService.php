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

    /**
     * @param string|null $mongoId
     * @return string
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return config('app.ai_server') . '/cameras' . ($mongoId ? "/$mongoId" : '');
    }
}
