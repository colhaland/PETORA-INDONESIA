<?php
declare(strict_types=1);
namespace App\Modules\Accounting\Services;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class AccountingService
{
    public function createJournal(array $entries): bool
    {
        Gate::authorize('accounting.manage');
        $debit = array_sum(array_column($entries, 'debit'));
        $credit = array_sum(array_column($entries, 'credit'));
        if (abs($debit - $credit) > 0.001) {
            throw new \RuntimeException('Jurnal tidak seimbang.');
        }
        AuditService::log('accounting.journal_create', 'Journal', null);
        return true;
    }
}
