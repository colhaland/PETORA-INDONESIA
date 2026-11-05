<?php

declare(strict_types=1);

namespace App\Modules\Veterinary\Livewire;

use App\Models\MedicalVisit;
use App\Modules\Veterinary\Services\VeterinaryService;
use App\Support\BranchAuthorization;
use App\Support\RecordFinder;
use Livewire\Component;

class VisitForm extends Component
{
    public ?int $visitId = null;

    public int $petId = 0;

    public string $subjective = '';

    public string $objective = '';

    public string $assessment = '';

    public string $plan = '';

    public function save(VeterinaryService $svc): void
    {
        $this->validate(['petId' => 'required|integer']);
        if ($this->visitId) {
            $svc->revise(RecordFinder::forClass(MedicalVisit::class, $this->visitId), ['subjective' => $this->subjective, 'objective' => $this->objective, 'assessment' => $this->assessment, 'plan' => $this->plan]);
        } else {
            $svc->createVisit(['branch_id' => BranchAuthorization::currentId(), 'pet_id' => $this->petId, 'subjective' => $this->subjective, 'objective' => $this->objective, 'assessment' => $this->assessment, 'plan' => $this->plan]);
        }
        session()->flash('success', 'Kunjungan disimpan.');
        $this->redirect(route('veterinary.index'));
    }

    public function finalize(VeterinaryService $svc): void
    {
        if ($this->visitId) {
            $svc->finalize(RecordFinder::forClass(MedicalVisit::class, $this->visitId));
            session()->flash('success', 'Kunjungan difinalisasi.');
            $this->redirect(route('veterinary.index'));
        }
    }

    public function render()
    {
        return view('modules.veterinary.form')->layout('layouts.app');
    }
}
