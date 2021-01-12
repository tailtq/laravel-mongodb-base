<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseCRUDRequest;
use App\Traits\RequestAPI;
use Illuminate\Support\MessageBag;

abstract class CRUDController extends Controller
{
    use RequestAPI;

    /**
     * @var $viewDirectory string
     */
    protected $viewDirectory;

    /**
     * @var $model \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function index()
    {
        $items = $this->model::orderBy('created_at', 'desc')->paginate(10);

        return view("pages.$this->viewDirectory.index", [
            'items' => $items,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view("pages.$this->viewDirectory.create");
    }

    /**
     * @param \App\Http\Requests\BaseCRUDRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(BaseCRUDRequest $request)
    {
        $data = $request->validated();
        $response = $this->sendPOSTRequest($this->getAIUrl(), $data, $this->getDefaultHeaders());

        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('message', $response->message);

            if ($request->ajax()) {
                return $this->error($response->message, $response->statusCode);
            }
            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }
        $result = $this->model::create(array_merge($data, [
            'mongo_id' => $response->body->_id
        ]));

        if ($request->ajax()) {
            return $this->success($result);
        }
        return redirect()->route($this->viewDirectory);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $item = $this->model::findOrFail($id);

        return view("pages.$this->viewDirectory.edit", [
            'item' => $item,
        ]);
    }

    /**
     * @param \App\Http\Requests\BaseCRUDRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(BaseCRUDRequest $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $data = $request->validated();
        $response = $this->sendPUTRequest($this->getAIUrl($item->mongo_id), $data, $this->getDefaultHeaders());

        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('message', $response->message);

            if ($request->ajax()) {
                return $this->error($response->message, $response->statusCode);
            }
            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }
        $result = $this->model::where('id', $id)->update($data);

        if ($request->ajax()) {
            return $this->success($result);
        }
        return redirect()->route($this->viewDirectory);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $item = $this->model::findOrFail($id);
        $response = $this->sendDELETERequest($this->getAIUrl($item->mongo_id), [], $this->getDefaultHeaders());

        if (!$response->status) {
            abort(500, $response->message);
        }
        $item->delete();

        return redirect()->route($this->viewDirectory);
    }

    abstract protected function getAIUrl($mongoId = '');
}
