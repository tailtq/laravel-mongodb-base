<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Http\Requests\IdentityCreateRequest;
use App\Models\Identity;
use App\Traits\RequestAPI;
use Illuminate\Support\Facades\Storage;

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
        $files = $request->file('files');
        $pathFiles = [];

        try {
            $identity = Identity::create([
                'name'        => $data['name'],
                'status'      => !empty($data['status']) ? 'tracking' : 'untracking',
                'info'        => $data['info'],
                'card_number' => $data['card_number'],
            ]);

            foreach ($files as $key => $file) {
                $filename = CommonHelper::generateFileName($file);
                $pathFiles[$key] = ['mongo_id' => rand(1, 999), 'url' => $this->uploadFile($file, $filename, $identity->id)];
            }
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }


        $identity->images = $pathFiles;
        $identity->save();

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
