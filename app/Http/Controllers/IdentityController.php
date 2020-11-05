<?php

namespace App\Http\Controllers;

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

        $identity = Identity::create([
            'name' => $data['name'],
            'status' => $data['status'] ? 'tracking' : 'untracking',
            'info' => $data['info'],
            'card_number' => $data['card_number'],
        ]);


        foreach ($files as $key => $file) {
            $filename   = uniqid() . '-' . time() . '-' . md5(time());
            $extension  = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION );
            $name = $filename . '.' . $extension;
            $pathFiles[$key] = $this->uploadFile($file, $name, $identity->id);
        }

        $identity->images = json_encode($pathFiles);
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

        $location = config('constants.minio_folder') . '/' . $id ;
        if(Storage::disk('minio')->exists($location)){
            Storage::disk('minio')->deleteDir($location);
        }

        $identity->delete();

        return redirect()->route('identities');
    }
}
