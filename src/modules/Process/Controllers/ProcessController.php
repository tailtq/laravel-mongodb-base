<?php

namespace Modules\Process\Controllers;

use App\Traits\AnalysisTrait;
use Illuminate\Http\Request;
use Infrastructure\BaseController;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Requests\CreateProcessRequest;
use Modules\Process\Services\ProcessService;

class ProcessController extends BaseController
{
    use AnalysisTrait;

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
        $result = $this->service->renderVideo($request->processId);

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
//
//    /**
//     * @param \Illuminate\Http\Request $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function searchFace(Request $request)
//    {
//        $ids = json_decode($request->get('process_ids', '[]'));
//        $searchType = $request->get('search_type');
//        $processes = Process::whereIn('id', $ids)->get();
//
//        $url = config('app.ai_server') . "/processes/faces/searching";
//        $payload = [
//            'process_ids' => $processes->pluck('mongo_id')->all(),
//            'type_search' => $searchType,
//            'threshold' => $searchType === 'face' ? 1.05 : 0.7
//        ];
//
//        if ($request->hasFile('file')) {
//            $file = $request->file('file');
//            $payload['image_url'] = $this->uploadFile($file);
//        } else {
//            $objectId = $request->get('object_id');
//            $payload['object_id'] = DB::table('objects')->where('id', $objectId)->first()->mongo_id;
//        }
//        $response = $this->sendPOSTRequest($url, $payload, $this->getDefaultHeaders());
//        $searchedObjects = $response->body;
//        $objectMongoIds = Arr::pluck($searchedObjects, 'object_id');
//
//        // handle error cases
//        // laravel receive image --> save to min_io + search
//
//        $objects = DB::table('objects')
//            ->join('processes', 'objects.process_id', 'processes.id')
//            ->leftJoin('clusters', 'objects.cluster_id', 'clusters.id')
//            ->leftJoin('identities as CI', 'clusters.identity_id', 'CI.id')
//            ->leftJoin('identities as OI', 'objects.identity_id', 'OI.id')
//            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
//                $query->select(DB::raw('MIN(O.id)'))
//                    ->from('objects AS O')
//                    ->whereIn('O.mongo_id', $objectMongoIds)
//                    ->groupBy(DB::raw('IFNULL(O.cluster_id, UUID())'));
//            })
//            ->select([
//                'objects.*',
//                'OI.id as identity_id',
//                'OI.name as identity_name',
//                'OI.images as identity_images',
//                'CI.id as cluster_identity_id',
//                'CI.name as cluster_identity_name',
//                'CI.images as cluster_identity_images',
//                'processes.name as process_name',
//            ])
//            ->get();
//        $objects = DatabaseHelper::blendObjectsIdentity($objects);
//        $objects = $this->getAppearances($objects, $processes->count() > 0);
//
//        foreach ($objects as &$object) {
//            $object->confidence_rate = null;
//            foreach ($searchedObjects as $searchedObject) {
//                if ($searchedObject->object_id == $object->mongo_id) {
//                    $object->distance = $searchedObject->search_distance;
//                }
//            }
//        }
//        $objects = array_values($objects->sortBy('distance')->toArray());
//
//        return $this->success($objects);
//    }
//
//    /**
//     * @param $id
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function getDetail($id)
//    {
//        $process = Process::find($id);
//
//        if (!$process) {
//            return $this->error('RESOURCE_NOT_FOUND', 404);
//        }
//        if ($process->status === Process::STATUS['done'] || $process->status === Process::STATUS['stopped']) {
//            $process->detecting_duration = $this->parseTime($process->detecting_start_time, $process->detecting_end_time);
//            $process->total_duration = $this->parseTime($process->detecting_start_time, $process->done_time);
//        }
//
//        return $this->success($process);
//    }
//
//    /**
//     * @param $id
//     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
//     */
//    public function exportBeforeGrouping($id)
//    {
//        $process = Process::findOrFail($id);
//        $url = config('app.ai_server') . "/processes/$process->mongo_id/report/before-grouping";
//        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());
//
//        if (!$response->status) {
//            abort(400);
//        }
//
//        return redirect($response->body->url);
//    }
//
//    /**
//     * @param $id
//     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
//     */
//    public function exportAfterGrouping($id)
//    {
//        $process = Process::findOrFail($id);
//        $url = config('app.ai_server') . "/processes/$process->mongo_id/report/after-grouping";
//        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());
//
//        if (!$response->status) {
//            abort(400);
//        }
//
//        return redirect($response->body->url);
//    }
//
//    public function getThumbnail(Request $request)
//    {
//        $thumbnailData = $this->sendPOSTRequest(config('app.ai_server') . '/medias/thumbnails', [
//            'url' => $request->get('video_url'),
//            'size' => [640, 480]
//        ]);
//
//        if (!$thumbnailData->status) {
//            return $this->error($thumbnailData->message, $thumbnailData->statusCode);
//        } else if (!$thumbnailData->body->url) {
//            return $this->error('Đường dẫn không hợp lệ', 400);
//        }
//
//        return $this->success([
//            'thumbnail' => $thumbnailData->body->url,
//        ]);
//    }
}
