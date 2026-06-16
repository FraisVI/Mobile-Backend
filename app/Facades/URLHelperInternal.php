<?php

namespace App\Facades;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class URLHelperInternal
{
    public function transform($url) : string
    {
        if (Str::substr($url, 0, 4) == 'http') {
            return $url;
        }

        return Storage::url($url);
    }
}
