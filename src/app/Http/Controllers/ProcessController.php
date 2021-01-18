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
use Illuminate\Support\Facades\DB;

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
            DB::raw("(SELECT COUNT(*) FROM objects WHERE objects.process_id = $id) as total_appearances"),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE OO.id in (
                    SELECT min(objects.id) as unq_identity_id
                        FROM objects
                        WHERE objects.process_id = $id
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_objects
            "),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $id AND clusters.identity_id != NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_identified
            "),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $id AND clusters.identity_id = NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_unidentified
            ")
        )->where('id', $id)->first();

        if (!$process) {
            abort(404);
        }
        if ($process->status === Process::STATUS['done']) {
            $process->detecting_duration = $this->parseTime($process->detecting_start_time, $process->detecting_end_time);
        }
        $processData = $this->sendGETRequest(
            config('app.ai_server') . "/processes/$process->mongo_id", [], $this->getDefaultHeaders()
        );
        $process->config = $processData->status ? $processData->body->config : null;
        $cameras = Camera::select(['id', 'name', 'url'])->orderBy('created_at', 'desc')->get();

        return view('pages.processes.detail', [
            'process' => $process,
            'cameras' => $cameras,
            'detectingPercentage' => $this->getProgressing($process->status),
        ]);
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
        $startedAt = Arr::get($data, 'started_at')
            ? Carbon::createFromFormat('H:i d-m-Y', $data['started_at'])->format('Y/m/d H:i:s')
            : null;

        $processData = $this->sendPOSTRequest(config('app.ai_server') . '/processes', [
            'camera' => $camera ? $camera->mongo_id : null,
            'name' => $data['name'],
            'url' => $camera ? null : $data['video_url'],
            'status' => Process::STATUS['ready'],
            'started_at' => $startedAt,
            'detection_scale' => (float)$data['detection_scale'],
            'frame_drop' => (int)$data['frame_drop'],
            'frame_step' => (int)$data['frame_step'],
            'max_pitch' => (int)$data['max_pitch'],
            'max_roll' => (int)$data['max_roll'],
            'max_yaw' => (int)$data['max_yaw'],
            'min_face_size' => (int)$data['min_face_size'],
            'tracking_scale' => (float)$data['tracking_scale'],
            'biometric_threshold' => (float)$data['biometric_threshold'],
            'min_head_confidence' => (int)$data['min_head_confidence'],
            'min_face_confidence' => (int)$data['min_face_confidence'],
            'min_body_confidence' => (int)$data['min_body_confidence'],
            'write_video_step' => (int)$data['write_video_step'],
            'write_data_step' => (int)$data['write_data_step'],
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
        $objects = DB::table('objects')
            ->leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->where('objects.process_id', $processId)
            ->select([
                'objects.*',
                'identities.name as identity_name',
                'identities.images as identity_images',
            ])
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
     * @return int
     */
    private function getProgressing($status)
    {
        $detectingPercentage = ($status === 'done') ? 100 : 0;

        return $detectingPercentage;
    }

    private function parseTime($timeFrom, $timeTo)
    {
        if ($timeFrom && $timeTo) {
            return Carbon::parse($timeFrom)->diff($timeTo)->format('%I:%S');
        }
        return '';
    }
}
