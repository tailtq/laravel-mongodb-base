<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Identity\Models\Identity;
use Modules\Process\Models\Process;
use Modules\Process\Models\TrackedObject;

class DashboardController extends Controller
{
    /**
     * @return Application|Factory|View
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
