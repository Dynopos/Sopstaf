<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['business_id', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function get(int $businessId, string $key, mixed $default = null): mixed
    {
        $row = static::where('business_id', $businessId)->where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    public static function put(int $businessId, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['business_id' => $businessId, 'key' => $key],
            ['value' => $value],
        );
    }
}
