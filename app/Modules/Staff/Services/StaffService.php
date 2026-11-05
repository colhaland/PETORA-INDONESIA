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
        $validated = validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ])->validate();
        $data = $validated + ['password' => Str::random(64)];
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
        $validated = validator($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ])->validate();
        $user->update($validated);

        return $user;
    }

    public function deactivate(User $user): void
    {
        Gate::authorize('staff.manage');
        $user->update(['status' => 'inactive']);
        AuditService::log('staff.deactivate', User::class, $user->id);
    }
}
