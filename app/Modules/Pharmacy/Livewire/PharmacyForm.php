<?php

declare(strict_types=1);

namespace App\Modules\Pharmacy\Livewire;

use App\Models\Medicine;
use App\Modules\Pharmacy\Services\PharmacyService;
use Livewire\Component;

class PharmacyForm extends Component
{
    public ?int $medicineId = null;

    public string $name = '';

    public string $category = 'medicine';

    protected function rules(): array
    {
        return ['name' => 'required|string|max:255', 'category' => 'required|string|max:30'];
    }

    public function save(PharmacyService $svc): void
    {
        $this->validate();
        if ($this->medicineId) {
            $svc->update(Medicine::findOrFail($this->medicineId), ['name' => $this->name, 'category' => $this->category]);
        } else {
            $svc->create(['name' => $this->name, 'category' => $this->category]);
        }
        session()->flash('success', 'Obat disimpan.');
        $this->redirect(route('pharmacy.index'));
    }

    public function delete(PharmacyService $svc): void
    {
        if ($this->medicineId) {
            $svc->delete(Medicine::findOrFail($this->medicineId));
        }
        session()->flash('success', 'Obat dihapus.');
        $this->redirect(route('pharmacy.index'));
    }

    public function render()
    {
        if ($this->medicineId && empty($this->name)) {
            $m = Medicine::find($this->medicineId);
            if ($m) {
                $this->name = $m->name;
                $this->category = $m->category;
            }
        }

        return view('modules.pharmacy.form')->layout('layouts.app');
    }
}
