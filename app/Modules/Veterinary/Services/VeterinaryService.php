<?php
declare(strict_types=1);
namespace App\Modules\Veterinary\Services;
use App\Models\MedicalVisit;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class VeterinaryService
{
    public function createVisit(array $data): MedicalVisit
    {
        Gate::authorize('veterinary.create');
        $data['status'] = $data['status'] ?? 'draft';
        $v = MedicalVisit::create($data);
        AuditService::log('medical_visit.create', MedicalVisit::class, $v->id);
        return $v;
    }
    public function finalize(MedicalVisit $visit): void
    {
        Gate::authorize('medical_records.finalize');
        $visit->update(['status' => 'final', 'finalized_at' => now()]);
        AuditService::log('medical_visit.finalize', MedicalVisit::class, $visit->id);
    }
    public function revise(MedicalVisit $visit, array $newData): void
    {
        Gate::authorize('medical_records.update');
        AuditService::log('medical_visit.revise', MedicalVisit::class, $visit->id, $visit->toArray(), $newData);
        $visit->update($newData);
    }
}
