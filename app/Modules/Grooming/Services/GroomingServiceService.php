<?php
declare(strict_types=1);
namespace App\Modules\Grooming\Services;
use App\Models\GroomingService;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class GroomingServiceService
{
    public function create(array $data): GroomingService
    {
        Gate::authorize('grooming.create');
        $s = GroomingService::create($data);
        AuditService::log('grooming_service.create', GroomingService::class, $s->id);
        return $s;
    }
    public function update(GroomingService $service, array $data): GroomingService
    {
        Gate::authorize('grooming.update');
        $service->update($data);
        return $service;
    }
    public function delete(GroomingService $service): void
    {
        Gate::authorize('grooming.manage');
        $service->delete();
        AuditService::log('grooming_service.delete', GroomingService::class, $service->id);
    }
}
