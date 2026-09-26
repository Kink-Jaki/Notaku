<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    public static function get(): Setting
    {
        $data = Cache::rememberForever('app_settings', function () {
            $setting = Setting::first();
            return $setting ? $setting->toArray() : [];
        });

        return new Setting($data);
    }
}
