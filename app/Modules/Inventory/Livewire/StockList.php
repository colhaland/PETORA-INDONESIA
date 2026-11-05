<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Livewire;
use App\Models\StockBalance;
use Livewire\Component;
class StockList extends Component
{
    public function render()
    {
        return view('modules.inventory.list', ['balances' => StockBalance::with(['product', 'warehouse'])->paginate(20)])->layout('layouts.app');
    }
}
