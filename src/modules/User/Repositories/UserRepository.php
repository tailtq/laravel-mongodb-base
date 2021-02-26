<?php

namespace Modules\User\Repositories;

use Infrastructure\BaseRepository;
use Modules\User\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(User::class);
    }
}
