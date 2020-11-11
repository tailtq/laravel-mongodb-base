<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Http\Requests\MediaCreateRequest;
use Illuminate\Http\UploadedFile;

class MediaController extends Controller
{
    public function create(MediaCreateRequest $request)
    {
        $urls = [];
        $files = $request->file('files');

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        foreach ($files as $file) {
            $filename = CommonHelper::generateFileName($file);
            array_push($urls, $this->uploadFile($file, $filename));
        }

        return $this->success($urls);
    }
}
