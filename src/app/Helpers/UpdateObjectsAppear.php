<?php

namespace App\Helpers;
use App\Models\TrackedObject;
use Illuminate\Support\Arr;

class UpdateObjectsAppear
{
    /**
     * @param $data
     */
    public static function updateObject($data)
    {
        //Response data AI
        $data = [
            [
                "mongo_id"    => "5fb1e9b739311f1812035085",
                "identity"    => "1",
                "appearances" => [
                    [
                        "mongo_id"   => "5fb1e9b739311f1812035085",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ],
                    [
                        "mongo_id"   => "123",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ],
                    [
                        "mongo_id"   => "1233",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ]
                ]
            ],
            [
                "mongo_id"    => "5fb1e9b739311f1812035085",
                "identity"    => "1",
                "appearances" => [
                    [
                        "mongo_id"   => "5fb1e9b739311f1812035085",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ],
                    [
                        "mongo_id"   => "5fb1e9b739311f1812035085",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ],
                    [
                        "mongo_id"   => "5fb1e9b739311f1812035085",
                        "track_id"   => '123',
                        "frame_from" => '590',
                        "frame_to"   => '733'
                    ]
                ]
            ]
        ];

        if (!empty($data)) {
            foreach ($data as &$value){
                $value['appearances'] = Arr::pluck($value['appearances'], 'mongo_id');

                $objectUpdate = TrackedObject::where('mongo_id', $value['mongo_id'])->first();

                foreach ($value['appearances'] as $key => $item){
                    if ($value['mongo_id'] !== $item) {
                        $object = TrackedObject::where('mongo_id', $item)->first();
                        if ($object) {
                            $object->appearances()->update(['object_id' => $objectUpdate->id]);
                        }
                        $object->delete();
                    }
                }
            }
        }

        return;
    }
}
