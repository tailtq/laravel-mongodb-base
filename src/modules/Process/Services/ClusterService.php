<?php

namespace Modules\Process\Services;

use Infrastructure\BaseService;
use Modules\Process\Repositories\ClusterRepository;

class ClusterService extends BaseService
{
    /**
     * ObjectService constructor.
     * @param \Modules\Process\Repositories\ClusterRepository $repository
     */
    public function __construct(ClusterRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $mongoId
     * @return null
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return null;
    }
}
