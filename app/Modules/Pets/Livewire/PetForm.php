<?php
declare(strict_types=1);
namespace App\Modules\Pets\Livewire;
use App\Models\Pet;
use Livewire\Component;
class PetForm extends Component
{
    public string $name = '';
    public string $species = 'dog';
    public ?int $customerId = null;
    public function save(): void
    {
        $this->validate(['name' => 'required', 'species' => 'required', 'customerId' => 'required']);
        Pet::create(['name' => $this->name, 'species' => $this->species, 'customer_id' => $this->customerId]);
        session()->flash('success', 'Hewan disimpan.');
        $this->redirect(route('pets.index'));
    }
    public function render()
    {
        return view('modules.pets.form')->layout('layouts.app');
    }
}
