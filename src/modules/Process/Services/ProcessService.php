<?php

namespace Modules\Process\Services;

use Modules\Process\Events\ProgressChange;
use App\Traits\AnalysisTrait;
use App\Traits\HandleUploadFile;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\BaseException;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Camera\Services\CameraService;
use Modules\Process\Models\Process;
use Modules\Process\Repositories\ProcessRepository;

class ProcessService extends BaseService
{
    use AnalysisTrait, HandleUploadFile;

    /** @var \Modules\Camera\Services\CameraService $cameraService */
    protected $cameraService;

    /*** @var \Modules\Process\Services\ObjectService $objectService */
    protected $objectService;

    /**
     * ProcessService constructor.
     * @param \Modules\Process\Repositories\ProcessRepository $repository
     * @param \Modules\Camera\Services\CameraService $cameraService
     */
    public function __construct(ProcessRepository $repository, CameraService $cameraService)
    {
        $this->repository = $repository;
        $this->cameraService = $cameraService;
    }

    /**
     * Init service
     */
    public function setObjectService(): void
    {
        $this->objectService = app(ObjectService::class);
    }

    /**
     * @return array
     */
    public function getIndexPageData()
    {
        return [
            'items' => $this->repository->listBy(),
            'cameras' => $this->cameraService->listAll(),
        ];
    }

