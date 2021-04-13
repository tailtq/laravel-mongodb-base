<?php

namespace Modules\Process\Transformers;

use League\Fractal\TransformerAbstract;
use MongoDB\BSON\ObjectId;

class ObjectTransformer extends TransformerAbstract
{
    public function transform($object)
    {
        if (!empty($object->identity)) {
            $object->identity->_id = (string) $object->identity->_id;
        }
        $object->appearances = array_map(function ($e) {
            return $this->getFormat($e);
        }, $object->appearances);

        return $this->getFormat($object);
    }

    /**
     * @param $object
     * @return array
     */
    private function getFormat($object): array
    {
        return [
            '_id' => (string) $object->_id,
            'identity' => $object->identity instanceof ObjectId ? (string) $object->identity : $object->identity,
            'process' => (string) $object->process,
            'track_id' => $object->track_id,
            'body_ids' => $object->body_ids ?? [],
            'face_ids' => $object->face_ids ?? [],
            'avatars' => $object->avatars ?? [],
            'confidence_rate' => $object->confidence_rate,
            'from_frame' => $object->from_frame,
            'from_time' => !empty($object->from_time) ? $object->from_time->toDateTime()->format('Y-m-d H:i:s') : null,
            'to_frame' => $object->to_frame,
            'to_time' => !empty($object->to_time) ? $object->to_time->toDateTime()->format('Y-m-d H:i:s') : null,
            'cluster_elements' => $object->cluster_elements ?? [],
            'appearances' => $object->appearances ?? [],
            'created_at' => !empty($object->created_at) ? $object->created_at->toDateTime()->format('Y-m-d H:i:s') : null,
        ];
    }
}
