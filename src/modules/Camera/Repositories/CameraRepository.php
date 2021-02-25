<?php

namespace Modules\Camera\Repositories;

use Infrastructure\BaseRepository;
use Modules\Camera\Models\Camera;

class CameraRepository extends BaseRepository
{
    /**
     * CameraRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Camera::class);
    }
}
