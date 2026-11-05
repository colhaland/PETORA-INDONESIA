<?php
declare(strict_types=1);
namespace Tests\Feature\Settings;
use App\Support\SettingsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_set_and_get(): void
    {
        SettingsService::set('foo', 'bar');
        $this->assertEquals('bar', SettingsService::get('foo'));
    }
}
