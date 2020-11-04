<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentityCreateRequest;
use App\Models\Identity;
use App\Traits\RequestAPI;

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
            $extension  = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION );
            $name = $this->generateFileName($file);
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

//        $fileName = $object->name;
//        $fileNameOriginal = $object->file_name_original;
//        $diskName = config('constants-fileMedia.disk_name');
//        $existFile = $this->existFileInStorage($diskName, $fileName);
//        if (!$existFile) {
//            $existFile = $this->existFileInStorage($diskName, $fileNameOriginal);
//        }
//
//        if ($existFile) {
//            if ($type === FileMedia::DOWNLOAD) {
//                return Storage::disk($diskName)->download(env('FOLDER_SAVE') .'/' . $fileName);
//            }
//
//            if ($type === FileMedia::DELETE) {
//                $this->fileMediaRepository->delete($id);
//                return Storage::disk($diskName)->delete(env('FOLDER_SAVE') .'/' . $fileName);
//            }
//        }

        $identity->delete();

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
