<?php

declare(strict_types=1);

namespace App\Modules\Veterinary\Services;

use App\Models\MedicalVisit;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

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
        if (($visit->status ?? 'draft') === 'final') {
            throw new \RuntimeException('Rekam medis final hanya dapat diubah melalui revisi.');
        }
        $visit->update(['status' => 'final', 'finalized_at' => now()]);
        AuditService::log('medical_visit.finalize', MedicalVisit::class, $visit->id);
    }

    public function revise(MedicalVisit $visit, array $newData): void
    {
        Gate::authorize('medical_records.update');
        $before = $visit->toArray();
        DB::transaction(function () use ($visit, $newData, $before): void {
            if (Schema::hasTable('medical_record_revisions')) {
                DB::table('medical_record_revisions')->insert([
                    'medical_visit_id' => $visit->id,
                    'revised_by' => auth()->id(),
                    'reason' => $newData['revision_reason'] ?? null,
                    'previous_snapshot' => json_encode($before, JSON_UNESCAPED_UNICODE),
                    'revised_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            unset($newData['revision_reason']);
            $visit->update($newData);
            AuditService::log('medical_visit.revise', MedicalVisit::class, $visit->id, $before, $visit->fresh()?->toArray() ?? []);
        });
    }
}
