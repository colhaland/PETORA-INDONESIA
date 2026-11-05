<?php
declare(strict_types=1);
namespace App\Modules\Customers\Livewire;
use App\Models\Customer;
use Livewire\Component;
class CustomerDetail extends Component
{
    public Customer $customer;
    public function mount(int $id): void
    {
        $this->customer = Customer::findOrFail($id);
    }
    public function render()
    {
        return view('modules.customers.detail')->layout('layouts.app');
    }
}
