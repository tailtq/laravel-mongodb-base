<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\CustomException;
use Modules\User\Repositories\UserRepository;

class UserService extends BaseService
{
    /**
     * UserService constructor.
     * @param \Modules\User\Repositories\UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     * @return \Illuminate\Http\RedirectResponse|\Infrastructure\Exceptions\CustomException
     */
    public function create(array $data, $id = false)
    {
        $response = $this->sendPOSTRequest($this->getAIUrl('register'), $data, [
            'X-API-KEY' => config('app.ai_api_key')
        ]);
        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        $data['mongo_id'] = $response->body->_id;
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    /**
     * @param string|null $additionalPath
     * @return string
     */
    protected function getAIUrl(string $additionalPath = null)
    {
        return config('app.ai_server') . '/users' . ($additionalPath ? "/$additionalPath" : '');
    }
}
