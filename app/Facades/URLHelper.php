<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *  @method static string transform(string $url)
 */
class URLHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'urlhelper';
    }
}
