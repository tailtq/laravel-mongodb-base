<?php

namespace Modules\User\Controllers;

use App\Helpers\MongoDB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Infrastructure\BaseController;
use Modules\User\Requests\CreateUserRequest;
use Modules\User\Services\UserService;

class UserController extends BaseController
{
    /**
     * UserController constructor.
     * @param UserService $service
     */
    public function __construct(UserService $service)
    {
        parent::__construct('User', 'users');
        $this->service = $service;
    }

    /**
     * @param CreateUserRequest $request
     * @return RedirectResponse|JsonResponse
     */
    public function storeNew(CreateUserRequest $request)
    {
        return parent::store($request);
    }
}
