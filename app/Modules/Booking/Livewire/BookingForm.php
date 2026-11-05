<?php

declare(strict_types=1);

namespace App\Modules\Booking\Livewire;

use App\Models\Booking;
use App\Modules\Booking\Services\BookingService;
use Livewire\Component;

class BookingForm extends Component
{
    public ?int $bookingId = null;

    public int $branchId = 1;

    public int $customerId = 0;

    public ?int $petId = null;

    public string $type = 'grooming';

    public string $scheduledAt = '';

    protected function rules(): array
    {
        return ['branchId' => 'required|integer', 'customerId' => 'required|integer', 'type' => 'required|string', 'scheduledAt' => 'required'];
    }

    public function save(BookingService $svc): void
    {
        $this->validate();
        if ($this->bookingId) {
            Booking::findOrFail($this->bookingId)->update(['branch_id' => $this->branchId, 'customer_id' => $this->customerId, 'pet_id' => $this->petId, 'type' => $this->type, 'scheduled_at' => $this->scheduledAt]);
        } else {
            $svc->create(['branch_id' => $this->branchId, 'customer_id' => $this->customerId, 'pet_id' => $this->petId, 'type' => $this->type, 'scheduled_at' => $this->scheduledAt]);
        }
        session()->flash('success', 'Booking disimpan.');
        $this->redirect(route('booking.index'));
    }

    public function render()
    {
        return view('modules.booking.form')->layout('layouts.app');
    }
}
