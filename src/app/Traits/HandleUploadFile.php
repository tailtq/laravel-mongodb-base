<?php

namespace App\Traits;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Storage;

trait HandleUploadFile
{
    /**
     * Upload file
     * @param $file
     * @param $name
     * @param $id
     * @return mixed
     */
    public function uploadFile($file, $id = null)
    {
        $disk = Storage::disk('minio');

        $name = CommonHelper::generateFileName($file);
        $filePath = config('constants.minio_folder') . '/' . ($id ? "$id/" : '') . $name;
        $disk->putFileAs(config('constants.minio_folder') . '/' . $id, $file, $name);
        $disk->setVisibility($filePath, 'public');

        return $disk->url($filePath);
    }
}
