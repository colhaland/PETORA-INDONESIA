<?php

declare(strict_types=1);

namespace App\Modules\Grooming\Livewire;

use App\Models\GroomingService;
use App\Models\GroomingSession;
use App\Modules\Grooming\Services\GroomingSessionService;
use App\Support\BranchAuthorization;
use App\Support\RecordFinder;
use Livewire\Component;

class GroomingForm extends Component
{
    public ?int $sessionId = null;

    public int $petId = 0;

    public ?int $serviceId = null;

    public ?int $groomerId = null;

    public function save(GroomingSessionService $svc): void
    {
        $this->validate(['petId' => 'required|integer']);
        $payload = ['branch_id' => BranchAuthorization::currentId(), 'pet_id' => $this->petId, 'grooming_service_id' => $this->serviceId, 'groomer_id' => $this->groomerId];
        if ($this->sessionId) {
            RecordFinder::forClass(GroomingSession::class, $this->sessionId)->update($payload);
        } else {
            $svc->create($payload);
        }
        session()->flash('success', 'Grooming disimpan.');
        $this->redirect(route('grooming.index'));
    }

    public function render()
    {
        return view('modules.grooming.form', ['services' => GroomingService::all()])->layout('layouts.app');
    }
}
