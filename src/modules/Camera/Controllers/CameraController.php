<?php

namespace Modules\Camera\Controllers;

use Illuminate\Http\Request;
use Infrastructure\BaseController;
use Modules\Camera\Services\CameraService;

class CameraController extends BaseController
{
    /**
     * CameraController constructor.
     * @param \Modules\Camera\Services\CameraService $service
     */
    public function __construct(CameraService $service)
    {
        $this->service = $service;
    }

    /**
     *
     */
    public function index()
    {

    }

    /**
     *
     */
    public function create()
    {

    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {

    }

    /**
     * @param $id
     */
    public function edit($id)
    {

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * @param $id
     */
    public function delete($id)
    {

    }
}
