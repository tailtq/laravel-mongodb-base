<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\BaseException;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Identity\Repositories\IdentityRepository;

class IdentityService extends BaseService
{
    /**
     * IdentityService constructor.
     */
    public function __construct()
    {
        $this->repository = app(IdentityRepository::class);
    }

    /**
     * @param array $data
     * @return \Infrastructure\Exceptions\CustomException
     */
    public function create(array $data)
    {
        // Proceed AI request
        $response = $this->sendPOSTRequest($this->getAIUrl(), [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'images' => Arr::pluck($data['images'], 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        $data = array_merge($data, [
            'mongo_id' => $response->body->_id,
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'images' => array_map(function ($index, $element) use ($response) {
                return [
                    'url' => $element['url'],
                    'mongo_id' => $response->body->facial_data[$index]->face_id
                ];
            }, array_keys($data['images']), $data['images']),
        ]);

        return $this->repository->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @return \Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function update(array $data, int $id)
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return new ResourceNotFoundException();
        }
        $oldImages = $item->images;
        $newImages = Arr::where($data['images'], function ($image) {
            return empty($image['mongo_id']);
        });
        // Proceed AI request
        $response = $this->sendPUTRequest($this->getAIUrl($item->mongo_id), [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'images' => Arr::pluck($newImages, 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        foreach ($newImages as $index => $image) {
            array_push($oldImages, [
                'mongo_id' => $response->body->facial_data[$index]['face_id'],
                'url' => $image['url']
            ]);
        }

        return $this->repository->update(['id' => $id], [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'info' => $data['info'],
            'card_number' => $data['card_number'],
            'images' => $oldImages,
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Infrastructure\Exceptions\BaseException|\Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function delete($id)
    {
        $result = parent::delete($id);
        $location = config('constants.minio_folder') . '/' . $id;

        if ($result instanceof BaseException) {
            return $result;
        }
        if (Storage::disk('minio')->exists($location)) {
            Storage::disk('minio')->deleteDir($location);
        }
        return $result;
    }

    /**
     * @param string|null $mongoId
     * @return string
     */
    protected function getAIUrl(string $mongoId = null)
    {
        return config('app.ai_server') . '/identities' . ($mongoId ? "/$mongoId" : '');
    }
}
