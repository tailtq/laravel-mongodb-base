<?php

namespace Modules\User\Controllers;

use Infrastructure\BaseController;
use Modules\Identity\Requests\CreateIdentityRequest;
use Modules\User\Requests\CreateUserRequest;
use Modules\User\Services\UserService;

class UserController extends BaseController
{
    /**
     * UserController constructor.
     * @param \Modules\User\Services\UserService $service
     */
    public function __construct(UserService $service)
    {
        parent::__construct('User', 'users');
        $this->service = $service;
    }

    /**
     * @param \Modules\User\Requests\CreateUserRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function storeNew(CreateUserRequest $request)
    {
        return parent::store($request);
    }
}
