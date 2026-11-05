<?php
declare(strict_types=1);
namespace App\Modules\Pos\Livewire;
use App\Modules\Pos\Services\OrderService;
use Livewire\Component;
class PosPage extends Component
{
    public ?int $orderId = null;
    public function mount(): void
    {
        $this->orderId = null;
    }
    public function render()
    {
        return view('modules.pos.page', ['orderId' => $this->orderId])->layout('layouts.app');
    }
}
