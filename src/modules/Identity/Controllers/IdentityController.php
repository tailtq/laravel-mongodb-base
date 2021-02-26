<?php

namespace Modules\Identity\Controllers;

use Infrastructure\BaseController;
use Modules\Identity\Requests\CreateIdentityRequest;
use Modules\Identity\Services\IdentityService;

class IdentityController extends BaseController
{
    /**
     * IdentityController constructor.
     * @param \Modules\Identity\Services\IdentityService $service
     */
    public function __construct(IdentityService $service)
    {
        parent::__construct('Identity', 'identities');
        $this->service = $service;
    }

    /**
     * @param \Modules\Identity\Requests\CreateIdentityRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function storeNew(CreateIdentityRequest $request)
    {
        return parent::store($request);
    }

    /**
     * @param \Modules\Identity\Requests\CreateIdentityRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updateNew(CreateIdentityRequest $request, $id)
    {
        return parent::update($request, $id);
    }
}
