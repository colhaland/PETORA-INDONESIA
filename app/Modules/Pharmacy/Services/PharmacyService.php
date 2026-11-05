<?php
declare(strict_types=1);
namespace App\Modules\Pharmacy\Services;
use App\Models\Medicine;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class PharmacyService
{
    public function create(array $data): Medicine
    {
        Gate::authorize('pharmacy.manage');
        $m = Medicine::create($data);
        AuditService::log('medicine.create', Medicine::class, $m->id);
        return $m;
    }
    public function update(Medicine $medicine, array $data): Medicine
    {
        Gate::authorize('pharmacy.manage');
        $medicine->update($data);
        return $medicine;
    }
    public function delete(Medicine $medicine): void
    {
        Gate::authorize('pharmacy.manage');
        $medicine->delete();
        AuditService::log('medicine.delete', Medicine::class, $medicine->id);
    }
}
