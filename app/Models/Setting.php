<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static function cacheKey(): string
    {
        return 'rizqmall.settings';
    }

    public static function allCached(): array
    {
        if (!Schema::hasTable('settings')) {
            return [];
        }

        return Cache::rememberForever(self::cacheKey(), function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    public static function getValue(string $key, $default = null)
    {
        $settings = self::allCached();
        return $settings[$key] ?? $default;
    }

    public static function getFloat(string $key, $default = null): float
    {
        $value = self::getValue($key, $default);
        return is_numeric($value) ? (float) $value : (float) $default;
    }

    public static function setValues(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget(self::cacheKey());
    }
}
