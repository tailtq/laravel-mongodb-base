<?php

namespace App\Traits;

use App\Helpers\DatabaseHelper;
use App\Models\Identity;
use App\Models\ObjectAppearance;
use App\Models\Process;
use App\Models\TrackedObject;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait GroupDataTrait
{
    use RequestAPI;

    public function callGroupingData($processes)
    {
        Log::info(json_encode($processes));

        foreach ($processes as $process) {
            $process = DB::table('processes')
                ->where('id', $process->id)
                ->select(['id', 'status', 'mongo_id', 'ungrouped_count'])
                ->addSelect(DB::raw("(SELECT COUNT(*) FROM objects WHERE process_id = $process->id and matching_status = '" . TrackedObject::MATCHING_STATUS['identified'] . "') as identified_count"))
                ->first();
            if ($process->status != Process::STATUS['detected']) {
                continue;
            }
            Log::info("Detected with ungrouped count: $process->ungrouped_count, identified count: $process->identified_count");

            if ($process->ungrouped_count == $process->identified_count) {
                $this->groupData($process);
            }
        }
    }

    // group data handler
    public function groupData($process)
    {
        $url = config('app.ai_server') . "/processes/$process->mongo_id/grouping/unidentified";
        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());
        Log::info("Grouping unidentified $process->id response " . json_encode($response));

        if ($response->status) {
            $data = json_decode(json_encode($response->body), true);
            $this->syncObjects($data);
        }

        $url = config('app.ai_server') . "/processes/$process->mongo_id/grouping2";
        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());
        Log::info("Grouping $process->id response " . json_encode($response));

        if ($response->status) {
            $data = json_decode(json_encode($response->body), true);
            $this->syncObjects($data);
        }
    }

    /**
     * AI API combines data using identity_id and also other algorithms then map to app's database
     * @param $data
     */
    public function syncObjects($data)
    {
        // Response data AI
        $mongoIds = Arr::collapse(Arr::pluck($data, 'appearances.*.mongo_id'));
        $objects = TrackedObject::whereIn('mongo_id', $mongoIds)->select(['id', 'mongo_id'])->get();

        $identityMongoIds = array_filter(Arr::pluck($data, 'identity'), function ($element) {
            return $element != null;
        });
        $identities = Identity::whereIn('mongo_id', $identityMongoIds)->select(['id', 'mongo_id'])->get();
        $deleteIds = [];
        $identityData = [];

        foreach ($data as $element) {
            $appearanceMongoIds = Arr::pluck($element['appearances'], 'mongo_id');
            $object = $objects->where('mongo_id', $element['mongo_id'])->first();
            $identity = !empty($element['identity']) ? $identities->where('mongo_id', $element['identity'])->first() : null;

            if ($object) {
                $appearanceIds = $objects->whereIn('mongo_id', $appearanceMongoIds)
                    ->where('mongo_id', '!=', $object->mongo_id)
                    ->pluck('id')
                    ->all();
                ObjectAppearance::whereIn('object_id', $appearanceIds)->update(['object_id' => $object->id]);

                $identityData[] = [
                    'id' => $object->id,
                    'identity_id' => $identity->id ?? null
                ];
                $deleteIds = array_merge($deleteIds, $appearanceIds);
            }
        }
        TrackedObject::whereIn('id', $deleteIds)->delete();
        DatabaseHelper::updateMultiple($identityData, 'id', 'objects');
    }
}
