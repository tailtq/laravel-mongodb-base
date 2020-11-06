<?php
namespace App\Traits;

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
    public function uploadFile($file, $name, $id = null)
    {
        $disk = Storage::disk('minio');

        $filePath = config('constants.minio_folder') . '/' . $id .'/'. $name;

        $disk->putFileAs(config('constants.minio_folder') . '/' . $id, $file, $name);
        $disk->setVisibility($filePath, 'public');
        return $disk->url($filePath);
    }
}
