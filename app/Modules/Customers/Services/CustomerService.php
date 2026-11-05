<?php

declare(strict_types=1);

namespace App\Modules\Customers\Services;

use App\Models\Customer;
use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;

class CustomerService
{
    public function create(array $data): Customer
    {
        Gate::authorize('customers.create');
        $data['customer_number'] = 'CUS-'.str_pad((string) (Customer::max('id') + 1), 6, '0', STR_PAD_LEFT);
        $c = Customer::create($data);
        AuditService::log('customer.create', Customer::class, $c->id, [], $c->toArray());

        return $c;
    }

    public function update(Customer $customer, array $data): Customer
    {
        Gate::authorize('customers.update');
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        Gate::authorize('customers.delete');
        $customer->delete();
        AuditService::log('customer.delete', Customer::class, $customer->id);
    }
}
