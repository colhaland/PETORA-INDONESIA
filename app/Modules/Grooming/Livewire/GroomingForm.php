<?php

declare(strict_types=1);

namespace App\Modules\Grooming\Livewire;

use App\Models\GroomingService;
use App\Models\GroomingSession;
use App\Modules\Grooming\Services\GroomingSessionService;
use Livewire\Component;

class GroomingForm extends Component
{
    public ?int $sessionId = null;

    public int $branchId = 1;

    public int $petId = 0;

    public ?int $serviceId = null;

    public ?int $groomerId = null;

    public function save(GroomingSessionService $svc): void
    {
        $this->validate(['branchId' => 'required|integer', 'petId' => 'required|integer']);
        if ($this->sessionId) {
            GroomingSession::findOrFail($this->sessionId)->update(['branch_id' => $this->branchId, 'pet_id' => $this->petId, 'grooming_service_id' => $this->serviceId, 'groomer_id' => $this->groomerId]);
        } else {
            $svc->create(['branch_id' => $this->branchId, 'pet_id' => $this->petId, 'grooming_service_id' => $this->serviceId, 'groomer_id' => $this->groomerId]);
        }
        session()->flash('success', 'Grooming disimpan.');
        $this->redirect(route('grooming.index'));
    }

    public function render()
    {
        return view('modules.grooming.form', ['services' => GroomingService::all()])->layout('layouts.app');
    }
}
