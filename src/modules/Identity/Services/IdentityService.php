<?php

namespace Modules\Identity\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\BaseException;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Identity\Repositories\IdentityRepository;

class IdentityService extends BaseService
{
    /**
     * IdentityService constructor.
     * @param IdentityRepository $repository
     */
    public function __construct(IdentityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     * @return array|bool|CustomException|\stdClass
     */
    public function createAndSync(array $data)
    {
        // Proceed AI request
        $response = $this->sendPOSTRequest($this->getAIUrl(), [
            'name'        => $data['name'],
            'status'      => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'info'        => Arr::get($data, 'info'),
            'images'      => Arr::pluck($data['images'], 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        return $response->body;
    }

    /**
     * @param array $data
     * @param string $id
     * @return CustomException|ResourceNotFoundException|\stdClass
     */
    public function update(array $data, string $id)
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return new ResourceNotFoundException();
        }
        $newImages = Arr::where($data['images'], function ($image) {
            return empty($image['exist']);
        });
        // Proceed AI request
        $response = $this->sendPUTRequest($this->getAIUrl($item->id), [
            'name'        => $data['name'],
            'status'      => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'info'        => Arr::get($data, 'info'),
            'images'      => Arr::pluck($newImages, 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        $this->sendGETRequest($this->getAIUrl(), $this->getDefaultHeaders());

        return $response->body;
    }

    /**
     * @param $id
     * @return RedirectResponse|BaseException|CustomException|ResourceNotFoundException
     */
    public function delete($id)
    {
        $item = $this->repository->findById($id);

        if (!$item) {
            return new ResourceNotFoundException();
        }

        // Proceed AI request
        $response = $this->sendDELETERequest($this->getAIUrl($item->id), [], $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        $this->sendGETRequest($this->getAIUrl(), $this->getDefaultHeaders());

        return $response->body;
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
