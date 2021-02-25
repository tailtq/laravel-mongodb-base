<?php

namespace Modules\Process\Repositories;

use Infrastructure\BaseRepository;
use Modules\Process\Models\Process;

class ProcessRepository extends BaseRepository
{
    /**
     * ProcessRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Process::class);
    }
}
