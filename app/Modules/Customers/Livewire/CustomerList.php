<?php
declare(strict_types=1);
namespace App\Modules\Customers\Livewire;
use App\Models\Customer;
use Livewire\Component;
class CustomerList extends Component
{
    public function render()
    {
        return view("modules.customers.list", ["customers" => Customer::latest()->paginate(20)])->layout("layouts.app");
    }
}
