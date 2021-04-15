<?php

namespace Modules\Media\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Infrastructure\BaseController;
use Infrastructure\Exceptions\BadRequestException;
use Infrastructure\Exceptions\CustomException;
use Modules\Media\Requests\CreateMediaRequest;
use Modules\Media\Services\MediaService;

class MediaController extends BaseController
{
    /**
     * MediaController constructor.
     * @param MediaService $service
     */
    public function __construct(MediaService $service)
    {
        parent::__construct('Media', 'medias');
        $this->service = $service;
    }

    /**
     * @param CreateMediaRequest $request
     * @return JsonResponse
     */
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

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function createThumbnail(Request $request)
    {
        $result = $this->service->createThumbnail($request->get('url'));

        if ($result instanceof CustomException) {
            return $this->returnFailedResult($result, $request);
        } else if ($result instanceof BadRequestException) {
            return $this->error($result->getMessage(), $result->getCode());
        }
        return $this->success(['thumbnail' => $result]);
    }
}
