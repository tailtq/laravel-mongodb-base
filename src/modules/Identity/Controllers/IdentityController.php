<?php

namespace Modules\Identity\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Infrastructure\BaseController;
use Modules\Identity\Requests\CreateIdentityRequest;
use Modules\Identity\Services\IdentityService;

class IdentityController extends BaseController
{
    /**
     * IdentityController constructor.
     * @param IdentityService $service
     */
    public function __construct(IdentityService $service)
    {
        parent::__construct('Identity', 'identities');
        $this->service = $service;
    }

    /**
     * @param CreateIdentityRequest $request
     * @return RedirectResponse|JsonResponse
     */
    public function storeNew(CreateIdentityRequest $request)
    {
        return parent::store($request);
    }

    /**
     * @param CreateIdentityRequest $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function updateNew(CreateIdentityRequest $request, $id)
    {
        return parent::update($request, $id);
    }
}
