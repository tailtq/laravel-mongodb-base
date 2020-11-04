<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentityCreateRequest;
use App\Models\Identity;

class IdentityController extends Controller
{
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

        foreach ($files as $key => $file) {
            $extension  = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION );
            $name = $this->generateFileName($file);
            $pathFiles[$key] = $this->uploadFile($file, $name);
        }

        Identity::create([
            'name' => $data['name'],
            'images' => json_encode($pathFiles),
            'status' => $data['status'],
            'info' => $data['info'],
            'identity_number' => $data['identity_number'],
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
        Identity::where('id', $id)->delete();

        return redirect()->route('identities');
    }

    /**
     * Generate file name
     * @param $file
     * @return string
     */
    private function generateFileName($file) {
        $filename   = uniqid() . "-" . time() . "-" . md5(time());
        $extension  = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION );
        $basename   = $filename . "." . $extension;
        return $basename;
    }
}
