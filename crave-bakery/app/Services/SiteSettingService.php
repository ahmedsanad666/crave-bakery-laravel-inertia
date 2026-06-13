<?php

namespace App\Services;

use App\Models\SiteSetting;
class SiteSettingService
{

    public static function get(string $key, mixed $default = null):mixed{

        return SiteSetting::get($key, $default);
    }

    public static function set(string $key, mixed $value):void{
        SiteSetting::set($key, $value);
    }
}