<?php
declare(strict_types=1);
namespace App\Modules\Customers\Services;
use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
class CustomerService
{
    public function create(array $data): Customer
    {
        Gate::authorize('customers.create');
        $data['customer_number'] = 'CUS-' . str_pad((string) (Customer::max('id') + 1), 6, '0', STR_PAD_LEFT);
        return Customer::create($data);
    }
    public function update(Customer $customer, array $data): Customer
    {
        Gate::authorize('customers.update');
        $customer->update($data);
        return $customer;
    }
}
