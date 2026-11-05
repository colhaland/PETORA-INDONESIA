<?php
declare(strict_types=1);
namespace App\Modules\Staff\Services;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Support\AuditService;
class StaffService
{
    public function create(array $data): User
    {
        Gate::authorize('staff.create');
        $u = User::create($data);
        AuditService::log('staff.create', User::class, $u->id);
        return $u;
    }
    public function update(User $user, array $data): User
    {
        Gate::authorize('staff.update');
        $user->update($data);
        return $user;
    }
    public function deactivate(User $user): void
    {
        Gate::authorize('staff.manage');
        $user->update(['status' => 'inactive']);
        AuditService::log('staff.deactivate', User::class, $user->id);
    }
}
