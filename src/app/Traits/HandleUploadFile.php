<?php

namespace App\Traits;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Storage;

trait HandleUploadFile
{
    /**
     * Upload file
     * @param $file
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

    /**
     * @param $path
     * @return array
     */
    public function listFiles($path)
    {
        $disk = Storage::disk('minio');
        $files = $disk->files($path);

        foreach ($files as $index => $file) {
            $files[$index] = $disk->url($file);
        }

        return $files;
    }
}
