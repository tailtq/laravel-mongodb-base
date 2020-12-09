<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\TrackedObject;
use App\Traits\RequestAPI;
use App\Traits\ResponseTrait;

class TrackedObjectController  extends Controller
{
    use ResponseTrait, RequestAPI;

    /**
     */
    public function startRendering($id)
    {
        $object = TrackedObject::with('process')->find($id);

        if (!$object) {
            return $this->error('Đối tượng không hợp lệ', 404);
        }
        $url = config('app.ai_server') . "/processes/faces/rendering";
        $response = $this->sendPOSTRequest($url, [
            'object_id' => $object->mongo_id
        ], $this->getDefaultHeaders());

        if ($response->status) {
            return $this->success();
        } else {
            return $this->error($response->message, 400);
        }
    }
}
