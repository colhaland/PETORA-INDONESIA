<?php
declare(strict_types=1);
namespace App\Modules\Pets\Services;
use App\Models\Pet;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class PetService
{
    public function create(array $data): Pet
    {
        Gate::authorize('pets.create');
        $data['pet_code'] = 'PET-' . str_pad((string) (Pet::max('id') + 1), 6, '0', STR_PAD_LEFT);
        $p = Pet::create($data);
        AuditService::log('pet.create', Pet::class, $p->id, [], $p->toArray());
        return $p;
    }
    public function update(Pet $pet, array $data): Pet
    {
        Gate::authorize('pets.update');
        $pet->update($data);
        return $pet;
    }
    public function delete(Pet $pet): void
    {
        Gate::authorize('pets.delete');
        $pet->delete();
        AuditService::log('pet.delete', Pet::class, $pet->id);
    }
}
