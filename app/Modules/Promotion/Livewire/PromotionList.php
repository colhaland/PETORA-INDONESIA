<?php
declare(strict_types=1);
namespace App\Modules\Promotion\Livewire;
use Livewire\Component;
class PromotionList extends Component
{
    public function render()
    {
        return view('modules.promotion.list')->layout('layouts.app');
    }
}
