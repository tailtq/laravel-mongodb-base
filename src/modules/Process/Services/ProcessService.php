<?php

namespace Modules\Process\Services;

use Infrastructure\BaseService;
use Modules\Process\Repositories\ProcessRepository;

class ProcessService extends BaseService
{
    /**
     * ProcessService constructor.
     */
    public function __construct()
    {
        $this->repository = app(ProcessRepository::class);
    }
}
