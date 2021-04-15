<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Infrastructure\BaseService;
use Modules\User\Repositories\UserRepository;

class UserService extends BaseService
{
    /**
     * UserService constructor.
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     * @param bool $id
     * @return array|bool|int
     */
    public function createAndSync(array $data, $id = false)
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }
}
