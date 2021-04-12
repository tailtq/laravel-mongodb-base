<?php

namespace Modules\Process\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Modules\Process\Events\ProgressChange;
use App\Traits\HandleUploadFile;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\BaseException;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Camera\Services\CameraService;
use Modules\Process\Models\Process;
use Modules\Process\Repositories\ProcessRepository;
use MongoDB\BSON\ObjectID;

class ProcessService extends BaseService
{
    use HandleUploadFile;

    /** @var CameraService $cameraService */
    protected $cameraService;

    /*** @var ObjectService $objectService */
    protected $objectService;

    /**
     * ProcessService constructor.
     * @param ProcessRepository $repository
     * @param CameraService $cameraService
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
     * @return array|ResourceNotFoundException
     */
    public function getDetailPageData($id)
    {
        $item = $this->repository->findById($id);

        if ($item instanceof ResourceNotFoundException) {
            return $item;
        }
        $this->setObjectService();
        $item->statistic = $this->objectService->getStatisticByProcesses([new ObjectId($id)])[0];

        return array_merge([
            'item' => $item,
            'cameras' => $this->cameraService->listAll(),
        ], $this->getProgressing($item));
    }

    /**
     * @param array $data
     * @return array|bool|CustomException|int
     */
    public function createAndSync(array $data)
    {
        $cameraId = Arr::get($data, 'camera');
        $camera = $cameraId ? $this->cameraService->findById($cameraId) : null;
        $camera = !($camera instanceof ResourceNotFoundException) ? $camera : null;

        $startedAt = Arr::get($data, 'started_at')
            ? Carbon::createFromFormat('H:i d-m-Y', $data['started_at'])->format('Y/m/d H:i:s')
            : null;

        $response = $this->sendPOSTRequest($this->getAIUrl(), [
            'camera' => $camera ? $camera->idString : null,
            'name' => $data['name'],
            'url' => $camera ? null : $data['url'],
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
            'thumbnail' => $data['thumbnail'],
            'description' => $data['description'],
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }

        return $response->body->_id;
    }

    /**
     * @param $id
     * @return CustomException|ResourceNotFoundException|int
     */
    public function startProcess($id)
    {
        $result = $this->callAIService($id, 'start');

        if ($result instanceof BaseException) {
            return $result;
        }

        return $this->repository->updateBy(['_id' => new ObjectID($id)], [
            'detecting_start_time' => Carbon::now(),
            'status' => Process::STATUS['detecting'],
        ]);
    }

    /**
     * @param $id
     * @return CustomException|ResourceNotFoundException|int
     */
    public function stopProcess($id)
    {
        $result = $this->callAIService($id, 'stop');

        if ($result instanceof BaseException && $result->getData()->message != 'process_not_found' && $result->getData()->message != 'Thread is not active') {
            return $result;
        }
        $result = $this->repository->updateBy(['_id' => new ObjectID($id)], [
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
        $path = $additionalPath ? "$process->id/$additionalPath" : $process->id;
        $response = $this->{"send{$method}Request"}(
            $this->getAIUrl($path), [], $this->getDefaultHeaders()
        );
        if (!$response->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $response->message
            ]);
        }
        return ['process' => $process, 'response' => $response];
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function getObjects($id)
    {
        $this->setObjectService();

        return $this->objectService->getObjectsByProcess(new ObjectID($id));
    }

    /**
     * @param $id
     * @return bool|CustomException|ResourceNotFoundException
     */
    public function delete($id)
    {
        $result = $this->callAIService($id, null, 'DELETE');

        if ($result instanceof ResourceNotFoundException) {
            return $result;
        }

        return true;
    }

    /**
     * @param $id
     * @return Model|ResourceNotFoundException
     */
    public function getProcessDetail(string $id)
    {
        $item = $this->repository->findById($id, ['*'], ['cameraRelation']);

        if (!$item) {
            return new ResourceNotFoundException();
        }
        if ($item->status === Process::STATUS['done'] || $item->status === Process::STATUS['stopped']) {
            $item->detecting_duration = $this->parseTime($item->detecting_start_time, $item->detecting_end_time);
            $item->total_duration = $this->parseTime($item->detecting_start_time, $item->done_time);
        }
        $this->setObjectService();
        $item->statistic = $this->objectService->getStatisticByProcesses([new ObjectId($id)])[0];

        return $item;
    }

    /**
     * @param array $ids
     * @param string $searchType
     * @param UploadedFile|null $file
     * @param int|null $objectId
     * @return ResourceNotFoundException|mixed
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

            if ($object instanceof ResourceNotFoundException) {
                return $object;
            }
            $payload['object_id'] = (string)$object->id;
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

    public function getDetectingProcess(array $ignoredIds = [])
    {
        $this->setObjectService();

        $processes = $this->repository->listBy(function ($query) use ($ignoredIds) {
            return $query->where('status', Process::STATUS['detecting'])
                         ->whereNotIn('id', $ignoredIds);
        }, false);
        $ids = $processes->pluck('_id')->toArray();
        $statistics = $this->objectService->getStatisticByProcesses($ids);

        foreach ($processes as $process) {
            foreach ($statistics as $statistic) {
                if ($statistic['_id'] == $process->idString)
                $process->statistic = $statistic;
            }
        }

        return $processes;
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
