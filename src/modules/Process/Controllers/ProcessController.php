<?php

namespace Modules\Process\Controllers;

use Illuminate\Http\Request;
use Infrastructure\BaseController;
use Modules\Process\Services\ProcessService;

class ProcessController extends BaseController
{
    /**
     * ProcessController constructor.
     * @param \Modules\Process\Services\ProcessService $service
     */
    public function __construct(ProcessService $service)
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
