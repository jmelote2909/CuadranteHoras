<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getRate($key, $default = 0)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? (float) $setting->value : $default;
    }
}
