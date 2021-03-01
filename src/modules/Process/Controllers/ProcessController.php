<?php

namespace Modules\Process\Controllers;

use Illuminate\Http\Request;
use Infrastructure\BaseController;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Requests\CreateProcessRequest;
use Modules\Process\Services\ProcessService;

class ProcessController extends BaseController
{
    /**
     * ProcessController constructor.
     * @param \Modules\Process\Services\ProcessService $service
     */
    public function __construct(ProcessService $service)
    {
        parent::__construct('Process', 'processes');
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $data = $this->service->getIndexPageData();

        return view('pages.processes.index', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $data = $this->service->getDetailPageData($id);

        if ($data instanceof ResourceNotFoundException) {
            abort(404);
        }
        return view('pages.processes.detail', $data);
    }

    /**
     * @param \Modules\Process\Requests\CreateProcessRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNew(CreateProcessRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->create($data);

        if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        return $this->success(['id' => $result]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startProcess(Request $request)
    {
        $result = $this->service->startProcess($request->processId);

        if ($result instanceof ResourceNotFoundException) {
            return $this->error('Không tìm thấy luồng xử lý', 404);
        } else if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        return $this->success('Bắt đầu thành công');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopProcess(Request $request)
    {
        $result = $this->service->stopProcess($request->processId);

        if ($result instanceof ResourceNotFoundException) {
            return $this->error('Không tìm thấy luồng xử lý', 404);
        } else if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        return $this->success('Kết thúc thành công');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderVideo(Request $request)
    {
        $result = $this->service->callAIService($request->processId, 'rendering', 'GET');

        if ($result instanceof ResourceNotFoundException) {
            return $this->error('Không tìm thấy luồng xử lý', 404);
        } else if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        return $this->success('Đang tổng hợp video');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjects($id)
    {
        $objects = $this->service->getObjects($id);

        return $this->success($objects);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailAfterSuccessOrStop($id)
    {
        $process = $this->service->getProcessDetail($id);

        if ($process instanceof ResourceNotFoundException) {
            return $this->error('RESOURCE_NOT_FOUND', 404);
        }
        return $this->success($process);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function exportBeforeGrouping($id)
    {
        $result = $this->service->callAIService($id, 'report/before-grouping', 'POST');

        if ($result instanceof ResourceNotFoundException) {
            abort(404);
        } else if ($result instanceof CustomException) {
            abort(400);
        }
        return redirect($result->body->url);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function exportAfterGrouping($id)
    {
        $result = $this->service->callAIService($id, 'report/after-grouping', 'POST');

        if ($result instanceof ResourceNotFoundException) {
            abort(404);
        } else if ($result instanceof CustomException) {
            abort(400);
        }
        return redirect($result->body->url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchFace(Request $request)
    {
        $ids = json_decode($request->get('process_ids', '[]'));
        $searchType = $request->get('search_type');
        $file = $request->file('file');
        $objectId = $request->get('object_id');

        $objects = $this->service->searchFace($ids, $searchType, $file, $objectId);

        if ($objects instanceof ResourceNotFoundException) {
            return $this->error('RESOURCE_NOT_FOUND', 404);
        }

        return $this->success($objects);
    }
}
