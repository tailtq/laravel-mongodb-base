<?php

namespace App\Helpers;
use App\Models\Identity;
use App\Models\ObjectAppearance;
use App\Models\TrackedObject;
use Illuminate\Support\Arr;

class UpdateObjectsAppear
{
    /**
     * @param $data
     */
    public static function updateObject($data)
    {
        // Response data AI
        $mongoIds = Arr::collapse(Arr::pluck($data, 'appearances.*.object_id'));
        $objects = TrackedObject::whereIn('mongo_id', $mongoIds)->select(['id', 'mongo_id'])->get();

        $identityMongoIds = array_filter(Arr::pluck($data, 'identity'), function ($element) {
            return $element != null;
        });
        $identities = Identity::whereIn('mongo_id', $identityMongoIds)->select(['id', 'mongo_id'])->get();
        $deleteIds = [];
        $identityData = [];

        foreach ($data as $element) {
            $appearanceMongoIds = Arr::pluck((array) $element['appearances'], 'object_id');
            $object = $objects->where('mongo_id', $element['object_id'])->first();
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
