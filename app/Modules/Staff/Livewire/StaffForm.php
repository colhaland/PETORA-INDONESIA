<?php

declare(strict_types=1);

namespace App\Modules\Staff\Livewire;

use App\Models\User;
use App\Modules\Staff\Services\StaffService;
use App\Support\RecordFinder;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StaffForm extends Component
{
    public ?int $staffId = null;

    public string $name = '';

    public ?string $email = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                $this->staffId ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->staffId),
            ],
        ];
    }

    public function save(StaffService $svc): void
    {
        $this->validate();
        if ($this->staffId) {
            $svc->update(RecordFinder::forClass(User::class, $this->staffId), ['name' => $this->name, 'email' => $this->email]);
        } else {
            $svc->create(['name' => $this->name, 'email' => $this->email]);
        }
        session()->flash('success', 'Staf disimpan.');
        $this->redirect(route('staff.index'));
    }

    public function render()
    {
        return view('modules.staff.form')->layout('layouts.app');
    }
}
