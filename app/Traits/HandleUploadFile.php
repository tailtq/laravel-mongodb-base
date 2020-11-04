<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HandleUploadFile
{
    /**
     * Upload file
     * @param $file
     * @param $name
     * @return mixed
     */
    public function uploadFile($file, $name)
    {
        $disk = Storage::disk('minio');

        $filePath = config('constants.MINIO_FOLDER') . '/' . $name;
        $disk->putFileAs(config('constants.MINIO_FOLDER'), $file, $name);
        $disk->setVisibility($filePath, 'public');
        return $disk->url($filePath);
    }
}