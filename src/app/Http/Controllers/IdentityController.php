<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentityCreateRequest;
use App\Models\Identity;
use App\Traits\RequestAPI;
use Illuminate\Support\Arr;
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

        $response = $this->sendPOSTRequest($this->getIdentityUrl(), [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'images' => Arr::pluck($data['images'], 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('name', $response->message);

            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }

        Identity::create([
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'info' => $data['info'],
            'mongo_id' => $response->body->_id,
            'images' => array_map(function ($index, $element) use ($response) {
                return [
                    'url' => $element['url'],
                    'mongo_id' => $response->body->facial_data[$index]
                ];
            }, array_keys($data['images']), $data['images']),
        ]);

        return redirect()->route('identities');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $identity = Identity::find($id);

        if (!$identity) {
            abort(404);
        }

        return view('pages.identities.edit', [
            'identity' => $identity,
        ]);
    }

    /**
     * @param \App\Http\Requests\IdentityCreateRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(IdentityCreateRequest $request, $id)
    {
        $identity = Identity::find($id);

        if (!$identity) {
            abort(404);
        }
        $data = $request->validated();
        $oldImages = $identity->images;
        $newImages = Arr::where($data['images'], function ($image) {
            return empty($image['mongo_id']);
        });
        $response = $this->sendPUTRequest($this->getIdentityUrl($identity->mongo_id), [
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'card_number' => $data['card_number'],
            'images' => Arr::pluck($newImages, 'url'),
        ], $this->getDefaultHeaders());

        if (!$response->status) {
            $messageBag = new MessageBag();
            $messageBag->add('name', $response->message);

            return redirect()->back()->withErrors($messageBag)->withInput($request->all());
        }
        foreach ($newImages as $index => $image) {
            array_push($oldImages, [
                'mongo_id' => $response->body->facial_data[$index],
                'url' => $image['url']
            ]);
        }

        Identity::where('id', $id)->update([
            'name' => $data['name'],
            'status' => !empty($data['status']) ? 'tracking' : 'untracking',
            'info' => $data['info'],
            'card_number' => $data['card_number'],
            'images' => $oldImages,
        ]);

        return redirect()->route('identities.edit', $identity->id);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $identity = Identity::find($id);

        if (!$identity) {
            abort(404);
        }
        $this->sendDELETERequest($this->getIdentityUrl($identity->mongo_id), [], $this->getDefaultHeaders());

        $location = config('constants.minio_folder') . '/' . $id;
        if (Storage::disk('minio')->exists($location)) {
            Storage::disk('minio')->deleteDir($location);
        }

        $identity->delete();

        return redirect()->route('identities');
    }

    protected function getIdentityUrl($mongoId = '')
    {
        return config('app.ai_server') . '/identities' . ($mongoId ? "/$mongoId" : '');
    }
}
