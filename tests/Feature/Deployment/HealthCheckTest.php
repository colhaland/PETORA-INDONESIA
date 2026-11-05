<?php

declare(strict_types=1);

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_hides_details_without_a_valid_bearer_token(): void
    {
        config(['services.healthcheck.token' => 'health-test-token']);

        $this->getJson('/health')->assertNotFound();
        $this->withToken('invalid-token')->getJson('/health')->assertNotFound();
    }

    public function test_health_check_reports_database_and_redis_status_with_a_valid_token(): void
    {
        config(['services.healthcheck.token' => 'health-test-token']);

        $redis = Mockery::mock();
        $redis->shouldReceive('ping')->once()->andReturn('PONG');
        Redis::shouldReceive('connection')->once()->andReturn($redis);

        $this->withToken('health-test-token')
            ->getJson('/health')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'services' => [
                    'database' => 'ok',
                    'redis' => 'ok',
                ],
            ]);
    }

    public function test_health_check_rate_limits_repeated_requests(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->get('/health')->assertNotFound();
        }

        $this->get('/health')->assertStatus(429);
    }
}
