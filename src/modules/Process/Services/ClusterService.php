<?php

namespace Modules\Process\Services;

use Infrastructure\BaseService;
use Modules\Process\Repositories\ObjectRepository;

class ClusterService extends BaseService
{
    /**
     * ObjectService constructor.
     * @param \Modules\Process\Repositories\ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
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
