<?php
declare(strict_types=1);
namespace App\Support;
use App\Models\SystemSetting;
class SettingsService
{
    public static function get(string $key, $default = null)
    {
        $row = SystemSetting::where('key', $key)->first();
        return $row ? $row->value : $default;
    }
    public static function set(string $key, $value, ?int $userId = null): void
    {
        SystemSetting::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $userId]);
    }
}
