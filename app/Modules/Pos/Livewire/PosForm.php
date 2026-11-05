<?php

declare(strict_types=1);

namespace App\Modules\Pos\Livewire;

use App\Models\Order;
use App\Modules\Pos\Services\PosService;
use App\Support\BranchAuthorization;
use Livewire\Component;

class PosForm extends Component
{
    public ?int $orderId = null;

    public function mount(PosService $svc): void
    {
        $order = $svc->createOrder(BranchAuthorization::currentId(), null, auth()->id());
        $this->orderId = $order->id;
    }

    public function render()
    {
        return view('modules.pos.form', ['order' => $this->orderId ? Order::find($this->orderId) : null])->layout('layouts.app');
    }
}
