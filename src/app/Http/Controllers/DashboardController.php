<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $processesDone = Process::where('status', Process::STATUS['done'])->count();
        $processesTotal = Process::count();
        return view('dashboard', compact('processesDone', 'processesTotal'));
    }
}
