<?php
declare(strict_types=1);
namespace App\Modules\Reports\Livewire;
use App\Modules\Reports\Services\ReportsService;
use Livewire\Component;
class ReportsIndex extends Component
{
    public array $sales = [];
    public array $customers = [];
    public function mount(ReportsService $svc): void
    {
        $this->sales = $svc->salesSummary();
        $this->customers = $svc->customerSummary();
    }
    public function render()
    {
        return view('modules.reports.index')->layout('layouts.app');
    }
}
