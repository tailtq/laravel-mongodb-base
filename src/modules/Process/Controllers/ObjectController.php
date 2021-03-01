<?php

namespace Modules\Process\Controllers;

use Illuminate\Http\Request;
use Infrastructure\BaseController;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Services\ObjectService;

class ObjectController extends BaseController
{
    /**
     * ProcessController constructor.
     * @param \Modules\Process\Services\ObjectService $service
     */
    public function __construct(ObjectService $service)
    {
        parent::__construct('Object', 'objects');
        $this->service = $service;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function startRendering(Request $request, $id)
    {
        $result = $this->service->startRendering($id);

        if ($result instanceof ResourceNotFoundException) {
            return $this->error('Không tìm thấy đối tượng', 404);
        } else if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }

        return $this->success();
    }
}
