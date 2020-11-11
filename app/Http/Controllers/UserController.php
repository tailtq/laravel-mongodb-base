<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Traits\RequestAPI;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

class UserController extends Controller
{
    use RequestAPI;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);

        return view('pages.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * @param \App\Http\Requests\UserCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserCreateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password']);

        $response = $this->sendPOSTRequest(config('app.ai_server') . '/users/register', $data, [
            'X-API-KEY' => config('app.ai_api_key')
        ]);
        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('email', $response->body->message);

            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }
        $data['mongo_id'] = $response->body->data->_id;
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        User::where('id', $id)->delete();

        return redirect()->route('users');
    }
}
