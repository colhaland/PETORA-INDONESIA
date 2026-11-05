<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Integrations\Contracts\IntegrationAdapter;
use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class IntegrationService
{
    public function testAdapter(IntegrationAdapter $adapter): bool
    {
        Gate::authorize('integrations.test');
        $result = $adapter->test();
        AuditService::log('integration.test', 'Integration', null, [], ['result' => $result]);

        return $result;
    }

    public function isConfigured(IntegrationAdapter $adapter): bool
    {
        return $adapter->isConfigured();
    }
}
