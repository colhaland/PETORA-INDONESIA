<?php

declare(strict_types=1);

namespace App\Modules\Staff\Services;

use App\Models\User;
use App\Support\AuditService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class StaffService
{
    public function create(array $data): User
    {
        Gate::authorize('staff.create');
        unset($data['password']);
        $data['password'] = Str::random(64);
        $u = User::create($data);
        $status = Password::sendResetLink(['email' => $u->email]);
        if ($status !== Password::RESET_LINK_SENT) {
            throw new \RuntimeException('Undangan password tidak dapat dikirim: '.__($status));
        }
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
