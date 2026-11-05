<?php
declare(strict_types=1);
namespace App\Modules\Pets\Livewire;
use App\Models\Pet;
use Livewire\Component;
class PetDetail extends Component
{
    public Pet $pet;
    public function mount(int $id): void { $this->pet = Pet::findOrFail($id); }
    public function render() { return view('modules.pets.detail')->layout('layouts.app'); }
}
