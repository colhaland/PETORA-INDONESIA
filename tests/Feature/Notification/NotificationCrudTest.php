<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Modules\Notification\Services\NotificationService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('notifications.view', 'notifications.manage_templates');
        $this->actingAs($user);

        return $user;
    }

    public function test_send_notification(): void
    {
        $this->authUser();
        $svc = new NotificationService;
        $n = $svc->send(auth()->id(), 'info', 'Hello');
        $this->assertEquals('info', $n->type);
        $this->assertNull($n->read_at);
    }

    public function test_mark_read(): void
    {
        $this->authUser();
        $svc = new NotificationService;
        $n = $svc->send(auth()->id(), 'info', 'Hello');
        $svc->markRead($n);
        $this->assertNotNull($n->fresh()->read_at);
    }

    public function test_unread_count(): void
    {
        $this->authUser();
        $svc = new NotificationService;
        $svc->send(auth()->id(), 'info', 'Hello');
        $this->assertEquals(1, $svc->unreadCount(auth()->id()));
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/notifications')->assertStatus(200);
    }
}
