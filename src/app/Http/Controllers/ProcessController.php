<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCreateRequest;
use App\Models\Process;
use App\Traits\RequestAPI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProcessController extends Controller
{
    use RequestAPI;

    public function index()
    {
        $processes = Process::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.processes.index', [
            'processes' => $processes,
        ]);
    }

    public function show($id)
    {
        $process = Process::where('id', $id)->first();
        if (!$process) {
            abort(404);
        }
        $processData = $this->sendGETRequest(
            config('app.ai_server') . "/processes/$process->mongo_id", [], $this->getDefaultHeaders()
        );
        $process->mongoData = $processData->status ? $processData->body : null;

        return view('pages.processes.detail', [
            'process' => $process,
        ]);
    }

    public function store(ProcessCreateRequest $request)
    {
        $data = $request->validationData();

        $thumbnailData = $this->sendPOSTRequest(config('app.ai_server') . '/medias/thumbnails', [
            'url' => $data['video_url'],
            'size' => [640, 480]
        ]);
        if (!$thumbnailData->status) {
            return $this->error($thumbnailData->message, $thumbnailData->statusCode);
        } else if (!$thumbnailData->body->url) {
            return $this->error('Đường dẫn không hợp lệ', 400);
        }

        $processData = $this->sendPOSTRequest(config('app.ai_server') . '/processes', [
            'name' => $data['name'],
            'url' => $data['video_url'],
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
        ], $this->getDefaultHeaders());

        if (!$processData->status) {
            return $this->error($processData->message, $processData->statusCode);
        }

        $process = Process::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'thumbnail' => $thumbnailData->body->url,
            'video_url' => $data['video_url'],
            'description' => $data['description'],
            'status' => Process::STATUS['ready'],
            'mongo_id' => $processData->body->_id,
            'total_time' => $processData->body->total_time,
            'total_frames' => $processData->body->total_frames,
            'fps' => $processData->body->fps,
        ]);

        return $this->success($process);
    }
}
