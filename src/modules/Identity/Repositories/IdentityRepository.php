<?php

namespace Modules\Identity\Repositories;

use Infrastructure\BaseRepository;
use Modules\Identity\Models\Identity;

class IdentityRepository extends BaseRepository
{
    /**
     * IdentityRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Identity::class);
    }
}
