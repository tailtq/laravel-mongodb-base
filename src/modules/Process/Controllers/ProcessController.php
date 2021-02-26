<?php

namespace Modules\Process\Controllers;

use Infrastructure\BaseController;
use Modules\Process\Services\ProcessService;

class ProcessController extends BaseController
{
    /**
     * ProcessController constructor.
     * @param \Modules\Process\Services\ProcessService $service
     */
    public function __construct(ProcessService $service)
    {
        parent::__construct('Process', 'processes');
        $this->service = $service;
    }
}
