<?php

namespace App\Helpers;

class CommonHelper
{
    public static function generateFileName($file)
    {
        $filename = uniqid() . '-' . time();
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        return "$filename.$extension";
    }
}
