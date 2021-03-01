<?php

namespace Modules\Process\Services;

use App\Events\ProgressChange;
use App\Traits\AnalysisTrait;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Camera\Services\CameraService;
use Modules\Process\Models\Process;
use Modules\Process\Repositories\ProcessRepository;

class ProcessService extends BaseService
{
    use AnalysisTrait;

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
        $item = $this->repository->findById($id, array_merge(['*'], $this->getStatistic($id)), ['camera']);

        if (!$item) {
            return new ResourceNotFoundException();
        }
        if ($item->status === Process::STATUS['done'] || $item->status === Process::STATUS['stopped']) {
            $item->detecting_duration = $this->parseTime($item->detecting_start_time, $item->detecting_end_time);
            $item->total_duration = $this->parseTime($item->detecting_start_time, $item->done_time);
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
     * @return \Infrastructure\Exceptions\CustomException
     */
    public function create($data)
    {
        $cameraId = Arr::get($data, 'camera_id');
        $camera = $cameraId ? $this->cameraService->findById($cameraId) : null;

        $startedAt = Arr::get($data, 'started_at')
            ? Carbon::createFromFormat('H:i d-m-Y', $data['started_at'])->format('Y/m/d H:i:s')
            : null;

        $response = $this->sendPOSTRequest(config('app.ai_server') . '/processes', [
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
        $process = $this->repository->findById($id);

        if (!$process) {
            return new ResourceNotFoundException();
        }
        $processData = $this->sendGETRequest(
            $this->getAIUrl("$process->mongo_id/start"), [], $this->getDefaultHeaders()
        );
        if (!$processData->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $processData->body->message
            ]);
        }
        return DB::table('processes')->where('id', $id)->update([
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
        $process = $this->repository->findById($id);

        if (!$process) {
            return new ResourceNotFoundException();
        }
        $processData = $this->sendGETRequest(
            $this->getAIUrl("$process->mongo_id/stop"), [], $this->getDefaultHeaders()
        );
        if (!$processData->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $processData->body->message
            ]);
        }
        $result = DB::table('processes')->where('id', $id)->update([
            'status' => Process::STATUS['stopped'],
            'done_time' => Carbon::now(),
            'detecting_end_time' => Carbon::now(),
        ]);
        broadcast(new ProgressChange($process->id, [
            'status' => Process::STATUS['stopped'],
        ]));

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function renderVideo($id)
    {
        $process = $this->repository->findById($id);

        if (!$process) {
            return new ResourceNotFoundException();
        }
        $response = $this->sendGETRequest(
            $this->getAIUrl("$process->mongo_id/rendering"), [], $this->getDefaultHeaders()
        );
        if (!$response->status) {
            return new CustomException('AI FAILED', 500, (object)[
                'message' => $response->body->message
            ]);
        }
        return true;
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjects($id)
    {
        return $this->objectService->getObjectsByProcess($id);
    }

    /**
     * @param $id
     * @return bool|\Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function delete($id)
    {
        $this->setObjectService();

        $process = $this->repository->findById($id);
        if (!$process) {
            return new ResourceNotFoundException();
        }
        // ignore AI's response
        $this->sendDELETERequest(
            $this->getAIUrl($process->mongo_id), [], $this->getDefaultHeaders()
        );
        $this->objectService->deleteBy(['process_id' => $id]);
        $this->repository->deleteBy(['id' => $id]);

        return true;
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
