<?php
declare(strict_types=1);
namespace Tests\Feature\Audit;
use App\Support\AuditService;
use App\Models\AuditLog;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AuditServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_log_creates_entry(): void
    {
        AuditService::log('test.action');
        $this->assertEquals(1, AuditLog::count());
    }
}
