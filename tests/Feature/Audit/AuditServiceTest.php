<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_entry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        request()->headers->set('User-Agent', 'PETORA security test');

        AuditService::log('test.action', User::class, $user->id, ['before' => 'old'], ['after' => 'new']);

        $this->assertEquals(1, AuditLog::count());
        $entry = AuditLog::firstOrFail();
        $this->assertSame($user->id, $entry->actor_id);
        $this->assertSame(User::class, $entry->auditable_type);
        $this->assertSame($user->id, $entry->auditable_id);
        $this->assertSame('PETORA security test', $entry->user_agent);
        $this->assertSame(['before' => 'old'], $entry->old_values);
        $this->assertSame(['after' => 'new'], $entry->new_values);
    }

    public function test_audit_log_model_rejects_mutation(): void
    {
        AuditService::log('immutable.test');

        $this->expectException(\LogicException::class);
        AuditLog::firstOrFail()->update(['action' => 'tampered']);
    }

    public function test_audit_log_database_trigger_rejects_raw_deletion(): void
    {
        AuditService::log('immutable.delete');

        $this->expectException(QueryException::class);
        DB::table('audit_logs')->delete();
    }
}
