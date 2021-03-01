<?php

namespace Modules\Media\Services;

use App\Traits\HandleUploadFile;
use App\Traits\RequestAPI;
use Infrastructure\Exceptions\BadRequestException;
use Infrastructure\Exceptions\CustomException;

class MediaService
{
    use HandleUploadFile, RequestAPI;

    /**
     * @param $videoUrl
     * @return \Infrastructure\Exceptions\BadRequestException|\Infrastructure\Exceptions\CustomException
     */
    public function createThumbnail($videoUrl)
    {
        $response = $this->sendPOSTRequest(config('app.ai_server') . '/medias/thumbnails', [
            'url' => $videoUrl,
            'size' => [640, 480]
        ]);

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        } else if (!$response->body->url) {
            return new BadRequestException('Đường dẫn không hợp lệ');
        }
        return $response->body->url;
    }
}
