<?php
declare(strict_types=1);
namespace Tests\Feature\Settings;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class SettingsTest extends TestCase
{
    use RefreshDatabase;
    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/settings')->assertStatus(200);
    }
}
