<?php

namespace App\Http\Controllers;

use App\Events\ProgressChange;
use App\Helpers\DatabaseHelper;
use App\Http\Requests\ProcessCreateRequest;
use App\Models\Process;
use App\Models\TrackedObject;
use App\Traits\AnalysisTrait;
use App\Traits\RequestAPI;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Camera\Models\Camera;

class ProcessController extends Controller
{
    use RequestAPI, AnalysisTrait;

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $process = Process::findOrFail($id);
        $url = config('app.ai_server') . "/processes/$process->mongo_id";
        $response = $this->sendDELETERequest($url, [], $this->getDefaultHeaders());
        Log::info('Delete process response: ' . json_encode($response));

//        if (!$response->status) {
//            abort(400, $response->message);
//        }
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
        $ids = json_decode($request->get('process_ids', '[]'));
        $searchType = $request->get('search_type');
        $processes = Process::whereIn('id', $ids)->get();

        $url = config('app.ai_server') . "/processes/faces/searching";
        $payload = [
            'process_ids' => $processes->pluck('mongo_id')->all(),
            'type_search' => $searchType,
            'threshold' => $searchType === 'face' ? 1.05 : 0.7
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['image_url'] = $this->uploadFile($file);
        } else {
            $objectId = $request->get('object_id');
            $payload['object_id'] = DB::table('objects')->where('id', $objectId)->first()->mongo_id;
        }
        $response = $this->sendPOSTRequest($url, $payload, $this->getDefaultHeaders());
        $searchedObjects = $response->body;
        $objectMongoIds = Arr::pluck($searchedObjects, 'object_id');

        // handle error cases
        // laravel receive image --> save to min_io + search

        $objects = DB::table('objects')
            ->join('processes', 'objects.process_id', 'processes.id')
            ->leftJoin('clusters', 'objects.cluster_id', 'clusters.id')
            ->leftJoin('identities as CI', 'clusters.identity_id', 'CI.id')
            ->leftJoin('identities as OI', 'objects.identity_id', 'OI.id')
            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
                $query->select(DB::raw('MIN(O.id)'))
                    ->from('objects AS O')
                    ->whereIn('O.mongo_id', $objectMongoIds)
                    ->groupBy(DB::raw('IFNULL(O.cluster_id, UUID())'));
            })
            ->select([
                'objects.*',
                'OI.id as identity_id',
                'OI.name as identity_name',
                'OI.images as identity_images',
                'CI.id as cluster_identity_id',
                'CI.name as cluster_identity_name',
                'CI.images as cluster_identity_images',
                'processes.name as process_name',
            ])
            ->get();
        $objects = DatabaseHelper::blendObjectsIdentity($objects);
        $objects = $this->getAppearances($objects, $processes->count() > 0);

        foreach ($objects as &$object) {
            $object->confidence_rate = null;
            foreach ($searchedObjects as $searchedObject) {
                if ($searchedObject->object_id == $object->mongo_id) {
                    $object->distance = $searchedObject->search_distance;
                }
            }
        }
        $objects = array_values($objects->sortBy('distance')->toArray());

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
        if ($process->status === Process::STATUS['done'] || $process->status === Process::STATUS['stopped']) {
            $process->detecting_duration = $this->parseTime($process->detecting_start_time, $process->detecting_end_time);
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

    private function parseTime($timeFrom, $timeTo)
    {
        if ($timeFrom && $timeTo) {
            return Carbon::parse($timeFrom)->diff($timeTo)->format('%I:%S');
        }
        return '';
    }
}
