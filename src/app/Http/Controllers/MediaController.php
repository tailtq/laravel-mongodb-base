<?php

namespace App\Http\Controllers;

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
            array_push($urls, $this->uploadFile($file));
        }

        return $this->success($urls);
    }
}
