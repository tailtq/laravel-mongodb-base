<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Http\Requests\IdentityCreateRequest;
use App\Models\Identity;
use App\Traits\RequestAPI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class IdentityController extends Controller
{
    use RequestAPI;

    public function list()
    {
        $identities = Identity::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.identities.index', [
            'identities' => $identities,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('pages.identities.create');
    }

    /**
     * @param \App\Http\Requests\IdentityCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(IdentityCreateRequest $request)
    {
        $data = $request->validated();
        $images = $request->file('images');
        $pathFiles = [];

        foreach ($images as $image) {
            array_push($pathFiles, $this->uploadFile($image));
        }
        $response = $this->sendPOSTRequest(config('app.ai_server') . '/identities', [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'images' => $pathFiles,
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('name', $response->body->message);

            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }

        Identity::create([
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'info' => $data['info'],
            'mongo_id' => $response->body->_id,
            'images' => array_map(function ($index, $url) use ($response) {
                return [
                    'url' => $url,
                    'mongo_id' => $response->body->facial_data[$index]
                ];
            }, array_keys($pathFiles), $pathFiles),
        ]);

        return redirect()->route('identities');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        return view('pages.identities.create');
    }

    /**
     * @param \App\Http\Requests\UserCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(IdentityCreateRequest $request, $id)
    {
//        $data = $request->validated();
//
//        User::create([
//            'name' => $data['name'],
//            'email' => $data['email'],
//            'password' => Hash::make($data['password']),
//        ]);
//
//        return redirect()->route('identities');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $identity = Identity::where('id', $id);

        $location = config('constants.minio_folder') . '/' . $id;
        if (Storage::disk('minio')->exists($location)) {
            Storage::disk('minio')->deleteDir($location);
        }

        $identity->delete();

        return redirect()->route('identities');
    }
}
