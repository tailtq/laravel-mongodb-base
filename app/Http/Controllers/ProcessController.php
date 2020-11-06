<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCreateRequest;
use App\Models\Process;
use App\Traits\RequestAPI;

class ProcessController extends Controller
{
    use RequestAPI;

    public function index()
    {
        $processes = Process::orderBy('created_at', 'desc')->paginate(10);

        return view('pages.processes.index', [
            'processes' => $processes,
        ]);
    }

    public function store(ProcessCreateRequest $request)
    {
        dd($request->all());
    }
}
