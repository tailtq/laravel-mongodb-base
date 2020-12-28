<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCreateRequest;
use App\Models\Camera;
use App\Models\ObjectAppearance;
use App\Models\Process;
use App\Models\TrackedObject;
use App\Traits\RequestAPI;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use DB;

class ProcessController extends Controller
{
    use RequestAPI;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $cameras = Camera::select(['id', 'name', 'url'])->orderBy('created_at', 'desc')->get();
        $processes = Process::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.processes.index', [
            'processes' => $processes,
            'cameras' => $cameras,
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $process = Process::with('camera')->select('*',
            DB::raw("
                (SELECT COUNT(*) FROM objects
                 WHERE objects.process_id = $id) as grouped_count"),
            DB::raw("
                (SELECT COUNT(objects.process_id) FROM objects
                 WHERE objects.process_id = $id 
                 AND objects.identity_id is not NULL
                 AND objects.matching_status = '" . TrackedObject::MATCHING_STATUS['identified'] . "') as identified_count"),
            DB::raw("
                (SELECT COUNT(objects.process_id) FROM objects
                 WHERE objects.process_id = $id 
                 AND objects.identity_id is NULL
                 AND objects.matching_status = '" . TrackedObject::MATCHING_STATUS['identified'] . "') as unidentified_count")
        )->where('id', $id)->first();
        $matchingPercentage = 0;
        $matchingText = '(0/0)';

        if (!$process) {
            abort(404);
        }
        if ($process->ungrouped_count != 0) {
            $totalMatched = in_array($process->status, ['detecting', 'detected'])
                ? $process->identified_count + $process->unidentified_count
                : $process->ungrouped_count;
            $totalObjects = $process->ungrouped_count;

            $matchingText = "($totalMatched/$totalObjects)";
            $matchingPercentage = (int) (100 * $totalMatched / ($totalObjects));
        }
        if (!in_array($process->status, ['done', 'grouped', 'rendering'])) {
            $process->grouped_count = 0;
            $process->identified_count = 0;
            $process->unidentified_count = 0;
        }
        if ($process->status === Process::STATUS['done']) {
            $process->detecting_duration = $this->parseTime($process->detecting_start_time, $process->detecting_end_time);
            $process->matching_duration = $this->parseTime($process->matching_start_time, $process->grouping_start_time);
            $process->rendering_duration = $this->parseTime($process->rendering_start_time, $process->done_time);
            $process->total_duration = $this->parseTime($process->detecting_start_time, $process->done_time);
        }
        $processData = $this->sendGETRequest(
            config('app.ai_server') . "/processes/$process->mongo_id", [], $this->getDefaultHeaders()
        );

        $process->config = $processData->status ? $processData->body->config : null;

        return view('pages.processes.detail', array_merge([
            'process' => $process,
            'matchingText' => $matchingText,
        ], $this->getProgressing($process->status, $matchingPercentage)));
    }

    /**
     * @param ProcessCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProcessCreateRequest $request)
    {
        $data = $request->validationData();
        $cameraId = Arr::get($data, 'camera_id');
        $camera = $cameraId ? Camera::find($cameraId) : null;

        $processData = $this->sendPOSTRequest(config('app.ai_server') . '/processes', [
            'camera' => $camera ? $camera->mongo_id : null,
            'name' => $data['name'],
            'url' => $camera ? null : $data['video_url'],
            'status' => Process::STATUS['ready'],
            'detection_scale' => $data['detection_scale'],
            'frame_drop' => $data['frame_drop'],
            'frame_step' => $data['frame_step'],
            'max_pitch' => $data['max_pitch'],
            'max_roll' => $data['max_roll'],
            'max_yaw' => $data['max_yaw'],
            'min_face_size' => $data['min_face_size'],
            'tracking_scale' => $data['tracking_scale'],
            'biometric_threshold' => $data['biometric_threshold'],
            'min_head_confidence' => $data['min_head_confidence'],
            'min_face_confidence' => $data['min_face_confidence'],
            'min_body_confidence' => $data['min_body_confidence'],
            'regions' => $data['regions'],
        ], $this->getDefaultHeaders());

        if (!$processData->status) {
            return $this->error($processData->message, $processData->statusCode);
        }

        $process = Process::create([
            'user_id' => Auth::id(),
            'camera_id' => $camera ? $camera->id : null,
            'name' => $data['name'],
            'thumbnail' => $data['thumbnail'],
            'video_url' => $camera ? null : $data['video_url'],
            'description' => $data['description'],
            'status' => Process::STATUS['ready'],
            'mongo_id' => $processData->body->_id,
            'total_time' => $processData->body->total_time,
            'total_frames' => $processData->body->total_frames,
            'fps' => $processData->body->fps,
        ]);

        return $this->success($process);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startProcess(Request $request)
    {
        $process = Process::find($request->processId);

        if (!$process) {
            return $this->error('Không tìm thấy luồng xử lý', 404);
        }
        $processData = $this->sendGETRequest(
            config('app.ai_server') . "/processes/$process->mongo_id/start", [], $this->getDefaultHeaders()
        );
        if (!$processData->status) {
            return $this->error($processData->body->message, 400);
        }
        $process->update([
            'detecting_start_time' => Carbon::now(),
            'status' => Process::STATUS['detecting'],
        ]);

        return $this->success('Bắt đầu thành công');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopProcess(Request $request)
    {
        $process = Process::findOrFail($request->processId);

        if (!$process) {
            return $this->error('Không tìm thấy luồng xử lý', 404);
        }
        $processData = $this->sendGETRequest(
            config('app.ai_server') . "/processes/$process->mongo_id/stop", [], $this->getDefaultHeaders()
        );
        if (!$processData->status) {
            return $this->error($processData->body->message, 400);
        }
        $process->update(['status' => Process::STATUS['stopped']]);

        return $this->success('Kết thúc thành công');
    }

    /**
     * @param $processId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjects($processId)
    {
        $objects = TrackedObject::leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->where('process_id', $processId)
            ->select([
                'objects.id',
                'objects.process_id',
                'objects.mongo_id',
                'objects.track_id',
                'objects.identity_id',
                'objects.image',
                'objects.video_result',
                'identities.name',
                'identities.images'
            ])
            ->orderBy('objects.track_id')
            ->with('appearances')
            ->get();

        return $this->success($objects);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $process = Process::findOrFail($id);
        $url = config('app.ai_server') . "/processes/$process->mongo_id";
        $response = $this->sendDELETERequest($url, [], $this->getDefaultHeaders());

        if (!$response->status) {
            abort(400, $response->message);
        }
        $objectIds = TrackedObject::where('process_id', $id)->pluck('id');

        ObjectAppearance::whereIn('object_id', $objectIds)->delete();
        TrackedObject::where('process_id', $id)->delete();
        $process->delete();

        return redirect()->route('processes');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchFace(Request $request)
    {
        $ids = json_decode($request->get('process_ids'));
        $processes = Process::whereIn('id', $ids)->get();

        $file = $request->file('file');
        $imageUrl = $this->uploadFile($file);

        $url = config('app.ai_server') . "/processes/faces/searching";
        $requestBody = [
            'image_url' => $imageUrl,
            'process_ids' => $processes->pluck('mongo_id')->all(),
        ];
        $response = $this->sendPOSTRequest($url, $requestBody, $this->getDefaultHeaders());
        $searchedObjects = $response->body;
        $objectMongoIds = Arr::pluck($searchedObjects, 'object_id');

        // handle error cases
        // laravel receive image --> save to min_io + search

        $objects = TrackedObject::leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->whereIn('objects.mongo_id', $objectMongoIds)
            ->select([
                'objects.id',
                'objects.identity_id',
                'objects.mongo_id',
                'objects.image',
                'objects.video_result',
                'identities.name',
                'identities.images',
            ])
            ->orderBy('objects.track_id')
            ->with('appearances')
            ->get();

        foreach ($objects as &$object) {
            $searchedObject = Arr::first($searchedObjects, function ($searchedObject) use ($object) {
                return $searchedObject->object_id === $object->mongo_id;
            });
            $object->confidence = ($searchedObject->confidence ?? 0) * 100;
        }

        return $this->success($objects);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        $process = Process::find($id);

        if (!$process) {
            return $this->error('RESOURCE_NOT_FOUND', 404);
        }
        if ($process->status === Process::STATUS['done']) {
            $process->detecting_duration = $this->parseTime($process->detecting_start_time, $process->detecting_end_time);
            $process->matching_duration = $this->parseTime($process->matching_start_time, $process->grouping_start_time);
            $process->rendering_duration = $this->parseTime($process->rendering_start_time, $process->done_time);
            $process->total_duration = $this->parseTime($process->detecting_start_time, $process->done_time);
        }

        return $this->success($process);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function exportBeforeGrouping($id)
    {
        $process = Process::findOrFail($id);
        $url = config('app.ai_server') . "/processes/$process->mongo_id/report/before-grouping";
        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());

        if (!$response->status) {
            abort(400);
        }

        return redirect($response->body->url);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function exportAfterGrouping($id)
    {
        $process = Process::findOrFail($id);
        $url = config('app.ai_server') . "/processes/$process->mongo_id/report/after-grouping";
        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());

        if (!$response->status) {
            abort(400);
        }

        return redirect($response->body->url);
    }

    public function getThumbnail(Request $request)
    {
        $thumbnailData = $this->sendPOSTRequest(config('app.ai_server') . '/medias/thumbnails', [
            'url' => $request->get('video_url'),
            'size' => [640, 480]
        ]);

        if (!$thumbnailData->status) {
            return $this->error($thumbnailData->message, $thumbnailData->statusCode);
        } else if (!$thumbnailData->body->url) {
            return $this->error('Đường dẫn không hợp lệ', 400);
        }

        return $this->success([
            'thumbnail' => $thumbnailData->body->url,
        ]);
    }

    /**
     * @param $status
     * @param $matchingPercentage
     * @return array
     */
    private function getProgressing($status, $matchingPercentage)
    {
        $detectingPercentage = 0;
        $renderingPercentage = 0;

        if ($status === 'done') {
            $detectingPercentage = 100;
            $matchingPercentage = 100;
            $renderingPercentage = 100;
        } else if ($status === 'grouping' || $status === 'grouped' || $status === 'rendering') {
            $detectingPercentage = 100;
            $matchingPercentage = 100;
        } else if ($status === 'detected') {
            $detectingPercentage = 100;
        }

        return [
            'detectingPercentage' => $detectingPercentage,
            'matchingPercentage' => $matchingPercentage,
            'renderingPercentage' => $renderingPercentage,
        ];
    }

    private function parseTime($timeFrom, $timeTo)
    {
        if ($timeFrom && $timeTo) {
            return Carbon::parse($timeFrom)->diff($timeTo)->format('%I:%S');
        }
        return '';
    }
}
