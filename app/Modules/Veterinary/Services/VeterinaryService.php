<?php

declare(strict_types=1);

namespace App\Modules\Veterinary\Services;

use App\Models\MedicalVisit;
use App\Models\Pet;
use App\Support\AuditService;
use App\Support\BranchAuthorization;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class VeterinaryService
{
    public function createVisit(array $data): MedicalVisit
    {
        Gate::authorize('veterinary.create');
        $branchId = (int) ($data['branch_id'] ?? 0);
        BranchAuthorization::assertCanAccess($branchId);
        $petId = (int) ($data['pet_id'] ?? 0);
        $pet = Pet::query()->findOrFail($petId);
        $doctorId = auth()->id();
        abort_unless($doctorId, 403);
        $v = MedicalVisit::create([
            'branch_id' => $branchId,
            'pet_id' => $pet->id,
            'doctor_id' => $doctorId,
            'status' => 'draft',
            'subjective' => $data['subjective'] ?? null,
            'objective' => $data['objective'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
        ]);
        AuditService::log('medical_visit.create', MedicalVisit::class, $v->id);

        return $v;
    }

    public function finalize(MedicalVisit $visit): void
    {
        Gate::authorize('medical_records.finalize');
        BranchAuthorization::assertCanAccess((int) $visit->branch_id);
        if (($visit->status ?? 'draft') === 'final') {
            throw new \RuntimeException('Rekam medis final hanya dapat diubah melalui revisi.');
        }
        $visit->update(['status' => 'final', 'finalized_at' => now()]);
        AuditService::log('medical_visit.finalize', MedicalVisit::class, $visit->id);
    }

    public function revise(MedicalVisit $visit, array $newData): void
    {
        Gate::authorize('medical_records.update');
        BranchAuthorization::assertCanAccess((int) $visit->branch_id);
        $clinicalKeys = ['subjective', 'objective', 'assessment', 'plan', 'status', 'visible_to_owner', 'finalized_at', 'branch_id', 'pet_id', 'doctor_id'];
        $before = array_intersect_key($visit->getAttributes(), array_flip($clinicalKeys));
        $updates = array_intersect_key($newData, array_flip(['subjective', 'objective', 'assessment', 'plan']));
        DB::transaction(function () use ($visit, $updates, $newData, $before, $clinicalKeys): void {
            if (Schema::hasTable('medical_record_revisions')) {
                $snapshot = json_encode(
                    ['encrypted' => Crypt::encryptString(json_encode($before, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                );
                DB::table('medical_record_revisions')->insert([
                    'medical_visit_id' => $visit->id,
                    'revised_by' => auth()->id(),
                    'reason' => $newData['revision_reason'] ?? null,
                    'previous_snapshot' => $snapshot,
                    'revised_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if (DB::getDriverName() === 'pgsql') {
                DB::select("select set_config('petora.medical_revision', 'on', true)");
            }
            $visit->update($updates);
            AuditService::log('medical_visit.revise', MedicalVisit::class, $visit->id, $before, $visit->fresh()?->only($clinicalKeys) ?? []);
        });
    }
}
