<?php

namespace Modules\Identity\Controllers;

use Infrastructure\BaseController;
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
}
