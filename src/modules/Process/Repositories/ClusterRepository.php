<?php

namespace Modules\Process\Repositories;

use Infrastructure\BaseRepository;
use Modules\Process\Models\Cluster;

class ClusterRepository extends BaseRepository
{
    /**
     * ProcessRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Cluster::class);
    }
}