    /**
     * @param $id
     * @return array|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function getDetailPageData($id)
    {
        $item = $this->getProcessDetail($id);

        if ($item instanceof ResourceNotFoundException) {
            return $item;
        }
        $processData = $this->sendGETRequest($this->getAIUrl($item->mongo_id), [], $this->getDefaultHeaders());
        $item->config = $processData->status ? $processData->body->config : null;

        return array_merge([
            'item' => $item,
            'cameras' => $this->cameraService->listAll(),
        ], $this->getProgressing($item));
    }

    /**
     * @param array $data
     * @return array|bool|\Infrastructure\Exceptions\CustomException|int
     */
    public function createAndSync(array $data)
    {
        $cameraId = Arr::get($data, 'camera_id');
        $camera = $cameraId ? $this->cameraService->findById($cameraId) : null;
        $camera = !($camera instanceof ResourceNotFoundException) ? $camera : null;

        $startedAt = Arr::get($data, 'started_at')
            ? Carbon::createFromFormat('H:i d-m-Y', $data['started_at'])->format('Y/m/d H:i:s')
            : null;

        $response = $this->sendPOSTRequest($this->getAIUrl(), [
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

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        return $this->repository->create([
            'user_id' => Auth::id(),
            'camera_id' => $camera ? $camera->id : null,
            'name' => $data['name'],
            'thumbnail' => $data['thumbnail'],
            'video_url' => $camera ? null : $data['video_url'],
            'description' => $data['description'],
            'status' => Process::STATUS['ready'],
            'mongo_id' => $response->body->_id,
            'total_time' => $camera ? -1 : $response->body->total_time,
            'total_frames' => $camera ? -1 : $response->body->total_frames,
            'fps' => $response->body->fps,
        ]);
    }

    /**
     * @param $id
     * @return \Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException|int
     */
    public function startProcess($id)
    {
        $result = $this->callAIService($id, 'start', 'GET');

        if ($result instanceof BaseException) {
            return $result;
        }
        return $this->repository->updateBy(['id' => $id], [
            'detecting_start_time' => Carbon::now(),
            'status' => Process::STATUS['detecting'],
        ]);
    }

    /**
     * @param $id
     * @return \Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException|int
     */
    public function stopProcess($id)
    {
        $result = $this->callAIService($id, 'stop', 'GET');

        if ($result instanceof BaseException) {
            return $result;
        }
        $result = $this->repository->updateBy(['id' => $id], [
            'status' => Process::STATUS['stopped'],
            'done_time' => Carbon::now(),
            'detecting_end_time' => Carbon::now(),
        ]);
        broadcast(new ProgressChange($id, [
            'status' => Process::STATUS['stopped'],
        ]));

        return $result;
    }

    /**
     * @param $id
     * @param null $additionalPath
     * @param string $method
     * @return mixed
     */
    public function callAIService($id, $additionalPath = null, $method = 'GET')
    {
        $process = $this->repository->findById($id);

        if (!$process) {
            return new ResourceNotFoundException();
        }
        $path = $additionalPath ? "$process->mongo_id/$additionalPath" : $process->mongo_id;
        $response = $this->{"send{$method}Request"}(
            $this->getAIUrl($path), [], $this->getDefaultHeaders()
        );
        if (!$response->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $response->body->message
            ]);
        }
        return ['process' => $process, 'response' => $response];
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjects($id)
    {
        $this->setObjectService();

        return $this->objectService->getObjectsByProcess($id);
    }

    /**
     * @param $id
     * @return bool|\Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function delete($id)
    {
        $this->setObjectService();
        $result = $this->callAIService($id, null, 'DELETE');

        if ($result instanceof ResourceNotFoundException) {
            return $result;
        }
        $this->objectService->deleteBy(['process_id' => $id]);
        $this->repository->deleteBy(['id' => $id]);

        return true;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function getProcessDetail(int $id)
    {
        $item = $this->repository->getDetailWithStatistic($id, ['camera']);
        if (!$item) {
            return new ResourceNotFoundException();
        }
        if ($item->status === Process::STATUS['done'] || $item->status === Process::STATUS['stopped']) {
            $item->detecting_duration = $this->parseTime($item->detecting_start_time, $item->detecting_end_time);
            $item->total_duration = $this->parseTime($item->detecting_start_time, $item->done_time);
        }
        return $item;
    }

    /**
     * @param array $ids
     * @param string $searchType
     * @param \Illuminate\Http\UploadedFile|null $file
     * @param int|null $objectId
     * @return \Infrastructure\Exceptions\ResourceNotFoundException|mixed
     */
    public function searchFace(array $ids, string $searchType, $file = null, $objectId = null)
    {
        $this->setObjectService();

        $processes = $this->repository->listBy(function ($query) use ($ids) {
            return $query->whereIn('id', $ids);
        }, false);
        $payload = [
            'process_ids' => $processes->pluck('mongo_id')->all(),
            'type_search' => $searchType,
            'threshold' => $searchType === 'face' ? 1.05 : 0.7
        ];

        if ($file) {
            $payload['image_url'] = $this->uploadFile($file);
        } else {
            $object = $this->objectService->findById($objectId);

            if (!$object) {
                return new ResourceNotFoundException();
            }
            $payload['object_id'] = $object->mongo_id;
        }
        $response = $this->sendPOSTRequest(
            $this->getAIUrl('faces/searching'), $payload, $this->getDefaultHeaders()
        );
        if (!$response->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $response->body->message
            ]);
        }

        return $this->objectService->getObjectsAfterSearchFace($response->body, $processes->count() > 0);
    }

    /**
     * @param $timeFrom
     * @param $timeTo
     * @return string
     */
    private function parseTime($timeFrom, $timeTo)
    {
        return ($timeFrom && $timeTo) ? Carbon::parse($timeFrom)->diff($timeTo)->format('%I:%S') : '';
    }

    /**
     * @param $process
     * @return array
     */
    private function getProgressing($process)
    {
        $detectingPercentage = ($process->status === 'done') ? 100 : 0;
        $renderingPercentage = $process->video_result ? 100 : 0;

        return [
            'detectingPercentage' => $detectingPercentage,
            'renderingPercentage' => $renderingPercentage
        ];
    }

    /**
     * @param string|null $mongoId
     * @return string
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return config('app.ai_server') . '/processes' . ($mongoId ? "/$mongoId" : '');
    }
}
