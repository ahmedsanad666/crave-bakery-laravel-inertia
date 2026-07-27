<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
    
        $value = Cache::rememberForever("site_setting_{$key}", function () use ($key) {
            return static::query()->where('key', $key)->value('value') ?? '__NULL__';
        });
    
        if ($value === '__NULL__') return $default;
    
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
    
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    
        // امسح الـ Cache لما الإعداد يتغير
        Cache::forget("site_setting_{$key}");
    }
}
