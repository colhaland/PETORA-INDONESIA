<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = config('services.healthcheck.token');
        abort_unless(
            is_string($token) && $token !== '' && hash_equals($token, (string) $request->bearerToken()),
            404,
        );

        DB::select('SELECT 1');
        Redis::connection()->ping();

        return response()->json([
            'status' => 'ok',
            'services' => [
                'database' => 'ok',
                'redis' => 'ok',
            ],
        ]);
    }
}
