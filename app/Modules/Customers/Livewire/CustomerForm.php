<?php
declare(strict_types=1);
namespace App\Modules\Customers\Livewire;
use App\Models\Customer;
use App\Modules\Customers\Services\CustomerService;
use Livewire\Component;
class CustomerForm extends Component
{
    public ?int $customerId = null;
    public string $name = '';
    public ?string $phone = null;
    public ?string $email = null;
    public function save(CustomerService $svc): void
    {
        $this->validate(['name' => 'required|string|max:255']);
        if ($this->customerId) {
            Customer::findOrFail($this->customerId)->update([
                'name' => $this->name, 'phone' => $this->phone, 'email' => $this->email,
            ]);
        } else {
            $svc->create(['name' => $this->name, 'phone' => $this->phone, 'email' => $this->email]);
        }
        session()->flash('success', 'Pelanggan disimpan.');
        $this->redirect(route('customers.index'));
    }
    public function render()
    {
        return view('modules.customers.form');
    }
}
