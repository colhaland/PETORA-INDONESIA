<?php

declare(strict_types=1);

namespace App\Modules\Pos\Livewire;

use App\Models\Order;
use App\Modules\Pos\Services\PosService;
use Livewire\Component;

class PosForm extends Component
{
    public ?int $orderId = null;

    public function mount(PosService $svc): void
    {
        try {
            $order = $svc->createOrder(1, null, auth()->id() ?? 1);
            $this->orderId = $order->id;
        } catch (\Throwable $e) {
            $this->orderId = null;
        }
    }

    public function render()
    {
        return view('modules.pos.form', ['order' => $this->orderId ? Order::find($this->orderId) : null])->layout('layouts.app');
    }
}
