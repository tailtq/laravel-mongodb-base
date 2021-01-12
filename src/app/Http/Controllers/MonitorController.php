<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaCreateRequest;
use Illuminate\Http\UploadedFile;

class MonitorController extends Controller
{
    public function index()
    {
        return view('pages.monitors.index');
    }
}
