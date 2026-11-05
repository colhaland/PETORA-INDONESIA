<?php

declare(strict_types=1);

namespace App\Modules\Pets\Livewire;

use App\Models\Pet;
use App\Modules\Pets\Services\PetService;
use Livewire\Component;

class PetForm extends Component
{
    public ?int $petId = null;

    public string $name = '';

    public string $species = 'dog';

    public ?int $customerId = null;

    protected function rules(): array
    {
        return ['name' => 'required|string|max:255', 'species' => 'required|string|max:30', 'customerId' => 'required|integer'];
    }

    public function save(PetService $svc): void
    {
        $this->validate();
        if ($this->petId) {
            $svc->update(Pet::findOrFail($this->petId), ['name' => $this->name, 'species' => $this->species, 'customer_id' => $this->customerId]);
        } else {
            $svc->create(['name' => $this->name, 'species' => $this->species, 'customer_id' => $this->customerId]);
        }
        session()->flash('success', 'Hewan disimpan.');
        $this->redirect(route('pets.index'));
    }

    public function delete(PetService $svc): void
    {
        if ($this->petId) {
            $svc->delete(Pet::findOrFail($this->petId));
        }
        session()->flash('success', 'Hewan dihapus.');
        $this->redirect(route('pets.index'));
    }

    public function render()
    {
        if ($this->petId && empty($this->name)) {
            $p = Pet::find($this->petId);
            if ($p) {
                $this->name = $p->name;
                $this->species = $p->species;
                $this->customerId = $p->customer_id;
            }
        }

        return view('modules.pets.form')->layout('layouts.app');
    }
}
