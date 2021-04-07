<?php

namespace Infrastructure;

use App\Traits\RequestAPI;
use App\Traits\ResponseTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Requests\BaseCRUDRequest;
use Infrastructure\Exceptions\ResourceNotFoundException;

class BaseController
{
    use RequestAPI, ResponseTrait;

    /**
     * @var \Infrastructure\BaseService $service;
     */
    protected $service;

    /**
     * @var string $moduleName
     */
    protected $moduleName;

    /**
     * @var string $route
     */
    protected $route;

    public function __construct(string $moduleName, string $route)
    {
        $this->moduleName = $moduleName;
        $this->route = $route;
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        $items = $this->service->paginate();

        return view("pages.$this->route.index", [
            'items' => $items
        ]);
    }

    /**
     * @return Factory|View
     */
    public function create()
    {
        return view("pages.$this->route.create");
    }

    /**
     * @param BaseCRUDRequest $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(BaseCRUDRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->createAndSync($data);

        if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        if ($request->ajax()) {
            return $this->success($result);
        }
        return redirect()->route($this->route);
    }

    /**
     * @param int $id
     * @return Factory|View
     */
    public function edit(string $id)
    {
        $item = $this->service->findById($id);

        if ($item instanceof ResourceNotFoundException) {
            abort(404);
        }
        return view("pages.$this->route.edit", [
            'item' => $item,
        ]);
    }

    /**
     * @param BaseCRUDRequest $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function update(BaseCRUDRequest $request, $id)
    {
        $data = $request->validated();
        $result = $this->service->update($data, $id);

        if ($result instanceof ResourceNotFoundException) {
            abort(404);
        } else if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        }
        if ($request->ajax()) {
            return $this->success($result);
        }
        return redirect()->back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $result = $this->service->delete($id);

        if ($result instanceof ResourceNotFoundException) {
            abort(404);
        }
        return redirect()->route($this->route);
    }

    /**
     * @param \Infrastructure\Exceptions\CustomException $result
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|RedirectResponse
     */
    protected function returnFailedResult(CustomException $result, Request $request)
    {
        if ($request->ajax()) {
            return $this->error($result->getData()->message, $result->getCode());
        }
        $messageBag = new MessageBag();
        $messageBag->add('message', $result->getData()->message);

        return redirect()->back()->withErrors($messageBag)->withInput($request->all());
    }
}
