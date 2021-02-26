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

    /**
     * @param string|null $mongoId
     * @return string
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return config('app.ai_server') . '/processes' . ($mongoId ? "/$mongoId" : '');
    }
}
