<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SettingsService
{
    private const RULES = [
        'tax.enabled' => ['boolean'],
        'tax.rate' => ['numeric', 'min:0', 'max:100'],
        'stock.allow_negative' => ['boolean'],
        'security.lockout_attempts' => ['integer', 'min:3', 'max:10'],
        'security.lockout_minutes' => ['integer', 'min:1', 'max:60'],
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = SystemSetting::where('key', $key)->first();

        return $row ? $row->value : config('petora.'.$key, $default);
    }

    public static function set(string $key, mixed $value, ?int $userId = null): void
    {
        Gate::authorize('settings.manage');
        abort_unless(array_key_exists($key, self::RULES), 422, 'Kunci pengaturan tidak diizinkan.');

        validator(['value' => $value], ['value' => self::RULES[$key]])->validate();
        $old = static::get($key);
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => Str::before($key, '.'), 'updated_by' => $userId],
        );
        AuditService::log('settings.update', SystemSetting::class, null, ['key' => $key, 'value' => $old], ['key' => $key, 'value' => $value]);
    }
}
