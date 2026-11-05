<?php

declare(strict_types=1);

namespace App\Modules\Customers\Livewire;

use App\Models\Customer;
use App\Modules\Customers\Services\CustomerService;
use App\Support\RecordFinder;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?int $customerId = null;

    public string $name = '';

    public ?string $phone = null;

    public ?string $email = null;

    protected function rules(): array
    {
        return ['name' => 'required|string|max:255', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email|max:255'];
    }

    public function save(CustomerService $svc): void
    {
        $this->validate();
        if ($this->customerId) {
            $svc->update(RecordFinder::forClass(Customer::class, $this->customerId), ['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
        } else {
            $svc->create(['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
        }
        session()->flash('success', 'Pelanggan disimpan.');
        $this->redirect(route('customers.index'));
    }

    public function delete(CustomerService $svc): void
    {
        if ($this->customerId) {
            $svc->delete(RecordFinder::forClass(Customer::class, $this->customerId));
        }
        session()->flash('success', 'Pelanggan dihapus.');
        $this->redirect(route('customers.index'));
    }

    public function render()
    {
        if ($this->customerId && empty($this->name)) {
            $c = Customer::query()->find($this->customerId);
            if ($c) {
                $this->name = $c->name;
                $this->phone = $c->phone;
                $this->email = $c->email;
            }
        }

        return view('modules.customers.form')->layout('layouts.app');
    }
}
