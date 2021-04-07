<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\Process;
use App\Models\TrackedObject;

class DashboardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $processesDone = Process::where('status', Process::STATUS['done'])->count();
        $processesTotal = Process::where('status', '!=', Process::STATUS['ready'])->count();
        $trackedObjects = TrackedObject::count();
        $identities = Identity::count();

        return view('dashboard', compact('processesDone', 'processesTotal', 'trackedObjects', 'identities'));
    }
}
