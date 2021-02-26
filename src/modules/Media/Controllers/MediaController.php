<?php

namespace Modules\Media\Controllers;

use Illuminate\Http\UploadedFile;
use Infrastructure\BaseController;
use Modules\Media\Requests\CreateMediaRequest;
use Modules\Media\Services\MediaService;

class MediaController extends BaseController
{
    /**
     * MediaController constructor.
     * @param \Modules\Media\Services\MediaService $service
     */
    public function __construct(MediaService $service)
    {
        parent::__construct('Media', 'medias');
        $this->service = $service;
    }

    public function storeNew(CreateMediaRequest $request)
    {
        $urls = [];
        $files = $request->file('files');

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        foreach ($files as $file) {
            array_push($urls, $this->service->uploadFile($file));
        }

        return $this->success($urls);
    }
}
