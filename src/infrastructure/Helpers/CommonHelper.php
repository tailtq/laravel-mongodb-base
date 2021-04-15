<?php

namespace Infrastructure\Helpers;

class CommonHelper
{
    public static function generateFileName($file)
    {
        $filename = uniqid() . '-' . time();
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        return "$filename.$extension";
    }

    /**
     * @param array $arr
     * @return bool
     */
    public static function isAssociativeArray(array $arr)
    {
        if ($arr === []) return false;

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
