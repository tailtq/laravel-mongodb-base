<?php

namespace App\Http\Controllers;

use Infrastructure\Traits\MongoDB;
use Infrastructure\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Infrastructure\Traits\HandleUploadFile;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, HandleUploadFile, ResponseTrait;
}
